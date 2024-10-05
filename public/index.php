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

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = require __DIR__ . '/../src/Config/Application.php';

$containerBuilder = new ContainerBuilder();
$dependencies = require __DIR__ . '/../src/Config/Dependencies.php';
$dependencies($containerBuilder, $config);
$containerBuilder->compile();

$routeCollection = RouteCollection::getInstance();
$routeDefinitions = require __DIR__ . '/../src/Config/Routes.php';
$routeDefinitions($routeCollection);

$webSocketRouteCollection = new WebSocketRouteCollection();
$webSocketRouteDefinitions = require __DIR__ . '/../src/Config/WebSocketRoutes.php';
$webSocketRouteDefinitions($webSocketRouteCollection);

$router = new Router(
    $containerBuilder,
    $routeCollection,
    $containerBuilder->get(ErrorHandler::class)
);

$server = new Server($config['swoole']['host'], $config['swoole']['port']);

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

$server->on("request", function ($request, $response) use ($router, $corsMiddleware) {
    $corsMiddleware->handle($request, $response, function ($request, $response) use ($router) {
        $router->dispatch($request, $response);
    });
});

$server->start();