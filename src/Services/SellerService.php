<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\SellerServiceInterface;
use Src\Interfaces\AssetServiceInterface;
use Src\Models\Seller;

class SellerService implements SellerServiceInterface
{
    public function __construct(
        private readonly Seller $sellerModel,
        private readonly AssetServiceInterface $assetService
    ) {}

    public function getProductById(int $productId): ?array
    {
        return $this->sellerModel->getProductById($productId);
    }

    public function updateRelatedProducts(int $productId, array $relatedProductIds): bool
    {
        return $this->sellerModel->updateRelatedProducts($productId, $relatedProductIds);
    }

    public function updateServices(int $sellerId, array $services): bool
    {
        return $this->sellerModel->updateServices($sellerId, $services);
    }

    public function getSellerById(int $sellerId): ?array
    {
        return $this->sellerModel->getSellerById($sellerId);
    }

    public function getSellerProducts(int $sellerId): array
    {
        return $this->sellerModel->getSellerProducts($sellerId);
    }

    public function getSellerOffers(int $sellerId): array
    {
        return $this->sellerModel->getSellerOffers($sellerId);
    }

    public function addSellerAsset(int $sellerId, array $fileData): int
    {
        $assetId = $this->assetService->uploadAsset($fileData, $sellerId);
        $this->sellerModel->addAsset($sellerId, $assetId);
        return $assetId;
    }

    public function removeSellerAsset(int $sellerId, int $assetId): bool
    {
        $this->sellerModel->removeAsset($sellerId, $assetId);
        return $this->assetService->deleteAsset($assetId);
    }

    public function getSellerAssets(int $sellerId): array
    {
        $assetIds = $this->sellerModel->getAssetIds($sellerId);
        return array_map(fn($id) => $this->assetService->getAssetById($id), $assetIds);
    }
}