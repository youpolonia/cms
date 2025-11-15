<?php

declare(strict_types=1);

namespace App\Includes\Database;

class CacheHandler
{
    private static array $cache = [];
    private static bool $enabled = true;
    private static int $defaultTtl = 3600; // 1 hour

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    public static function setTtl(int $seconds): void
    {
        self::$defaultTtl = $seconds;
    }

    public static function get(string $key): ?array
    {
        if (!self::$enabled || !isset(self::$cache[$key])) {
            return null;
        }

        $entry = self::$cache[$key];
        if ($entry['expires'] < time()) {
            unset(self::$cache[$key]);
            return null;
        }

        return $entry['data'];
    }

    public static function set(string $key, array $data, ?int $ttl = null): void
    {
        if (!self::$enabled) {
            return;
        }

        self::$cache[$key] = [
            'data' => $data,
            'expires' => time() + ($ttl ?? self::$defaultTtl)
        ];
    }

    public static function clear(?string $key = null): void
    {
        if ($key === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$key]);
        }
    }

    public static function generateKey(string $query, array $bindings = []): string
    {
        return md5($query . serialize($bindings));
    }
}
