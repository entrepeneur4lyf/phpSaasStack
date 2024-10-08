<?php

declare(strict_types=1);

namespace Src\Interfaces;

use Swoole\WebSocket\Server;
use Swoole\WebSocket\Frame;

interface WebSocketControllerInterface
{
    public function onMessage(Server $server, Frame $frame): void;
    public function onClose(Server $server, int $fd): void;
}
