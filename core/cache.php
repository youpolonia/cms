<?php
/**
 * Lightweight file-based cache system
 */
class Cache {
    const CACHE_DIR = __DIR__ . '/../cache/';
    const EXTENSION = '.cache';

    /**
     * Get cached data if valid
     * @param string $key Cache key
     * @return mixed|null Cached data or null if expired/missing
     */
    public static function get(string $key) {
        $file = self::getCachePath($key);
        
        if (!file_exists($file)) {
            return null;
        }

        $data = file_get_contents($file);
        $cache = json_decode($data, true);

        // Check if cache is expired
        if ($cache['expires'] < time()) {
            self::clear($key);
            return null;
        }

        return $cache['data'];
    }

    /**
     * Store data in cache with TTL
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     * @return bool True on success
     */
    public static function set(string $key, $data, int $ttl = 3600): bool {
        $file = self::getCachePath($key);
        $cache = [
            'data' => $data,
            'expires' => time() + $ttl,
            'created' => time()
        ];

        $json = json_encode($cache);
        return file_put_contents($file, $json) !== false;
    }

    /**
     * Clear a cache entry
     * @param string $key Cache key
     * @return bool True if file was deleted or didn't exist
     */
    public static function clear(string $key): bool {
        $file = self::getCachePath($key);
        return !file_exists($file) || unlink($file);
    }

    /**
     * Get full cache file path
     * @param string $key Cache key
     * @return string Full file path
     */
    private static function getCachePath(string $key): string {
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
        return self::CACHE_DIR . $safeKey . self::EXTENSION;
    }
}
