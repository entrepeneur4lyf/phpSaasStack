<?php

declare(strict_types=1);

namespace Src\Database;

use Src\Database\CoroutineMySQLPool;
use Src\Database\QueryLogger;
use Src\Exceptions\DatabaseException;
use Swoole\Database\MysqlConfig;
use Swoole\Coroutine\MySQL;
use Psr\Log\LoggerInterface;
use Src\Database\DatabaseProfiler;

class Database
{
    private static ?self $instance = null;
    private array $pools = [];
    private ?MySQL $transactionConnection = null;
    private CacheManager $cacheManager;
    private int $queryCacheTTL = 3600; // 1 hour default TTL for query cache
    private LoggerInterface $logger;
    private float $slowQueryThreshold = 1.0; // 1 second
    private QueryLogger $queryLogger;
    private DatabaseProfiler $profiler;

    private function __construct(array $configs, LoggerInterface $logger)
    {
        try {
            foreach ($configs as $name => $config) {
                $mysqlConfig = new MysqlConfig();
                $mysqlConfig->withHost($config['host'])
                    ->withPort($config['port'])
                    ->withDbName($config['database'])
                    ->withCharset($config['charset'])
                    ->withUsername($config['username'])
                    ->withPassword($config['password']);

                $this->pools[$name] = new CoroutineMySQLPool($mysqlConfig, $config['pool_size'] ?? 64);
            }
            $this->cacheManager = CacheManager::getInstance();
            $this->logger = $logger;
            $this->queryLogger = new QueryLogger($logger, $configs['default']['slow_query_threshold'] ?? 1.0);
            $this->profiler = new DatabaseProfiler($logger, $configs['default']['slow_query_threshold'] ?? 1.0);
        } catch (\Throwable $e) {
            throw new DatabaseException("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance(array $configs, LoggerInterface $logger): self
    {
        if (self::$instance === null) {
            self::$instance = new self($configs, $logger);
        }
        return self::$instance;
    }

    public function getConnection(string $name = 'default'): MySQL
    {
        if (!isset($this->pools[$name])) {
            throw new DatabaseException("Unknown database connection: $name");
        }
        return $this->transactionConnection ?? $this->pools[$name]->get();
    }

    public function releaseConnection(MySQL $connection, string $name = 'default'): void
    {
        if ($this->transactionConnection === null) {
            $this->pools[$name]->put($connection);
        }
    }

    public function query(string $sql, array $params = [], bool $useCache = true, string $connectionName = 'default'): array
    {
        $start = $this->profiler->startQuery($sql, $params);
        $cacheKey = $this->generateCacheKey($sql, $params);

        if ($useCache) {
            $cachedResult = $this->cacheManager->get($cacheKey);
            if ($cachedResult !== null) {
                $this->profiler->endQuery($start, $sql, $params, true);
                return $cachedResult;
            }
        }

        $connection = $this->getConnection($connectionName);
        try {
            $statement = $connection->prepare($sql);
            $result = $statement->execute($params);
            $data = $result ? $statement->fetchAll() : [];

            if ($useCache) {
                $this->cacheManager->set($cacheKey, $data, $this->queryCacheTTL);
            }

            $this->profiler->endQuery($start, $sql, $params, false);

            return $data;
        } finally {
            $this->releaseConnection($connection, $connectionName);
        }
    }

    public function execute(string $sql, array $params = [], string $connectionName = 'default'): int
    {
        $start = $this->profiler->startQuery($sql, $params);
        $connection = $this->getConnection($connectionName);
        try {
            $statement = $connection->prepare($sql);
            $result = $statement->execute($params);
            $affectedRows = $result ? $statement->affected_rows : 0;

            $this->profiler->endQuery($start, $sql, $params, false);

            // Clear cache for write operations
            $this->clearQueryCache();

            return $affectedRows;
        } finally {
            $this->releaseConnection($connection, $connectionName);
        }
    }

    public function beginTransaction(string $connectionName = 'default'): void
    {
        if ($this->transactionConnection !== null) {
            throw new DatabaseException("A transaction is already in progress");
        }
        $this->transactionConnection = $this->getConnection($connectionName);
        $this->transactionConnection->begin();
    }

    public function commit(): void
    {
        if ($this->transactionConnection === null) {
            throw new DatabaseException("No transaction in progress");
        }
        $this->transactionConnection->commit();
        $this->pool->put($this->transactionConnection);
        $this->transactionConnection = null;
    }

    public function rollBack(): void
    {
        if ($this->transactionConnection === null) {
            throw new DatabaseException("No transaction in progress");
        }
        $this->transactionConnection->rollback();
        $this->pool->put($this->transactionConnection);
        $this->transactionConnection = null;
    }

    public function lastInsertId(): int
    {
        $connection = $this->getConnection();
        try {
            return $connection->insert_id;
        } finally {
            $this->releaseConnection($connection);
        }
    }

    public function close(): void
    {
        if ($this->transactionConnection !== null) {
            $this->rollBack();
        }
        foreach ($this->pools as $pool) {
            $pool->close();
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new DatabaseException("Cannot unserialize singleton");
    }

    private function generateCacheKey(string $sql, array $params): string
    {
        return md5($sql . serialize($params));
    }

    public function clearQueryCache(): void
    {
        // Implement a method to clear all query caches
        // This is a simplified version; you might want to use a more targeted approach
        $this->cacheManager->clear('query_cache_');
    }

    public function setQueryCacheTTL(int $ttl): void
    {
        $this->queryCacheTTL = $ttl;
    }

    public function getQueryLogger(): QueryLogger
    {
        return $this->queryLogger;
    }

    public function getProfiler(): DatabaseProfiler
    {
        return $this->profiler;
    }

    private function logQuery(string $sql, array $params, float $duration, bool $cacheHit): void
    {
        $logMessage = sprintf(
            "Query: %s | Params: %s | Duration: %.4f sec | Cache: %s",
            $sql,
            json_encode($params),
            $duration,
            $cacheHit ? 'HIT' : 'MISS'
        );

        if ($duration > $this->slowQueryThreshold) {
            $this->logger->warning("Slow query detected: " . $logMessage);
        } else {
            $this->logger->info($logMessage);
        }
    }

    public function setSlowQueryThreshold(float $threshold): void
    {
        $this->slowQueryThreshold = $threshold;
    }
}