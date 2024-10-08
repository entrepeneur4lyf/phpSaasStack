<?php

declare(strict_types=1);

namespace Src\WebSocket;

use Src\Interfaces\JWTServiceInterface;

class WebSocketAuthHandler
{
    public function __construct(private JWTServiceInterface $jwtService)
    {
    }

    public function authenticate($server, $request)
    {
        $token = $request->get['token'] ?? null;

        if (!$token || !$this->jwtService->validateToken($token)) {
            $server->disconnect($request->fd);
            return;
        }

        $payload = $this->jwtService->getPayload($token);
        // Store user information in the connection
        $server->connection_info[$request->fd]['user'] = $payload['user'] ?? null;
    }
}
