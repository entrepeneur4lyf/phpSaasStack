<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface ModerationServiceInterface
{
    public function moderateContent(string $content, string $contentType): array;
    public function flagContent(int $contentId, string $contentType, int $reporterId, string $reason): bool;
    public function reviewFlaggedContent(int $contentId, string $contentType): array;
    public function approveContent(int $contentId, string $contentType): bool;
    public function rejectContent(int $contentId, string $contentType, string $reason): bool;
    public function getFlaggedContent(int $page = 1, int $limit = 20): array;
    public function getContentStatus(int $contentId, string $contentType): string;
}
