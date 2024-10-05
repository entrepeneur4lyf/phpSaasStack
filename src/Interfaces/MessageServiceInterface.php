<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface MessageServiceInterface
{
    public function getMessagesByUserId(int $userId): array;
    public function getMessageById(int $messageId): ?array;
    public function sendMessage(array $messageData): int;
    public function markAsRead(int $messageId): bool;
    public function deleteMessage(int $messageId): bool;
    public function getConversation(int $userId1, int $userId2): array;
    public function getUnreadCount(int $userId): int;
}