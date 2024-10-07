<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\CacheServiceInterface;

class FileCacheService implements CacheServiceInterface
{
    private string $cacheDir;
    private int $defaultTtl;

    public function __construct(array $config)
    {
        $this->cacheDir = $config['cache_dir'] ?? sys_get_temp_dir() . '/app_cache';
        $this->defaultTtl = $config['default_ttl'] ?? 3600;

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get($key, $default = null)
    {
        $filename = $this->getFilename($key);
        if (!file_exists($filename)) {
            return $default;
        }

        $data = unserialize(file_get_contents($filename));
        if ($data['expiry'] !== 0 && $data['expiry'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = null): bool
    {
        $filename = $this->getFilename($key);
        $expiry = $ttl !== null ? time() + $ttl : ($this->defaultTtl > 0 ? time() + $this->defaultTtl : 0);
        $data = serialize([
            'value' => $value,
            'expiry' => $expiry,
        ]);

        return file_put_contents($filename, $data) !== false;
    }

    public function delete($key): bool
    {
        $filename = $this->getFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $success = true;
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    public function deleteMultiple($keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }

    public function has($key): bool
    {
        return $this->get($key, $this) !== $this;
    }

    public function getKeys(string $pattern): array
    {
        $files = glob($this->cacheDir . '/' . str_replace(['*', '?'], ['*', '?'], $pattern));
        return array_map(function ($file) {
            return basename($file);
        }, $files);
    }

    private function getFilename(string $key): string
    {
        return $this->cacheDir . '/' . md5($key);
    }
}