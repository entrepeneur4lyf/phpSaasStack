<?php

declare(strict_types=1);

namespace Src\Interfaces;

use Src\Models\Category;

interface CategoryServiceInterface
{
    public function getCategoryById(int $id): ?Category;
    public function createCategory(array $categoryData): Category;
    public function updateCategory(int $id, array $categoryData): bool;
    public function deleteCategory(int $id): bool;
    public function getAllCategories(): array;
}
