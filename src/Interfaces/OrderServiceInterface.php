<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface OrderServiceInterface
{
    public function createOrder(array $orderData): int;
    public function getOrderById(int $orderId): ?array;
    public function getOrdersByUserId(int $userId): array;
    public function updateOrderStatus(int $orderId, string $status): bool;
    public function getOrderLicenses(int $orderId): array;
}