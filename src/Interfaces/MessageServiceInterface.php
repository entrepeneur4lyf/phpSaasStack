<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface MessageServiceInterface
{
    public function getInboxMessages(int $userId): array;
    public function getSentMessages(int $userId): array;
    public function getMessageById(int $messageId): ?array;
    public function sendMessage(array $messageData): int;
    public function markAsRead(int $messageId): bool;
    public function deleteMessage(int $messageId): bool;
    public function getMessageThread(int $messageId): array;
    public function searchMessages(int $userId, string $query, int $page = 1, int $limit = 20): array;
    public function getAttachmentPath(int $attachmentId, int $userId): ?string;
}
