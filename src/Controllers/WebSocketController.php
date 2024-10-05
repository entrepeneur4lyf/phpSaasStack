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