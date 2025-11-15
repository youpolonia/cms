<?php
declare(strict_types=1);

class CacheManager {
    private static array $caches = [
        'global' => [],
        'tenant' => [],
        'content' => []
    ];

    public static function get(string $level, string $key): mixed {
        self::validateLevel($level);
        return self::$caches[$level][$key] ?? null;
    }

    public static function set(string $level, string $key, mixed $value, int $ttl = 3600): void {
        self::validateLevel($level);
        self::$caches[$level][$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }

    public static function purgeExpired(): void {
        $now = time();
        foreach (self::$caches as &$cache) {
            $cache = array_filter($cache, fn($item) => $item['expires'] > $now);
        }
    }

    private static function validateLevel(string $level): void {
        if (!array_key_exists($level, self::$caches)) {
            throw new InvalidArgumentException("Invalid cache level: $level");
        }
    }
}
