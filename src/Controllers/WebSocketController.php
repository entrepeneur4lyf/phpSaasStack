<?php

namespace App\Controllers;

use App\Core\WebSocketRequest;
use App\Services\AuthService;
use App\Services\WebSocketService;

class WebSocketController extends AbstractWebSocketController
{
    protected $webSocketService;
    protected $authService;

    public function __construct(WebSocketService $webSocketService, AuthService $authService)
    {
        $this->webSocketService = $webSocketService;
        $this->authService = $authService;
    }

    public function onOpen(WebSocketRequest $request)
    {
        $token = $request->get('token');
        $user = $this->authService->getUserFromToken($token);

        if (!$user) {
            $request->close();
            return;
        }

        $this->webSocketService->addConnection($user->id, $request->fd);
    }

    public function onMessage(WebSocketRequest $request)
    {
        $message = $request->getData();
        $user = $this->authService->getUserFromFd($request->fd);

        if (!$user) {
            $request->close();
            return;
        }

        $this->webSocketService->handleMessage($user->id, $message);
    }

    public function onClose(WebSocketRequest $request)
    {
        $this->webSocketService->removeConnection($request->fd);
    }

    public function broadcastMessage($message)
    {
        $this->webSocketService->broadcastMessage($message);
    }

    public function sendPrivateMessage($userId, $message)
    {
        $this->webSocketService->sendPrivateMessage($userId, $message);
    }
}
use Swoole\WebSocket\Server;
use Swoole\WebSocket\Frame;
use Psr\Log\LoggerInterface;

class WebSocketController
{
    private WebSocketRouteCollection $routes;
    private LoggerInterface $logger;

    public function __construct(WebSocketRouteCollection $routes, LoggerInterface $logger)
    {
        $this->routes = $routes;
        $this->logger = $logger;
    }

    public function onOpen(Server $server, $request)
    {
        $this->logger->info("New WebSocket connection opened: {$request->fd}");
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $data = json_decode($frame->data, true);
        $event = $data['event'] ?? '';
        $payload = $data['payload'] ?? [];

        $handler = $this->routes->getHandler($event);
        if ($handler) {
            call_user_func($handler, $server, $frame->fd, $payload);
        } else {
            $this->logger->warning("No handler found for event: {$event}");
        }
    }

    public function onClose(Server $server, int $fd)
    {
        $this->logger->info("WebSocket connection closed: {$fd}");
    }

    public function sendNotification(Server $server, int $fd, string $type, array $data)
    {
        $handler = $this->routes->getNotificationHandler($type);
        if ($handler) {
            $notification = call_user_func($handler, $data);
            $server->push($fd, json_encode($notification));
        } else {
            $this->logger->warning("No notification handler found for type: {$type}");
        }
    }
}
