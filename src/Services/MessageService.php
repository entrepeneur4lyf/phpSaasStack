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
