<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Database\Database;

class Asset
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM assets WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM assets WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO assets (user_id, file_name, file_path, file_size, file_type, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['file_name'],
            $data['file_path'],
            $data['file_size'],
            $data['file_type'],
            $data['status'] ?? 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE assets SET ";
        $params = [];
        foreach ($data as $key => $value) {
            $sql .= "$key = ?, ";
            $params[] = $value;
        }
        $sql = rtrim($sql, ", ");
        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM assets WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search(array $filters): array
    {
        $sql = "SELECT * FROM assets WHERE 1=1";
        $params = [];

        if (!empty($filters['file_name'])) {
            $sql .= " AND file_name LIKE ?";
            $params[] = '%' . $filters['file_name'] . '%';
        }

        if (!empty($filters['file_type'])) {
            $sql .= " AND file_type = ?";
            $params[] = $filters['file_type'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filters['status'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getDownloadCount(int $assetId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM downloads WHERE asset_id = ?");
        $stmt->execute([$assetId]);
        return (int) $stmt->fetchColumn();
    }

    public function getRecentDownloads(int $assetId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, u.username 
            FROM downloads d
            JOIN users u ON d.user_id = u.id
            WHERE d.asset_id = ?
            ORDER BY d.download_date DESC
            LIMIT ?
        ");
        $stmt->execute([$assetId, $limit]);
        return $stmt->fetchAll();
    }
}