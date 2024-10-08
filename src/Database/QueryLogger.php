<?php

declare(strict_types=1);

namespace Src\Database;

use Psr\Log\LoggerInterface;

class QueryLogger
{
    private array $queries = [];
    private float $totalQueryTime = 0;

    public function __construct(private LoggerInterface $logger, private float $slowQueryThreshold = 1.0)
    {
    }

    public function log(string $sql, array $params, float $duration, bool $cacheHit): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'cacheHit' => $cacheHit,
        ];

        $this->totalQueryTime += $duration;

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

    public function getAverageQueryTime(): float
    {
        $count = count($this->queries);
        return $count > 0 ? $this->totalQueryTime / $count : 0;
    }

    public function getSlowQueries(): array
    {
        return array_filter($this->queries, fn ($query) => $query['duration'] > $this->slowQueryThreshold);
    }

    public function getCacheHitRate(): float
    {
        $count = count($this->queries);
        if ($count === 0) {
            return 0;
        }
        $cacheHits = count(array_filter($this->queries, fn ($query) => $query['cacheHit']));
        return $cacheHits / $count;
    }
}
