<?php
/**
 * API Cache Implementation
 * Simple file-based caching for API responses
 */
class ApiCache {
    private static $cacheDir = __DIR__ . '/../../cache/api/';
    private static $ttl = 3600; // 1 hour default

    /**
     * Store API response in cache
     */
    public static function store(string $key, $data): bool {
        self::ensureCacheDir();
        $file = self::getCacheFile($key);
        $content = [
            'timestamp' => time(),
            'data' => $data
        ];
        return file_put_contents($file, json_encode($content)) !== false;
    }

    /**
     * Retrieve cached API response
     */
    public static function get(string $key) {
        $file = self::getCacheFile($key);
        if (!file_exists($file)) {
            return null;
        }

        $content = json_decode(file_get_contents($file), true);
        if (!$content || !isset($content['timestamp']) || !isset($content['data'])) {
            return null;
        }

        if (time() - $content['timestamp'] > self::$ttl) {
            unlink($file);
            return null;
        }

        return $content['data'];
    }

    /**
     * Clear specific cache entry
     */
    public static function clear(string $key): bool {
        $file = self::getCacheFile($key);
        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * Clear all cached API responses
     */
    public static function clearAll(): bool {
        self::ensureCacheDir();
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }

    private static function getCacheFile(string $key): string {
        return self::$cacheDir . md5($key) . '.cache';
    }

    private static function ensureCacheDir(): void {
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
}
