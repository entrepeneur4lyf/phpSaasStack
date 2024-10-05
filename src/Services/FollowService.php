<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\FollowServiceInterface;
use Src\Models\Follow;
use Src\Exceptions\ValidationException;

class FollowService implements FollowServiceInterface
{
    public function __construct(
        private readonly Follow $followModel
    ) {}

    public function follow(int $followerId, int $followedId): bool
    {
        if ($followerId === $followedId) {
            throw new ValidationException("Users cannot follow themselves");
        }
        return $this->followModel->create($followerId, $followedId);
    }

    public function unfollow(int $followerId, int $followedId): bool
    {
        return $this->followModel->delete($followerId, $followedId);
    }

    public function getFollowers(int $userId): array
    {
        return $this->followModel->getFollowers($userId);
    }

    public function getFollowing(int $userId): array
    {
        return $this->followModel->getFollowing($userId);
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        return $this->followModel->isFollowing($followerId, $followedId);
    }

    public function getFollowersCount(int $userId): int
    {
        return $this->followModel->getFollowersCount($userId);
    }

    public function getFollowingCount(int $userId): int
    {
        return $this->followModel->getFollowingCount($userId);
    }
}