<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Core\Database;

class License
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getByProductId(int $productId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE product_id = ?");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO licenses (product_id, user_id, license_key, expiration_date, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['product_id'],
            $data['user_id'],
            $data['license_key'],
            $data['expiration_date'],
            $data['status'] ?? 'active'
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE licenses SET ";
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
        $stmt = $this->db->prepare("DELETE FROM licenses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function validateLicenseKey(string $licenseKey): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE license_key = ?");
        $stmt->execute([$licenseKey]);
        return $stmt->fetch() ?: null;
    }

    public function getActiveLicenses(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE user_id = ? AND status = 'active' AND expiration_date > NOW()");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function extendLicense(int $id, string $newExpirationDate): bool
    {
        $stmt = $this->db->prepare("UPDATE licenses SET expiration_date = ? WHERE id = ?");
        return $stmt->execute([$newExpirationDate, $id]);
    }

    public function revokeLicense(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE licenses SET status = 'revoked' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}