<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Database\Database;

class Comment
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getByPostId(int $postId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at ASC");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function incrementVote(int $id, string $voteField): bool
    {
        $stmt = $this->db->prepare("UPDATE comments SET $voteField = $voteField + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO comments (post_id, user_id, parent_id, content, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['post_id'],
            $data['user_id'],
            $data['parent_id'] ?? null,
            $data['content'],
            $data['status'] ?? 'approved'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE comments SET content = ?, status = ? WHERE id = ?");
        return $stmt->execute([
            $data['content'],
            $data['status'] ?? 'approved',
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getThreadedComments(int $postId): array
    {
        $comments = $this->getByPostId($postId);
        return $this->buildThreads($comments);
    }

    private function buildThreads(array $comments, ?int $parentId = null): array
    {
        $threads = [];
        foreach ($comments as $comment) {
            if ($comment['parent_id'] === $parentId) {
                $comment['replies'] = $this->buildThreads($comments, $comment['id']);
                $threads[] = $comment;
            }
        }
        return $threads;
    }
}