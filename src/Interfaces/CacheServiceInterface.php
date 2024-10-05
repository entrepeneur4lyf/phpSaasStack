<?php

declare(strict_types=1);

namespace Src\Interfaces;

interface CacheServiceInterface
{
    public function get(string $key): ?string;
    public function set(string $key, string $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
}