<?php

declare(strict_types=1);

namespace Src\Controllers;

use Src\Interfaces\WebSocketControllerInterface;
use Swoole\WebSocket\Server;
use Psr\Log\LoggerInterface;

abstract class AbstractWebSocketController implements WebSocketControllerInterface
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function sendMessage(Server $server, int $fd, array $data): void
    {
        $server->push($fd, json_encode($data));
        $this->logger->info('Message sent', ['fd' => $fd, 'data' => $data]);
    }

    protected function broadcastMessage(Server $server, array $fds, array $data): void
    {
        foreach ($fds as $fd) {
            $this->sendMessage($server, $fd, $data);
        }
        $this->logger->info('Message broadcasted', ['fds' => $fds, 'data' => $data]);
    }

    protected function closeConnection(Server $server, int $fd, int $code = 1000, string $reason = ''): void
    {
        $server->disconnect($fd, $code, $reason);
        $this->logger->info('Connection closed', ['fd' => $fd, 'code' => $code, 'reason' => $reason]);
    }

    protected function logError(\Throwable $e): void
    {
        $this->logger->error('WebSocket error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}