<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class Follow
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function create(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare("INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)");
        return $stmt->execute([$followerId, $followedId]);
    }

    public function delete(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
        return $stmt->execute([$followerId, $followedId]);
    }

    public function getFollowers(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT u.* FROM users u JOIN follows f ON u.id = f.follower_id WHERE f.followed_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getFollowing(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT u.* FROM users u JOIN follows f ON u.id = f.followed_id WHERE f.follower_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function isFollowing(int $followerId, int $followedId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND followed_id = ?");
        $stmt->execute([$followerId, $followedId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getFollowersCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE followed_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function getFollowingCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}