<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Swoole\WebSocket\Server;
use Src\Core\Router;
use Src\Core\ErrorHandler;
use Src\Core\RouteCollection;
use Src\Core\WebSocketRouteCollection;
use Src\Middleware\WebSocketAuthMiddleware;
use Src\Core\SwooleErrorHandler;
use Src\Core\ErrorReporter;
use Src\Core\ErrorTracker;
use Src\Exceptions\NotFoundException;
use Src\Exceptions\HttpException;
use Src\Config\ConfigurationManager;
use \Rollbar\Rollbar;
use \Rollbar\Payload\Level;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$dependencies = require __DIR__ . '/../src/Config/Dependencies.php';
$dependencies($containerBuilder);
$containerBuilder->compile();

$configManager = $containerBuilder->get(ConfigurationManager::class);

// Initialize Rollbar
Rollbar::init([
    'access_token' => $configManager->get('rollbar.access_token'),
    'environment' => $configManager->get('app.environment')
]);

$router = new Router(
    $containerBuilder,
    $routeCollection,
    $containerBuilder->get(ErrorHandler::class)
);

$server = new Swoole\HTTP\Server("0.0.0.0", 9501);

// Initialize and register the error handler
$redis = new Redis();
$redis->connect($config['redis']['host'], $config['redis']['port']);

$errorReporter = new ErrorReporter($config['error_reporting']);
$errorTracker = new ErrorTracker($redis);

$errorHandler = new SwooleErrorHandler(
    $config['app']['display_errors'],
    $errorReporter,
    $errorTracker,
    $config['app']['environment']
);
$errorHandler->register($server);

$server->set([
    'ssl_cert_file' => $config['swoole']['ssl']['cert_file'],
    'ssl_key_file' => $config['swoole']['ssl']['key_file'],
    'ssl_protocols' => $config['swoole']['ssl']['protocols'],
    'ssl_ciphers' => $config['swoole']['ssl']['ciphers'],
]);

$server->on("start", function (Server $server) use ($config) {
    echo "Swoole server is started at https://{$config['swoole']['host']}:{$config['swoole']['port']}\n";
});

// WebSocket handlers
$webSocketAuthMiddleware = $containerBuilder->get(WebSocketAuthMiddleware::class);

$server->on('open', function (Server $server, $request) use ($webSocketAuthMiddleware, $webSocketRouteCollection, $containerBuilder) {
    $webSocketRequest = new \Src\Core\WebSocketRequest($request);
    $webSocketAuthMiddleware->process($webSocketRequest, $server, function ($request, $server) use ($webSocketRouteCollection, $containerBuilder) {
        $handler = $webSocketRouteCollection->getHandler('open');
        if ($handler) {
            $controller = $containerBuilder->get($handler[0]);
            $controller->{$handler[1]}($server, $request);
        }
    });
});

$server->on('message', function (Server $server, $frame) use ($webSocketRouteCollection, $containerBuilder) {
    $data = json_decode($frame->data, true);
    $event = $data['event'] ?? 'message';
    $handler = $webSocketRouteCollection->getHandler($event);
    if ($handler) {
        $controller = $containerBuilder->get($handler[0]);
        $controller->{$handler[1]}($server, $frame);
    }
});

$server->on('close', function (Server $server, $fd) use ($webSocketRouteCollection, $containerBuilder) {
    $handler = $webSocketRouteCollection->getHandler('close');
    if ($handler) {
        $controller = $containerBuilder->get($handler[0]);
        $controller->{$handler[1]}($server, $fd);
    }
});

$corsMiddleware = new \Src\Middleware\CorsMiddleware($config['cors']);

$server->on("request", function ($request, $response) use ($containerBuilder, $router, $webSocketRouteCollection, $errorHandler) {
    try {
        $httpMethod = $request->server['request_method'];
        $uri = $request->server['request_uri'];

        $routeInfo = $router->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case FastRoute\Dispatcher::NOT_FOUND:
                throw new NotFoundException("Route not found: $uri");
            case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                throw new HttpException("Method not allowed. Allowed methods: " . implode(', ', $allowedMethods), 0, 405);
            case FastRoute\Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                // Execute the handler
                $result = $handler($vars);
                
                // Send the response
                $response->end($result);
                break;
        }
    } catch (Throwable $e) {
        if ($request->header['accept'] === 'application/json') {
            $errorHandler->handleException($e, $response);
        } else {
            $errorHandler->renderErrorPage($e, $response);
        }
    }
});

$server->start();