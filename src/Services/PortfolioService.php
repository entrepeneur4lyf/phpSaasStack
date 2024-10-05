<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\PortfolioServiceInterface;
use Src\Models\Portfolio;
use Src\Exceptions\ImageUploadException;

class PortfolioService implements PortfolioServiceInterface
{
    public function __construct(
        private readonly Portfolio $portfolioModel
    ) {}

    public function getItemsByUserId(int $userId): array
    {
        return $this->portfolioModel->getItemsByUserId($userId);
    }

    public function addItem(int $userId, array $itemData): bool
    {
        return $this->portfolioModel->addItem($userId, $itemData);
    }

    public function updateItem(int $itemId, array $itemData): bool
    {
        return $this->portfolioModel->updateItem($itemId, $itemData);
    }

    public function deleteItem(int $itemId): bool
    {
        return $this->portfolioModel->deleteItem($itemId);
    }

    public function handleImageUpload(array $file): string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            throw new ImageUploadException('No file uploaded or upload error occurred');
        }

        $uploadDir = __DIR__ . '/../../public/uploads/portfolio/';
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('portfolio_') . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadFile)) {
            throw new ImageUploadException('Failed to upload image');
        }

        return '/uploads/portfolio/' . $fileName;
    }
}