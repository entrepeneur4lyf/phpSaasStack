<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Database\Database;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class DatabaseService
{
    private Database $database;

    public function __construct(array $configs, LoggerInterface $logger)
    {
        $this->database = Database::getInstance($configs, $logger);
    }

    public function query(string $sql, array $params = [], string $connectionName = 'default'): array
    {
        try {
            return $this->database->query($sql, $params, true, $connectionName);
        } catch (Throwable $e) {
            // Log the error
            error_log("Database query error: " . $e->getMessage());
            throw new RuntimeException("An error occurred while executing the database query.", 0, $e);
        }
    }

    public function execute(string $sql, array $params = [], string $connectionName = 'default'): int
    {
        try {
            return $this->database->execute($sql, $params, $connectionName);
        } catch (Throwable $e) {
            // Log the error
            error_log("Database execute error: " . $e->getMessage());
            throw new RuntimeException("An error occurred while executing the database command.", 0, $e);
        }
    }

    // Add other methods as needed

    public function close(): void
    {
        $this->database->close();
    }
}
