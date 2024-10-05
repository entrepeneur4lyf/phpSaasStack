<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class Post
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getPosts(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO posts (user_id, title, content, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['title'],
            $data['content'],
            $data['status'] ?? 'draft'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE posts SET title = ?, content = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $data['title'],
            $data['content'],
            $data['status'] ?? 'draft',
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search(array $filters): array
    {
        // Implement search logic here
        // This is a basic example and should be expanded based on your specific requirements
        $sql = "SELECT * FROM posts WHERE 1=1";
        $params = [];

        if (!empty($filters['title'])) {
            $sql .= " AND title LIKE ?";
            $params[] = '%' . $filters['title'] . '%';
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function schedulePost(int $id, string $scheduledAt): bool
    {
        $stmt = $this->db->prepare("UPDATE posts SET scheduled_at = ?, status = 'scheduled' WHERE id = ?");
        return $stmt->execute([$scheduledAt, $id]);
    }

    public function toggleFeatured(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE posts SET is_featured = NOT is_featured WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getFeaturedPosts(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE is_featured = 1 ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}