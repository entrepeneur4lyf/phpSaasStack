<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class Offer
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM offers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM offers WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO offers (user_id, name, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['image_url'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("UPDATE offers SET name = ?, description = ?, price = ?, category_id = ?, image_url = ? WHERE id = ?");
        return $stmt->execute([
            $data['name'],
            $data['description'],
            $data['price'],
            $data['category_id'],
            $data['image_url'] ?? null,
            $id
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM offers WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function search(array $filters): array
    {
        $sql = "SELECT * FROM offers WHERE 1=1";
        $params = [];

        if (!empty($filters['name'])) {
            $sql .= " AND name LIKE ?";
            $params[] = '%' . $filters['name'] . '%';
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (isset($filters['min_price'])) {
            $sql .= " AND price >= ?";
            $params[] = $filters['min_price'];
        }

        if (isset($filters['max_price'])) {
            $sql .= " AND price <= ?";
            $params[] = $filters['max_price'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAll(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM offers LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public function getOffersWithImages(int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT o.*, i.url as image_url 
            FROM offers o
            LEFT JOIN images i ON o.image_id = i.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
}