<?php

declare(strict_types=1);

namespace Src\Database;

use Psr\Log\LoggerInterface;

class DatabaseProfiler
{
    private array $queries = [];
    private float $totalQueryTime = 0;
    private int $queryCount = 0;
    private int $cacheHits = 0;

    public function __construct(
        private LoggerInterface $logger,
        private float $slowQueryThreshold = 1.0
    ) {
    }

    public function startQuery(string $sql, array $params): float
    {
        $this->queryCount++;
        return microtime(true);
    }

    public function endQuery(float $startTime, string $sql, array $params, bool $cacheHit): void
    {
        $duration = microtime(true) - $startTime;
        $this->totalQueryTime += $duration;

        if ($cacheHit) {
            $this->cacheHits++;
        }

        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'cacheHit' => $cacheHit,
        ];

        $this->logQuery($sql, $params, $duration, $cacheHit);
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

    public function getQueries(): array
    {
        return $this->queries;
    }

    public function getTotalQueryTime(): float
    {
        return $this->totalQueryTime;
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getAverageQueryTime(): float
    {
        return $this->queryCount > 0 ? $this->totalQueryTime / $this->queryCount : 0;
    }

    public function getSlowQueries(): array
    {
        return array_filter($this->queries, fn ($query) => $query['duration'] > $this->slowQueryThreshold);
    }

    public function getCacheHitRate(): float
    {
        return $this->queryCount > 0 ? $this->cacheHits / $this->queryCount : 0;
    }

    public function getProfileReport(): array
    {
        return [
            'totalQueries' => $this->queryCount,
            'totalQueryTime' => $this->totalQueryTime,
            'averageQueryTime' => $this->getAverageQueryTime(),
            'slowQueries' => count($this->getSlowQueries()),
            'cacheHitRate' => $this->getCacheHitRate(),
        ];
    }
}
