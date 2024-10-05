<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Middleware\WebSocketMiddlewareInterface;

class WebSocketRouteCollection
{
    private array $routes = [];
    private array $middleware = [];

    public function addRoute(string $event, string $handler): void
    {
        $this->routes[$event] = $handler;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getHandler(string $event): ?string
    {
        return $this->routes[$event] ?? null;
    }

    public function addMiddleware(string $event, WebSocketMiddlewareInterface $middleware): void
    {
        if (!isset($this->middleware[$event])) {
            $this->middleware[$event] = [];
        }
        $this->middleware[$event][] = $middleware;
    }

    public function getMiddleware(string $event): array
    {
        return $this->middleware[$event] ?? [];
    }
}