<?php

declare(strict_types=1);

namespace Src\Factories;

use Src\Interfaces\CacheServiceInterface;
use Src\Services\RedisCacheService;
use Src\Services\FileCacheService;
use Src\Cache\SwooleRedisCache;

class CacheFactory
{
    public static function create(string $driver, array $config): CacheServiceInterface
    {
        switch ($driver) {
            case 'redis':
                return self::createRedisCache($config);
            case 'file':
                return new FileCacheService($config);
            default:
                throw new \InvalidArgumentException("Unsupported cache driver: $driver");
        }
    }

    private static function createRedisCache(array $config): RedisCacheService
    {
        $redisConfig = [
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => (int)($config['port'] ?? 6379),
            'password' => $config['password'] ?? null,
            'database' => (int)($config['database'] ?? 0),
            'prefix' => $config['prefix'] ?? 'cache:',
            'pool' => [
                'min_connections' => (int)($config['pool']['min_connections'] ?? 1),
                'max_connections' => (int)($config['pool']['max_connections'] ?? 10),
                'connect_timeout' => (float)($config['pool']['connect_timeout'] ?? 10.0),
                'wait_timeout' => (float)($config['pool']['wait_timeout'] ?? 3.0),
                'heartbeat' => (int)($config['pool']['heartbeat'] ?? -1),
            ],
        ];

        $swooleRedisCache = new SwooleRedisCache($redisConfig);
        return new RedisCacheService($swooleRedisCache, (int)($config['ttl'] ?? 3600));
    }
}
