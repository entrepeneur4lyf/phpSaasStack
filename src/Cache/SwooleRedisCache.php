<?php

declare(strict_types=1);

namespace Src\Cache;

use Hyperf\Cache\Driver\RedisDriver;
use Hyperf\Redis\RedisFactory;
use Psr\Container\ContainerInterface;

class SwooleRedisCache extends RedisDriver
{
    public function __construct(ContainerInterface $container, array $config)
    {
        parent::__construct($container, $config);

        $redis = $container->get(RedisFactory::class)->get($config['pool'] ?? 'default');
        $this->setRedis($redis);
    }

    public function getKeys(string $pattern): array
    {
        return $this->getRedis()->keys($this->getCacheKey($pattern));
    }
}