<?php

namespace App\Libraries\Cache;

class CacheManager
{
    private CacheEngine $engine;
    private string $prefix;
    private int $defaultTTL;

    public function __construct(string $prefix, int $defaultTTL = 300)
    {
        $this->engine = new CacheEngine();
        $this->prefix = $prefix;
        $this->defaultTTL = $defaultTTL;
    }

    /**
     * Get with auto-generate
     */
    public function get(string $key, callable $generator, int $ttl = null): mixed
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $fullKey = $this->makeKey($key);

        // Try cache first
        if ($this->engine->isValid($fullKey)) {
            $cached = $this->engine->read($fullKey);
            return $cached['data'];
        }

        // Generate fresh data
        $data = $generator();
        $this->engine->write($fullKey, $data, $ttl);

        return $data;
    }

    /**
     * Set cache manually
     */
    public function set(string $key, mixed $data, int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->defaultTTL;
        $fullKey = $this->makeKey($key);

        return $this->engine->write($fullKey, $data, $ttl);
    }

    /**
     * Forget specific key
     */
    public function forget(string $key): bool
    {
        $fullKey = $this->makeKey($key);
        return $this->engine->delete($fullKey);
    }

    /**
     * Clear all cache for this prefix
     */
    public function flush(): int
    {
        // For now, clear all (we can improve this with prefix filtering)
        return $this->engine->clear();
    }

    /**
     * Make full cache key with prefix
     */
    private function makeKey(string $key): string
    {
        return "{$this->prefix}:{$key}";
    }
}