<?php

declare(strict_types=1);

namespace Src\Core;

use Psr\Container\ContainerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ErrorHandler $errorHandler
    ) {}

    public function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request, Response $response): void
    {
        $method = $request->server['request_method'];
        $path = $request->server['request_uri'];

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                try {
                    [$controllerName, $actionName] = $route['handler'];
                    $controller = $this->container->get($controllerName);
                    $controller->$actionName($request, $response, $params);
                    return;
                } catch (\Throwable $e) {
                    $this->errorHandler->handleException($e, $response);
                    return;
                }
            }
        }

        $this->errorHandler->handleNotFound($response);
    }

    private function matchPath(string $routePath, string $requestPath, &$params): bool
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];

        foreach ($routeParts as $index => $routePart) {
            if (preg_match('/^{(\w+)}$/', $routePart, $matches)) {
                $params[$matches[1]] = $requestParts[$index];
            } elseif ($routePart !== $requestParts[$index]) {
                return false;
            }
        }

        return true;
    }
}