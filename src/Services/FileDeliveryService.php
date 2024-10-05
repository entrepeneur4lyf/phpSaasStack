<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\FileDeliveryServiceInterface;
use Src\Models\Asset;
use Src\Models\Download;
use Src\Exceptions\AccessDeniedException;
use Src\Exceptions\AssetNotFoundException;

class FileDeliveryService implements FileDeliveryServiceInterface
{
    public function __construct(
        private readonly Asset $assetModel,
        private readonly Download $downloadModel
    ) {}

    public function generateDownloadUrl(int $assetId, int $userId): string
    {
        if (!$this->validateDownloadAccess($assetId, $userId)) {
            throw new AccessDeniedException('User does not have access to this asset');
        }

        $token = bin2hex(random_bytes(16));
        $expirationTime = time() + 3600; // URL valid for 1 hour

        // Store the token and expiration time (you might want to use a cache or database for this)
        // For simplicity, we'll assume there's a method to store this information
        $this->storeDownloadToken($assetId, $userId, $token, $expirationTime);

        return "/download/{$assetId}/{$token}";
    }

    public function validateDownloadAccess(int $assetId, int $userId): bool
    {
        // Implement logic to check if the user has access to the asset
        // This could involve checking purchases, subscriptions, or permissions
        // For simplicity, we'll assume there's a method to check this
        return $this->checkUserAssetAccess($userId, $assetId);
    }

    public function getAssetMetadata(int $assetId): array
    {
        $asset = $this->assetModel->getById($assetId);
        if (!$asset) {
            throw new AssetNotFoundException('Asset not found');
        }
        return [
            'id' => $asset['id'],
            'name' => $asset['file_name'],
            'size' => $asset['file_size'],
            'type' => $asset['file_type'],
            // Add any other relevant metadata
        ];
    }

    public function logDownload(int $assetId, int $userId): void
    {
        $this->downloadModel->create([
            'asset_id' => $assetId,
            'user_id' => $userId,
            'download_date' => date('Y-m-d H:i:s'),
        ]);
    }

    public function getDownloadHistory(int $assetId): array
    {
        return $this->downloadModel->getByAssetId($assetId);
    }

    public function getDownloadCount(int $assetId): int
    {
        return $this->downloadModel->getCountByAssetId($assetId);
    }

    public function streamFile(int $assetId, int $userId): void
    {
        if (!$this->validateDownloadAccess($assetId, $userId)) {
            throw new AccessDeniedException('User does not have access to this asset');
        }

        $asset = $this->assetModel->getById($assetId);
        if (!$asset) {
            throw new AssetNotFoundException('Asset not found');
        }

        $filePath = $asset['file_path'];
        if (!file_exists($filePath)) {
            throw new AssetNotFoundException('Asset file not found');
        }

        $this->logDownload($assetId, $userId);

        header('Content-Type: ' . $asset['file_type']);
        header('Content-Disposition: attachment; filename="' . $asset['file_name'] . '"');
        header('Content-Length: ' . $asset['file_size']);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filePath);
    }

    private function storeDownloadToken(int $assetId, int $userId, string $token, int $expirationTime): void
    {
        // Implement token storage logic
        // This could involve storing in a database or cache
    }

    private function checkUserAssetAccess(int $userId, int $assetId): bool
    {
        // Implement access check logic
        // This could involve checking purchases, subscriptions, or permissions
        return true; // Placeholder return
    }
}