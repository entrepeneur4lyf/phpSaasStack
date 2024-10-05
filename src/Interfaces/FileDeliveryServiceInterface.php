<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface FileDeliveryServiceInterface
{
    public function generateDownloadUrl(int $assetId, int $userId): string;
    public function validateDownloadAccess(int $assetId, int $userId): bool;
    public function getAssetMetadata(int $assetId): array;
    public function logDownload(int $assetId, int $userId): void;
    public function getDownloadHistory(int $assetId): array;
    public function getDownloadCount(int $assetId): int;
    public function streamFile(int $assetId, int $userId): void;
}