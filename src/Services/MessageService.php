<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\MessageServiceInterface;
use Src\Models\Message;
use Src\Exceptions\ValidationException;

class MessageService implements MessageServiceInterface
{
    public function __construct(
        private readonly Message $messageModel
    ) {
    }

    public function getMessagesByUserId(int $userId): array
    {
        return $this->messageModel->getByUserId($userId);
    }

    public function getMessageById(int $messageId): ?array
    {
        return $this->messageModel->getById($messageId);
    }

    public function sendMessage(array $messageData): int
    {
        $this->validateMessageData($messageData);
        return $this->messageModel->create($messageData);
    }

    public function markAsRead(int $messageId): bool
    {
        return $this->messageModel->markAsRead($messageId);
    }

    public function deleteMessage(int $messageId): bool
    {
        return $this->messageModel->delete($messageId);
    }

    public function getConversation(int $userId1, int $userId2): array
    {
        return $this->messageModel->getConversation($userId1, $userId2);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->messageModel->getUnreadCount($userId);
    }

    private function validateMessageData(array $messageData): void
    {
        if (empty($messageData['sender_id'])) {
            throw new ValidationException("Sender ID is required");
        }

        if (empty($messageData['recipient_id'])) {
            throw new ValidationException("Recipient ID is required");
        }

        if (empty($messageData['content'])) {
            throw new ValidationException("Message content is required");
        }

        if (strlen($messageData['content']) > 5000) {
            throw new ValidationException("Message content cannot exceed 5000 characters");
        }
    }
}
<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\MessageServiceInterface;
use Src\Interfaces\DatabaseServiceInterface;

class MessageService implements MessageServiceInterface
{
    private DatabaseServiceInterface $db;

    public function __construct(DatabaseServiceInterface $db)
    {
        $this->db = $db;
    }

    public function getInboxMessages(int $userId): array
    {
        return $this->db->query("SELECT * FROM messages WHERE recipient_id = ? ORDER BY created_at DESC", [$userId]);
    }

    public function getSentMessages(int $userId): array
    {
        return $this->db->query("SELECT * FROM messages WHERE sender_id = ? ORDER BY created_at DESC", [$userId]);
    }

    public function getMessageById(int $messageId): ?array
    {
        $result = $this->db->query("SELECT * FROM messages WHERE id = ?", [$messageId]);
        return $result ? $result[0] : null;
    }

    public function sendMessage(array $messageData): int
    {
        $this->db->execute(
            "INSERT INTO messages (sender_id, recipient_id, subject, content, parent_id) VALUES (?, ?, ?, ?, ?)",
            [
                $messageData['sender_id'],
                $messageData['recipient_id'],
                $messageData['subject'],
                $messageData['content'],
                $messageData['parent_id'] ?? null
            ]
        );

        $messageId = $this->db->lastInsertId();

        // Handle attachments
        if (!empty($messageData['attachments'])) {
            foreach ($messageData['attachments'] as $attachmentPath) {
                $this->db->execute(
                    "INSERT INTO message_attachments (message_id, file_path) VALUES (?, ?)",
                    [$messageId, $attachmentPath]
                );
            }
        }

        return $messageId;
    }

    public function markAsRead(int $messageId): bool
    {
        return $this->db->execute("UPDATE messages SET read = 1 WHERE id = ?", [$messageId]) > 0;
    }

    public function deleteMessage(int $messageId): bool
    {
        return $this->db->execute("DELETE FROM messages WHERE id = ?", [$messageId]) > 0;
    }

    public function getMessageThread(int $messageId): array
    {
        $rootMessage = $this->getMessageById($messageId);
        if (!$rootMessage) {
            return [];
        }

        $threadId = $rootMessage['parent_id'] ?? $messageId;
        return $this->db->query(
            "SELECT * FROM messages WHERE id = ? OR parent_id = ? ORDER BY created_at ASC",
            [$threadId, $threadId]
        );
    }

    public function searchMessages(int $userId, string $query, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $searchQuery = "%{$query}%";
        return $this->db->query(
            "SELECT * FROM messages 
            WHERE (sender_id = ? OR recipient_id = ?) 
            AND (subject LIKE ? OR content LIKE ?) 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?",
            [$userId, $userId, $searchQuery, $searchQuery, $limit, $offset]
        );
    }

    public function getAttachmentPath(int $attachmentId, int $userId): ?string
    {
        $result = $this->db->query(
            "SELECT ma.file_path 
            FROM message_attachments ma 
            JOIN messages m ON ma.message_id = m.id 
            WHERE ma.id = ? AND (m.sender_id = ? OR m.recipient_id = ?)",
            [$attachmentId, $userId, $userId]
        );

        return $result ? $result[0]['file_path'] : null;
    }
}
