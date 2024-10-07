<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\CacheServiceInterface;
use Src\Cache\SwooleRedisCache;

class RedisCacheService implements CacheServiceInterface
{
    private SwooleRedisCache $cache;
    private int $defaultTtl;

    public function __construct(SwooleRedisCache $cache, int $defaultTtl = 3600)
    {
        $this->cache = $cache;
        $this->defaultTtl = $defaultTtl;
    }

    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    public function set($key, $value, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return $this->cache->set($key, $value, $ttl);
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function clear()
    {
        return $this->cache->clear();
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->cache->getMultiple($keys, $default);
    }

    public function setMultiple($values, $ttl = null)
    {
        $ttl = $ttl ?? $this->defaultTtl;
        return $this->cache->setMultiple($values, $ttl);
    }

    public function deleteMultiple($keys)
    {
        return $this->cache->deleteMultiple($keys);
    }

    public function has($key)
    {
        return $this->cache->has($key);
    }
}