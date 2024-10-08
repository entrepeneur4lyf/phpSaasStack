<?php

declare(strict_types=1);

namespace Src\Database;

use Swoole\Coroutine\MySQL;
use Swoole\Database\MysqlConfig;
use Swoole\Database\MysqlPool;
use RuntimeException;

class CoroutineMySQLPool
{
    private MysqlPool $pool;
    private int $maxRetries = 3;

    public function __construct(
        private readonly MysqlConfig $config,
        private readonly int $size = 64
    ) {
        $this->pool = new MysqlPool($this->config, $this->size);
    }

    public function get(): MySQL
    {
        $retries = 0;
        while ($retries < $this->maxRetries) {
            try {
                $connection = $this->pool->get();
                if ($connection->connected) {
                    return $connection;
                }
                $this->pool->put(null);
            } catch (\Throwable $e) {
                $retries++;
                if ($retries >= $this->maxRetries) {
                    throw new RuntimeException("Failed to get a valid database connection after {$this->maxRetries} attempts.", 0, $e);
                }
            }
        }
        throw new RuntimeException("Failed to get a valid database connection.");
    }

    public function put(MySQL $connection): void
    {
        $this->pool->put($connection);
    }

    public function close(): void
    {
        $this->pool->close();
    }
}
