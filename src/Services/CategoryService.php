<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\CategoryServiceInterface;
use Src\Interfaces\CategoryRepositoryInterface;
use Src\Models\Category;

class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepository
    ) {
    }

    public function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepository->findById($id);
    }

    public function createCategory(array $categoryData): Category
    {
        return $this->categoryRepository->create($categoryData);
    }

    public function updateCategory(int $id, array $categoryData): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false;
        }

        return $this->categoryRepository->update($category, $categoryData);
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);
        if (!$category) {
            return false;
        }

        return $this->categoryRepository->delete($category);
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}