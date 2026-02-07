<?php

namespace App\Traits;

use App\Libraries\Cache\CacheManager;

trait Cacheable
{
    protected ?CacheManager $cache = null;
    protected string $cachePrefix = 'default';
    protected int $cacheTTL = 300;

    /**
     * Initialize cache
     */
    protected function initCache(): void
    {
        if ($this->cache === null) {
            $this->cache = new CacheManager($this->cachePrefix, $this->cacheTTL);
        }
    }

    /**
     * Get cache instance
     */
    protected function cache(): CacheManager
    {
        $this->initCache();
        return $this->cache;
    }

    /**
     * Remember: get from cache or execute
     */
    protected function remember(string $key, callable $callback, int $ttl = null): mixed
    {
        return $this->cache()->get($key, $callback, $ttl);
    }

    /**
     * Forget cached key
     */
    protected function forget(string $key): bool
    {
        return $this->cache()->forget($key);
    }

    /**
     * Flush all cache
     */
    protected function flushCache(): int
    {
        return $this->cache()->flush();
    }
}