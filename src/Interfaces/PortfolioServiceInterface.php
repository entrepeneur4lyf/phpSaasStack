<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface PortfolioServiceInterface
{
    public function getItemsByUserId(int $userId): array;
    public function addItem(int $userId, array $itemData): bool;
    public function updateItem(int $itemId, array $itemData): bool;
    public function deleteItem(int $itemId): bool;
    public function handleImageUpload(array $file): string;
}