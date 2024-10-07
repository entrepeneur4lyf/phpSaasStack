<?php

declare(strict_types=1);

namespace Src\Services;

class NotificationService
{
    public function handleNewMessage(array $data): array
    {
        return [
            'type' => 'newMessage',
            'title' => 'New Message',
            'content' => "You have a new message from {$data['sender']}",
            'data' => $data
        ];
    }

    public function handleNewOrder(array $data): array
    {
        return [
            'type' => 'newOrder',
            'title' => 'New Order',
            'content' => "You have received a new order (#${data['orderId']})",
            'data' => $data
        ];
    }

    public function handleProductUpdate(array $data): array
    {
        return [
            'type' => 'productUpdate',
            'title' => 'Product Updated',
            'content' => "The product '{$data['productName']}' has been updated",
            'data' => $data
        ];
    }
}
