<?php

declare(strict_types=1);

namespace Src\Cache;

use Src\Cache\SwooleRedisCache;
use Psr\SimpleCache\CacheInterface;

class CacheManager implements CacheInterface
{
    private SwooleRedisCache $cache;

    public function __construct(SwooleRedisCache $cache)
    {
        $this->cache = $cache;
    }

    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete($key): bool
    {
        return $this->cache->delete($key);
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->cache->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys): bool
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has($key): bool
    {
        return $this->cache->has($key);
    }

    public function getKeys(string $pattern): array
    {
        return $this->cache->getKeys($pattern);
    }
}