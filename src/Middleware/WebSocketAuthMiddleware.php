<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Core\WebSocketRequest;
use Swoole\WebSocket\Server;
use Src\Services\AuthService;

class WebSocketAuthMiddleware implements WebSocketMiddlewareInterface
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function process(WebSocketRequest $request, Server $server, callable $next): void
    {
        $token = $request->get('token');

        if (!$token || !$this->authService->verifyToken($token)) {
            $server->push($request->getFd(), json_encode([
                'type' => 'error',
                'message' => 'Unauthorized'
            ]));
            return;
        }

        $next($request, $server);
    }
}