<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class Download
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare("INSERT INTO downloads (asset_id, user_id, download_date) VALUES (?, ?, ?)");
        return $stmt->execute([
            $data['asset_id'],
            $data['user_id'],
            $data['download_date']
        ]);
    }

    public function getByAssetId(int $assetId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM downloads WHERE asset_id = ? ORDER BY download_date DESC");
        $stmt->execute([$assetId]);
        return $stmt->fetchAll();
    }

    public function getCountByAssetId(int $assetId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM downloads WHERE asset_id = ?");
        $stmt->execute([$assetId]);
        return (int) $stmt->fetchColumn();
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM downloads WHERE user_id = ? ORDER BY download_date DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getRecentDownloads(int $limit = 10): array
    {
        $stmt = $this->db->prepare("SELECT d.*, a.file_name, u.username FROM downloads d 
                                    JOIN assets a ON d.asset_id = a.id 
                                    JOIN users u ON d.user_id = u.id 
                                    ORDER BY d.download_date DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}