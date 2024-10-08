<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\OrderServiceInterface;
use Src\Interfaces\LicenseServiceInterface;
use Src\Models\Order;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        private readonly Order $orderModel,
        private readonly LicenseServiceInterface $licenseService
    ) {
    }

    public function createOrder(array $orderData): int
    {
        $orderId = $this->orderModel->create($orderData);

        // Generate licenses for each product in the order
        foreach ($orderData['items'] as $item) {
            $this->licenseService->generateLicense($item['product_id'], $orderData['user_id']);
        }

        return $orderId;
    }

    public function getOrderById(int $orderId): ?array
    {
        return $this->orderModel->getById($orderId);
    }

    public function getOrdersByUserId(int $userId): array
    {
        return $this->orderModel->getByUserId($userId);
    }

    public function updateOrderStatus(int $orderId, string $status): bool
    {
        return $this->orderModel->updateStatus($orderId, $status);
    }

    public function getOrderLicenses(int $orderId): array
    {
        $order = $this->orderModel->getById($orderId);
        $licenses = [];

        foreach ($order['items'] as $item) {
            $licenses[] = $this->licenseService->getLicenseByProductAndUser($item['product_id'], $order['user_id']);
        }

        return $licenses;
    }
}
