<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface SellerServiceInterface
{
    public function getProductById(int $productId): ?array;
    public function updateRelatedProducts(int $productId, array $relatedProductIds): bool;
    public function updateServices(int $sellerId, array $services): bool;
    public function getSellerById(int $sellerId): ?array;
    public function getSellerProducts(int $sellerId): array;
    public function getSellerOffers(int $sellerId): array;
}