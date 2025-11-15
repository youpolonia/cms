<?php

declare(strict_types=1);

namespace CMS\Cache;

interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Persists data in the cache.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * Determines whether an item is present in the cache.
     */
    public function has(string $key): bool;

    /**
     * Deletes an item from the cache.
     */
    public function delete(string $key): bool;

    /**
     * Wipes clean the entire cache.
     */
    public function clear(): bool;
}
