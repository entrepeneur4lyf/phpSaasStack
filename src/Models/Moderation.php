<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class Moderation
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function flagContent(int $contentId, string $contentType, int $reporterId, string $reason): bool
    {
        $stmt = $this->db->prepare("INSERT INTO flagged_content (content_id, content_type, reporter_id, reason) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$contentId, $contentType, $reporterId, $reason]);
    }

    public function getFlaggedContentDetails(int $contentId, string $contentType): array
    {
        $stmt = $this->db->prepare("SELECT * FROM flagged_content WHERE content_id = ? AND content_type = ?");
        $stmt->execute([$contentId, $contentType]);
        return $stmt->fetchAll();
    }

    public function updateContentStatus(int $contentId, string $contentType, string $status, ?string $reason = null): bool
    {
        $stmt = $this->db->prepare("UPDATE flagged_content SET status = ?, moderation_reason = ? WHERE content_id = ? AND content_type = ?");
        return $stmt->execute([$status, $reason, $contentId, $contentType]);
    }

    public function getFlaggedContent(int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM flagged_content ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getContentStatus(int $contentId, string $contentType): string
    {
        $stmt = $this->db->prepare("SELECT status FROM flagged_content WHERE content_id = ? AND content_type = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$contentId, $contentType]);
        $result = $stmt->fetch();
        return $result ? $result['status'] : 'not_flagged';
    }

    public function getTotalFlaggedContentCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM flagged_content");
        return (int) $stmt->fetchColumn();
    }

    public function getFlaggedContentByStatus(string $status, int $page = 1, int $limit = 20): array
    {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("SELECT * FROM flagged_content WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$status, $limit, $offset]);
        return $stmt->fetchAll();
    }
}