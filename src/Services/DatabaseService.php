<?php

declare(strict_types=1);

namespace Src\Services;

use PDO;
use PDOException;
use Src\Core\Database;
use Src\Interfaces\DatabaseServiceInterface;
use Src\Exceptions\DatabaseException;

class DatabaseService implements DatabaseServiceInterface
{
    private PDO $pdo;

    public function __construct(Database $database)
    {
        $this->pdo = $database->getConnection();
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new DatabaseException("Query execution failed: " . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new DatabaseException("Statement execution failed: " . $e->getMessage());
        }
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    public function prepare(string $sql): \PDOStatement
    {
        return $this->pdo->prepare($sql);
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }
}