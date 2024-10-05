<?php

declare(strict_types=1);

namespace Src\Core;

use Hyperf\Cache\CacheManager as HyperfCacheManager;
use Psr\SimpleCache\CacheInterface;

class CacheManager
{
    private CacheInterface $cache;

    public function __construct(HyperfCacheManager $cacheManager)
    {
        $this->cache = $cacheManager->getDriver();
    }

    public function get(string $key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function set(string $key, $value, ?int $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function has(string $key): bool
    {
        return $this->cache->has($key);
    }
}