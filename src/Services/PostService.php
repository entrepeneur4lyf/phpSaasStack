<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\PostServiceInterface;
use Src\Interfaces\ModerationServiceInterface;
use Src\Models\Post;

class PostService implements PostServiceInterface
{
    public function __construct(
        private readonly Post $postModel,
        private readonly ModerationServiceInterface $moderationService
    ) {}

    public function getPosts(int $page = 1, int $limit = 20): array
    {
        return $this->postModel->getPosts($page, $limit);
    }

    public function getPostById(int $postId): ?array
    {
        return $this->postModel->getById($postId);
    }

    public function createPost(array $postData): int
    {
        $postId = $this->postModel->create($postData);
        
        // Submit the post for moderation
        $this->moderationService->submitForModeration('post', $postId);
        
        return $postId;
    }

    public function updatePost(int $postId, array $postData): bool
    {
        $result = $this->postModel->update($postId, $postData);
        
        if ($result) {
            // Re-submit the updated post for moderation
            $this->moderationService->submitForModeration('post', $postId);
        }
        
        return $result;
    }

    public function deletePost(int $postId): bool
    {
        return $this->postModel->delete($postId);
    }

    public function searchPosts(array $filters): array
    {
        return $this->postModel->search($filters);
    }

    public function schedulePost(int $postId, string $scheduledAt): bool
    {
        return $this->postModel->schedulePost($postId, $scheduledAt);
    }

    public function toggleFeatured(int $postId): bool
    {
        return $this->postModel->toggleFeatured($postId);
    }

    public function getFeaturedPosts(): array
    {
        return $this->postModel->getFeaturedPosts();
    }

    public function publishScheduledPosts(): int
    {
        $scheduledPosts = $this->postModel->getScheduledPostsDue();
        $publishedCount = 0;

        foreach ($scheduledPosts as $post) {
            $success = $this->postModel->updateStatus($post['id'], 'published');
            if ($success) {
                $publishedCount++;
            }
        }

        return $publishedCount;
    }
}