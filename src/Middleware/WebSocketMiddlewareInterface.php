<?php

declare(strict_types=1);

namespace Src\Middleware;

use Src\Core\WebSocketRequest;
use Swoole\WebSocket\Server;

interface WebSocketMiddlewareInterface
{
    public function process(WebSocketRequest $request, Server $server, callable $next): void;
}
