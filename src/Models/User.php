<?php

declare(strict_types=1);

namespace Src\Models;

use Src\Database\Database;

class User
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function create(array $data): ?object
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role'],
            $data['verification_token']
        ])) {
            return $this->findById((int)$this->db->lastInsertId());
        }
        return null;
    }

    public function findById(int $id): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchObject(__CLASS__) ?: null;
    }

    public function findByEmail(string $email): ?object
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetchObject(__CLASS__) ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $allowedFields = ['username', 'email', 'role', 'is_verified', 'verification_token', 'reset_token', 'reset_token_expiry', 'two_factor_secret'];
        $updateFields = [];
        $updateValues = [];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "$field = ?";
                $updateValues[] = $data[$field];
            }
        }

        if (empty($updateFields)) {
            return false;
        }

        $updateValues[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($updateValues);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
    }

    public function getTotalCount(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return (int)$stmt->fetchColumn();
    }
}