<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface AssetServiceInterface
{
    public function uploadAsset(array $fileData, int $userId): int;
    public function getAssetById(int $assetId): ?array;
    public function getAssetsByUserId(int $userId): array;
    public function updateAsset(int $assetId, array $assetData): bool;
    public function deleteAsset(int $assetId): bool;
    public function getAssetDownloadUrl(int $assetId, int $userId): string;
    public function validateAssetAccess(int $assetId, int $userId): bool;
    public function cropImage(int $assetId, array $cropData): bool;
    public function getAssetMetadata(int $assetId): array;
    public function logDownload(int $assetId, int $userId): void;
    public function getDownloadHistory(int $assetId): array;
    public function getDownloadCount(int $assetId): int;
}
