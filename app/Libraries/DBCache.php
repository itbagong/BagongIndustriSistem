<?php

namespace App\Libraries;

class DBCache
{
    private static array $instances = [];
    private string $collectionName;
    private string $cacheDir;
    private int $defaultTTL = 300; // 5 minutes
    private array $eventLog = [];
    private bool $isPreparing = false;

    private function __construct(string $collectionName)
    {
        $this->collectionName = $collectionName;
        $this->cacheDir = WRITEPATH . 'cache/db/';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cache instance for collection
     */
    public static function newCache(string $collectionName): self
    {
        if (!isset(self::$instances[$collectionName])) {
            self::$instances[$collectionName] = new self($collectionName);
        }
        
        return self::$instances[$collectionName];
    }

    /**
     * Safe get - returns cached data or generates new
     */
    public function safeGet(string $key, callable $dataGenerator, int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $cacheFile = $this->getCacheFilePath($key);
        $metaFile = $this->getMetaFilePath($key);

        // Check if cache exists and valid
        if (file_exists($cacheFile) && file_exists($metaFile)) {
            $meta = json_decode(file_get_contents($metaFile), true);
            
            if ($meta && time() < $meta['expires_at']) {
                $this->logEvent('HIT', $key);
                return unserialize(file_get_contents($cacheFile));
            }
        }

        // Cache miss - generate new data
        $this->logEvent('MISS', $key);
        $this->setPreparing(true);
        
        try {
            $data = $dataGenerator();
            $this->set($key, $data, $ttl);
            return $data;
        } finally {
            $this->setPreparing(false);
        }
    }

    /**
     * Set cache data
     */
    public function set(string $key, mixed $data, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $cacheFile = $this->getCacheFilePath($key);
        $metaFile = $this->getMetaFilePath($key);

        $serialized = serialize($data);
        
        $meta = [
            'collection' => $this->collectionName,
            'key' => $key,
            'created_at' => time(),
            'expires_at' => time() + $ttl,
            'ttl' => $ttl,
            'size' => strlen($serialized)
        ];

        file_put_contents($cacheFile, $serialized);
        file_put_contents($metaFile, json_encode($meta));

        $this->logEvent('SET', $key);
        
        return true;
    }

    /**
     * Invalidate specific key
     */
    public function invalidate(string $key): bool
    {
        $cacheFile = $this->getCacheFilePath($key);
        $metaFile = $this->getMetaFilePath($key);

        $deleted = false;

        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
            $deleted = true;
        }

        if (file_exists($metaFile)) {
            @unlink($metaFile);
        }

        if ($deleted) {
            $this->logEvent('INVALIDATE', $key);
        }

        return $deleted;
    }

    /**
     * Clear all cache for this collection
     */
    public function clear(): int
    {
        $pattern = $this->cacheDir . $this->collectionName . '_*';
        $files = glob($pattern);
        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
                $deleted++;
            }
        }

        $this->logEvent('CLEAR', "Deleted {$deleted} files");

        return $deleted;
    }

    /**
     * Check if cache is preparing
     */
    public function isPreparing(): bool
    {
        return $this->isPreparing;
    }

    /**
     * Set preparing status
     */
    public function setPreparing(bool $status): void
    {
        $this->isPreparing = $status;
    }

    /**
     * Get event log
     */
    public function getEventLog(): array
    {
        return $this->eventLog;
    }

    /**
     * Set/append event log
     */
    public function setEventLog(string $event, string $message): void
    {
        $this->logEvent($event, $message);
    }

    /**
     * Get cache stats for this collection
     */
    public function getStats(): array
    {
        $pattern = $this->cacheDir . $this->collectionName . '_*.meta';
        $metaFiles = glob($pattern);
        
        $totalSize = 0;
        $validCount = 0;
        $expiredCount = 0;
        $items = [];

        foreach ($metaFiles as $metaFile) {
            $meta = json_decode(file_get_contents($metaFile), true);
            
            if ($meta) {
                $totalSize += $meta['size'];
                
                if (time() < $meta['expires_at']) {
                    $validCount++;
                } else {
                    $expiredCount++;
                }

                $items[] = [
                    'key' => $meta['key'],
                    'size' => $meta['size'],
                    'created_at' => $meta['created_at'],
                    'expires_at' => $meta['expires_at'],
                    'is_valid' => time() < $meta['expires_at']
                ];
            }
        }

        return [
            'collection' => $this->collectionName,
            'total_items' => count($metaFiles),
            'valid_items' => $validCount,
            'expired_items' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'items' => $items
        ];
    }

    /**
     * Helper methods
     */
    private function getCacheFilePath(string $key): string
    {
        return $this->cacheDir . $this->collectionName . '_' . md5($key) . '.cache';
    }

    private function getMetaFilePath(string $key): string
    {
        return $this->cacheDir . $this->collectionName . '_' . md5($key) . '.meta';
    }

    private function logEvent(string $event, string $message): void
    {
        $this->eventLog[] = [
            'timestamp' => time(),
            'event' => $event,
            'message' => $message,
            'collection' => $this->collectionName
        ];

        // Keep only last 100 events
        if (count($this->eventLog) > 100) {
            array_shift($this->eventLog);
        }
    }
}