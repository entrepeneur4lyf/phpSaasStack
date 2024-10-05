<?php

declare(strict_types=1);

namespace Src\Core;

use FastRoute\RouteCollector;

class RouteCollection
{
    private static ?self $instance = null;
    private array $routes = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = new Route($method, $path, $handler);
    }

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->add('PUT', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->add('DELETE', $path, $handler);
    }

    public function addToCollector(RouteCollector $r): void
    {
        foreach ($this->routes as $route) {
            $r->addRoute($route->getMethod(), $route->getPath(), $route->getHandler());
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserializing of the instance
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}