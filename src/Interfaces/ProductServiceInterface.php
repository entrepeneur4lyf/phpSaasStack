<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ProductServiceInterface
{
    public function getProducts(int $page = 1, int $limit = 20): array;
    public function getProductById(int $productId): ?array;
    public function createProduct(array $productData): int;
    public function updateProduct(int $productId, array $productData): bool;
    public function deleteProduct(int $productId): bool;
    public function searchProducts(array $filters): array;
    public function getProductAnalytics(int $productId): array;
}
