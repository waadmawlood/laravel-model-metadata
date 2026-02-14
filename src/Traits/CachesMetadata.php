<?php

namespace Waad\Metadata\Traits;

use Closure;
use Illuminate\Support\Facades\Cache;

trait CachesMetadata
{
    /**
     * Check if metadata caching is enabled
     */
    public function metadataCacheIsEnabled(): bool
    {
        return (bool) config('model-metadata.cache.enabled', false);
    }

    /**
     * Get the cache store instance for metadata
     */
    protected function getMetadataCacheStore()
    {
        $store = config('model-metadata.cache.store');

        return Cache::store($store);
    }

    /**
     * Get the cache TTL in seconds
     */
    protected function getMetadataCacheTtl(): int
    {
        return (int) config('model-metadata.cache.ttl', 3600);
    }

    /**
     * Get the cache key prefix
     */
    protected function getMetadataCachePrefix(): string
    {
        return config('model-metadata.cache.prefix', 'model_metadata');
    }

    /**
     * Build a cache key for this model's metadata
     */
    protected function getMetadataCacheKey(string $suffix = ''): string
    {
        $prefix = $this->getMetadataCachePrefix();
        $type = get_class($this);
        $id = $this->getKey();

        $key = "{$prefix}:{$type}:{$id}";

        if ($suffix !== '') {
            $key .= ":{$suffix}";
        }

        return $key;
    }

    /**
     * Remember metadata in cache if caching is enabled.
     * If caching is disabled, simply executes the callback.
     */
    protected function rememberMetadata(string $suffix, Closure $callback): mixed
    {
        if (! $this->metadataCacheIsEnabled()) {
            return $callback();
        }

        $key = $this->getMetadataCacheKey($suffix);
        $ttl = $this->getMetadataCacheTtl();

        $result = $this->getMetadataCacheStore()->remember($key, $ttl, $callback);

        $this->trackMetadataCacheKey($key);

        return $result;
    }

    /**
     * Track a cache key in the tracker for later invalidation
     */
    protected function trackMetadataCacheKey(string $key): void
    {
        $trackerKey = $this->getMetadataCacheKey('_tracker');
        $store = $this->getMetadataCacheStore();
        $ttl = $this->getMetadataCacheTtl();

        $trackedKeys = $store->get($trackerKey, []);

        if (! in_array($key, $trackedKeys)) {
            $trackedKeys[] = $key;
            $store->put($trackerKey, $trackedKeys, $ttl);
        }
    }

    /**
     * Clear all cached metadata for this model instance
     */
    public function clearMetadataCache(): void
    {
        if (! $this->metadataCacheIsEnabled()) {
            return;
        }

        $store = $this->getMetadataCacheStore();
        $trackerKey = $this->getMetadataCacheKey('_tracker');

        $trackedKeys = $store->get($trackerKey, []);

        foreach ($trackedKeys as $key) {
            $store->forget($key);
        }

        $store->forget($trackerKey);
    }
}
