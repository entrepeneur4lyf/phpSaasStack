<?php

declare(strict_types=1);

namespace Src\Core;

use Src\Cache\SwooleRedisCache;
use Src\Interfaces\ErrorTrackerInterface;
use Src\Cache\CacheManager;

class ErrorTracker implements ErrorTrackerInterface
{
    private SwooleRedisCache $cache;
    private int $ttl;

    public function __construct(SwooleRedisCache $cache, int $ttl = 86400)
    {
        $this->cache = $cache;
        $this->ttl = $ttl;
    }

    public function trackError(\Throwable $error): void
    {
        $errorHash = $this->getErrorHash($error);
        $errorKey = "error:{$errorHash}";

        $count = $this->cache->get($errorKey, 0);
        $this->cache->set($errorKey, $count + 1, $this->ttl);

        $this->storeErrorDetails($errorHash, $error);
    }

    private function getErrorHash(\Throwable $error): string
    {
        return md5($error->getMessage() . $error->getFile() . $error->getLine());
    }

    private function storeErrorDetails(string $errorHash, \Throwable $error): void
    {
        $errorDetails = [
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
            'last_occurrence' => time(),
        ];

        $this->cache->set("error_details:{$errorHash}", $errorDetails, $this->ttl);
    }

    public function getTopErrors(int $limit = 10): array
    {
        $errors = [];
        $keys = $this->cache->getKeys('error:*');

        foreach ($keys as $key) {
            $count = $this->cache->get($key);
            $errorHash = substr($key, 6);
            $details = $this->cache->get("error_details:{$errorHash}");
            $errors[] = ['count' => $count, 'details' => $details];
        }

        usort($errors, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($errors, 0, $limit);
    }
}
