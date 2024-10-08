<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\ProductServiceInterface;
use Src\Interfaces\LicenseServiceInterface;
use Src\Models\Product;

class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly Product $productModel,
        private readonly LicenseServiceInterface $licenseService
    ) {
    }

    public function getProducts(int $page = 1, int $limit = 20): array
    {
        return $this->productModel->getAll($page, $limit);
    }

    public function getProductById(int $productId): ?array
    {
        return $this->productModel->getById($productId);
    }

    public function createProduct(array $productData): int
    {
        $productId = $this->productModel->create($productData);

        if (isset($productData['license_type'])) {
            $this->licenseService->createLicenseType($productId, $productData['license_type']);
        }

        return $productId;
    }

    public function updateProduct(int $productId, array $productData): bool
    {
        $result = $this->productModel->update($productId, $productData);

        if ($result && isset($productData['license_type'])) {
            $this->licenseService->updateLicenseType($productId, $productData['license_type']);
        }

        return $result;
    }

    public function deleteProduct(int $productId): bool
    {
        return $this->productModel->delete($productId);
    }

    public function searchProducts(array $filters): array
    {
        return $this->productModel->search($filters);
    }

    public function getProductAnalytics(int $productId): array
    {
        return $this->productModel->getAnalytics($productId);
    }

    public function addProductAsset(int $productId, array $fileData, int $userId): int
    {
        $assetId = $this->assetService->uploadAsset($fileData, $userId);
        $this->productModel->addAsset($productId, $assetId);
        return $assetId;
    }

    public function removeProductAsset(int $productId, int $assetId): bool
    {
        $this->productModel->removeAsset($productId, $assetId);
        return $this->assetService->deleteAsset($assetId);
    }

    public function getProductAssets(int $productId): array
    {
        $assetIds = $this->productModel->getAssetIds($productId);
        return array_map(fn ($id) => $this->assetService->getAssetById($id), $assetIds);
    }

    public function getProductLicenseInfo(int $productId): array
    {
        return $this->licenseService->getLicenseTypeByProductId($productId);
    }
}
