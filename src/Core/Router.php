<?php

declare(strict_types=1);

namespace Src\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

class Router
{
    private array $routes = [];
    private array $middleware = [];
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, string $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function delete(string $path, string $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[] = [
            'method' => strtolower($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function addMiddleware(string $middleware): void
    {
        $this->middleware[] = $middleware;
    }

    public function dispatch(Request $request, Response $response): void
    {
        $path = $request->server['request_uri'];
        $method = strtolower($request->server['request_method']);

        foreach ($this->routes as $route) {
            if ($route['path'] === $path && $route['method'] === $method) {
                $this->runMiddleware($request, $response, function ($request, $response) use ($route) {
                    [$controllerName, $action] = explode('@', $route['handler']);
                    $controller = $this->container->get($controllerName);
                    $controller->$action($request, $response);
                });
                return;
            }
        }

        $response->status(404);
        $response->end('404 Not Found');
    }

    private function runMiddleware(Request $request, Response $response, callable $controller): void
    {
        $next = $controller;

        foreach (array_reverse($this->middleware) as $middleware) {
            $next = function ($request, $response) use ($middleware, $next) {
                $this->container->get($middleware)->process($request, $response, $next);
            };
        }

        $next($request, $response);
    }
}