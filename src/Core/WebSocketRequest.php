<?php

declare(strict_types=1);

namespace Src\Core;

use Swoole\WebSocket\Frame;

class WebSocketRequest
{
    private string $event;
    private array $data;
    private int $fd;

    public function __construct(Frame $frame)
    {
        $payload = json_decode($frame->data, true);
        $this->event = $payload['type'] ?? '';
        $this->data = $payload['data'] ?? [];
        $this->fd = $frame->fd;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getFd(): int
    {
        return $this->fd;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
