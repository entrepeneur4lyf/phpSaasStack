<?php

declare(strict_types=1);

use Src\Core\WebSocketRouteCollection;
use Src\Controllers\WebSocketController;

return function (WebSocketRouteCollection $routes) {
    $routes->addRoute('open', [WebSocketController::class, 'onOpen']);
    $routes->addRoute('message', [WebSocketController::class, 'onMessage']);
    $routes->addRoute('close', [WebSocketController::class, 'onClose']);
    $routes->addRoute('error', [WebSocketController::class, 'onError']);
    
    // Add reconnection route
    $routes->addRoute('reconnect', [WebSocketController::class, 'onReconnect']);
    
    // Add more specific WebSocket routes
    $routes->addRoute('userEvent', [WebSocketController::class, 'onUserEvent']);
    $routes->addRoute('productUpdate', [WebSocketController::class, 'onProductUpdate']);
    $routes->addRoute('notification', [WebSocketController::class, 'onNotification']);
    
    // Add a catch-all route for unhandled events
    $routes->addRoute('default', [WebSocketController::class, 'onUnhandledEvent']);
};