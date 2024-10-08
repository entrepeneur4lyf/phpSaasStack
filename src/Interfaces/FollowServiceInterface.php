<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface FollowServiceInterface
{
    public function follow(int $followerId, int $followedId): bool;
    public function unfollow(int $followerId, int $followedId): bool;
    public function getFollowers(int $userId): array;
    public function getFollowing(int $userId): array;
    public function isFollowing(int $followerId, int $followedId): bool;
    public function getFollowersCount(int $userId): int;
    public function getFollowingCount(int $userId): int;
}
