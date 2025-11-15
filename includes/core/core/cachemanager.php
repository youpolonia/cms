<?php
declare(strict_types=1);

class CacheManager {
    public static function set(string $key, array $data, int $ttl): bool {
        $cacheFile = self::getCachePath($key);
        $cacheData = [
            'expires' => time() + $ttl,
            'data' => $data
        ];
        return file_put_contents($cacheFile, serialize($cacheData)) !== false;
    }

    public static function get(string $key): ?array {
        $cacheFile = self::getCachePath($key);
        
        if (!file_exists($cacheFile)) {
            return null;
        }

        $cacheData = unserialize(file_get_contents($cacheFile));
        if ($cacheData === false || $cacheData['expires'] < time()) {
            return null;
        }

        return $cacheData['data'];
    }

    private static function getCachePath(string $key): string {
        $cacheDir = __DIR__ . '/../../storage/cache/search';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
        return $cacheDir . '/' . $key . '.cache';
    }
}
