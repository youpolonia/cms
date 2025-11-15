<?php

class RateLimiter {
    private static array $config;
    private static string $cacheDir = __DIR__ . '/../cache/rate_limits/';

    public static function getCacheDir(): string {
        return self::$cacheDir;
    }

    public static function init(): void {
        self::$config = require __DIR__ . '/../config/rate-limiter.php';
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }

    public static function check(string $type, string $key, ?string $tenantType = null): bool {
        $limitConfig = self::getLimitConfig($type, $tenantType);
        [$maxRequests, $timeWindow] = explode(',', $limitConfig);

        $cacheFile = self::$cacheDir . md5("{$type}_{$key}") . '.json';
        $data = file_exists($cacheFile) 
            ? json_decode(file_get_contents($cacheFile), true) 
            : ['count' => 0, 'timestamp' => time()];

        if (time() - $data['timestamp'] > $timeWindow * 60) {
            $data = ['count' => 0, 'timestamp' => time()];
        }

        if ($data['count'] >= $maxRequests) {
            return false;
        }

        $data['count']++;
        file_put_contents($cacheFile, json_encode($data));
        return true;
    }

    private static function getLimitConfig(string $type, ?string $tenantType): string {
        $parts = explode('.', $type);
        $config = self::$config;

        foreach ($parts as $part) {
            if (!isset($config[$part])) {
                throw new InvalidArgumentException("Invalid rate limit type: {$type}");
            }
            $config = $config[$part];
        }

        if ($tenantType && isset($config[$tenantType])) {
            return $config[$tenantType];
        }

        return $config;
    }
}

RateLimiter::init();
