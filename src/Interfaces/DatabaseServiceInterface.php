<?php

declare(strict_types=1);

namespace Src\Interfaces;

use PDO;

interface DatabaseServiceInterface
{
    public function query(string $sql, array $params = []): array;
    public function execute(string $sql, array $params = []): int;
    public function lastInsertId(): string;
    public function beginTransaction(): void;
    public function commit(): void;
    public function rollBack(): void;
    public function prepare(string $sql): \PDOStatement;
    public function getPdo(): PDO;
}
