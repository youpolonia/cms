<?php
namespace Core\Cache;

interface CacheInterface {
    /**
     * Store an item in the cache
     * @param string $tenantId Tenant identifier for cache isolation
     */
    public function set(string $tenantId, string $key, $value, ?int $ttl = null): bool;

    /**
     * Retrieve an item from the cache
     * @param string $tenantId Tenant identifier for cache isolation
     */
    public function get(string $tenantId, string $key);

    /**
     * Remove an item from the cache
     * @param string $tenantId Tenant identifier for cache isolation
     */
    public function delete(string $tenantId, string $key): bool;

    /**
     * Clear the entire cache for a tenant
     * @param string $tenantId Tenant identifier for cache isolation
     */
    public function clear(string $tenantId): bool;

    /**
     * Check if an item exists in cache
     * @param string $tenantId Tenant identifier for cache isolation
     */
    public function has(string $tenantId, string $key): bool;
}
