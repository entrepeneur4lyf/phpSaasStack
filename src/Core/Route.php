<?php

declare(strict_types=1);

namespace Src\Core;

class Route
{
    private string $method;
    private string $path;
    private array $handler;

    public function __construct(string $method, string $path, array $handler)
    {
        $this->method = $method;
        $this->path = $path;
        $this->handler = $handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getHandler(): array
    {
        return $this->handler;
    }
}
