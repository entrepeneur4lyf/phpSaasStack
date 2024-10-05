<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface PostServiceInterface
{
    public function getPosts(int $page = 1, int $limit = 20): array;
    public function getPostById(int $postId): ?array;
    public function createPost(array $postData): int;
    public function updatePost(int $postId, array $postData): bool;
    public function deletePost(int $postId): bool;
    public function searchPosts(array $filters): array;
    public function schedulePost(int $postId, string $scheduledAt): bool;
    public function toggleFeatured(int $postId): bool;
    public function getFeaturedPosts(): array;
    public function publishScheduledPosts(): int;
}