<?php

declare(strict_types=1);

namespace Src\Interfaces;

use Src\Models\Product;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?Product;
    public function create(array $productData): Product;
    public function update(Product $product, array $productData): bool;
    public function delete(Product $product): bool;
}
