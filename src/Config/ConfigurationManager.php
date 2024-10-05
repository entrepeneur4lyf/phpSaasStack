<?php

declare(strict_types=1);

namespace Src\Config;

use Src\Interfaces\CacheInterface;
use Dotenv\Dotenv;

class ConfigurationManager
{
    private array $config = [];
    private CacheInterface $cache;
    private string $cacheKey = 'app_config';
    private int $cacheTtl = 3600; // 1 hour

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function load(): void
    {
        $cachedConfig = $this->cache->get($this->cacheKey);

        if ($cachedConfig !== null) {
            $this->config = $cachedConfig;
        } else {
            $this->loadEnvironmentVariables();
            $this->loadFromApplicationFile();
            $this->cache->set($this->cacheKey, $this->config, $this->cacheTtl);
        }
    }

    private function loadEnvironmentVariables(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    private function loadFromApplicationFile(): void
    {
        $this->config = require __DIR__ . '/../../src/Config/Application.php';
    }

    public function get(string $key, $default = null)
    {
        return $this->getNestedValue($this->config, $key, $default);
    }

    private function getNestedValue(array $array, string $key, $default = null)
    {
        $keys = explode('.', $key);
        foreach ($keys as $nestedKey) {
            if (!isset($array[$nestedKey])) {
                return $default;
            }
            $array = $array[$nestedKey];
        }
        return $array;
    }

    public function set(string $key, $value): void
    {
        $this->setNestedValue($this->config, $key, $value);
        $this->cache->set($this->cacheKey, $this->config, $this->cacheTtl);
    }

    private function setNestedValue(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        foreach ($keys as $nestedKey) {
            if (!isset($array[$nestedKey]) || !is_array($array[$nestedKey])) {
                $array[$nestedKey] = [];
            }
            $array = &$array[$nestedKey];
        }
        $array[$lastKey] = $value;
    }

    public function clearCache(): void
    {
        $this->cache->delete($this->cacheKey);
    }
}