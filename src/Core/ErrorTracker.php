<?php

namespace Src\Core;

use Redis;

class ErrorTracker
{
    private Redis $redis;
    private int $ttl;

    public function __construct(Redis $redis, int $ttl = 86400)
    {
        $this->redis = $redis;
        $this->ttl = $ttl;
    }

    public function trackError(\Throwable $error): void
    {
        $errorHash = $this->getErrorHash($error);
        $errorKey = "error:{$errorHash}";

        $this->redis->incr($errorKey);
        $this->redis->expire($errorKey, $this->ttl);

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

        $this->redis->hMSet("error_details:{$errorHash}", $errorDetails);
        $this->redis->expire("error_details:{$errorHash}", $this->ttl);
    }

    public function getTopErrors(int $limit = 10): array
    {
        $errors = [];
        $keys = $this->redis->keys('error:*');

        foreach ($keys as $key) {
            $count = $this->redis->get($key);
            $errorHash = substr($key, 6);
            $details = $this->redis->hGetAll("error_details:{$errorHash}");
            $errors[] = ['count' => $count, 'details' => $details];
        }

        usort($errors, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_slice($errors, 0, $limit);
    }
}