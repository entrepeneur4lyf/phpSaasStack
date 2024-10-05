<?php

declare(strict_types=1);

namespace Src\Services;

use Src\Interfaces\CacheServiceInterface;

class FileCacheService implements CacheServiceInterface
{
    private string $cacheDir;

    public function __construct(string $cacheDir = '../cache')
    {
        $this->cacheDir = $cacheDir;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    public function get(string $key): ?string
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename) && is_readable($filename)) {
            $content = file_get_contents($filename);
            $data = unserialize($content);
            if ($data['expiry'] > time()) {
                return $data['value'];
            }
            $this->delete($key);
        }
        return null;
    }

    public function set(string $key, string $value, int $ttl = 3600): bool
    {
        $filename = $this->getCacheFilename($key);
        $data = [
            'value' => $value,
            'expiry' => time() + $ttl,
        ];
        return file_put_contents($filename, serialize($data)) !== false;
    }

    public function delete(string $key): bool
    {
        $filename = $this->getCacheFilename($key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    private function getCacheFilename(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}