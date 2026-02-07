<?php

namespace App\Libraries\Cache;

class CacheEngine
{
    private string $cacheDir;

    public function __construct(string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? WRITEPATH . 'cache/db/';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cache file path
     */
    public function getPath(string $key): string
    {
        return $this->cacheDir . md5($key) . '.cache';
    }

    /**
     * Read from cache
     */
    public function read(string $key): ?array
    {
        $file = $this->getPath($key);
        
        if (!file_exists($file)) {
            return null;
        }

        $data = file_get_contents($file);
        return unserialize($data);
    }

    /**
     * Write to cache
     */
    public function write(string $key, mixed $data, int $ttl): bool
    {
        $file = $this->getPath($key);
        
        $cacheData = [
            'data' => $data,
            'expires_at' => time() + $ttl
        ];

        return file_put_contents($file, serialize($cacheData)) !== false;
    }

    /**
     * Check if cache is valid
     */
    public function isValid(string $key): bool
    {
        $cached = $this->read($key);
        
        if (!$cached || !isset($cached['expires_at'])) {
            return false;
        }

        return time() < $cached['expires_at'];
    }

    /**
     * Delete cache
     */
    public function delete(string $key): bool
    {
        $file = $this->getPath($key);
        
        if (file_exists($file)) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Clear all cache files
     */
    public function clear(): int
    {
        $files = glob($this->cacheDir . '*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            if (@unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }
}