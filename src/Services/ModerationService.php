<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\ModerationServiceInterface;
use Src\Models\Moderation;
use Src\Exceptions\ValidationException;

class ModerationService implements ModerationServiceInterface
{
    public function __construct(
        private readonly Moderation $moderationModel
    ) {}

    public function moderateContent(string $content, string $contentType): array
    {
        // Implement content moderation logic here
        // This could involve using AI services or other moderation tools
        // For now, we'll use a simple profanity filter as an example
        $profanityList = ['badword1', 'badword2', 'badword3'];
        $containsProfanity = false;
        foreach ($profanityList as $word) {
            if (stripos($content, $word) !== false) {
                $containsProfanity = true;
                break;
            }
        }

        return [
            'is_appropriate' => !$containsProfanity,
            'reason' => $containsProfanity ? 'Content contains inappropriate language' : null,
        ];
    }

    public function flagContent(int $contentId, string $contentType, int $reporterId, string $reason): bool
    {
        $this->validateContentType($contentType);
        return $this->moderationModel->flagContent($contentId, $contentType, $reporterId, $reason);
    }

    public function reviewFlaggedContent(int $contentId, string $contentType): array
    {
        $this->validateContentType($contentType);
        return $this->moderationModel->getFlaggedContentDetails($contentId, $contentType);
    }

    public function approveContent(int $contentId, string $contentType): bool
    {
        $this->validateContentType($contentType);
        return $this->moderationModel->updateContentStatus($contentId, $contentType, 'approved');
    }

    public function rejectContent(int $contentId, string $contentType, string $reason): bool
    {
        $this->validateContentType($contentType);
        return $this->moderationModel->updateContentStatus($contentId, $contentType, 'rejected', $reason);
    }

    public function getFlaggedContent(int $page = 1, int $limit = 20): array
    {
        return $this->moderationModel->getFlaggedContent($page, $limit);
    }

    public function getContentStatus(int $contentId, string $contentType): string
    {
        $this->validateContentType($contentType);
        return $this->moderationModel->getContentStatus($contentId, $contentType);
    }

    private function validateContentType(string $contentType): void
    {
        $validTypes = ['post', 'comment', 'product', 'offer', 'message'];
        if (!in_array($contentType, $validTypes)) {
            throw new ValidationException("Invalid content type: $contentType");
        }
    }
}