<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Database\Database;

class Message
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE sender_id = ? OR recipient_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO messages (sender_id, recipient_id, content) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['sender_id'],
            $data['recipient_id'],
            $data['content']
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getConversation(int $userId1, int $userId2): array
    {
        $stmt = $this->db->prepare("SELECT * FROM messages WHERE (sender_id = ? AND recipient_id = ?) OR (sender_id = ? AND recipient_id = ?) ORDER BY created_at ASC");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}