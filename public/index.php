<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Swoole\Http\Server;
use Src\Core\Router;
use Src\Core\ErrorHandler;
use Src\Core\RouteCollection;

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

$router = new Router(
    $containerBuilder,
    $routeCollection,
    $containerBuilder->get(ErrorHandler::class)
);

$server = new Server("0.0.0.0", 9501);

$server->on("start", function (Server $server) {
    echo "Swoole http server is started at http://0.0.0.0:9501\n";
});

$server->on("request", function ($request, $response) use ($router) {
    $router->dispatch($request, $response);
});

$server->start();