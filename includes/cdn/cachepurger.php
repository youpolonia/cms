<?php
declare(strict_types=1);

class CachePurger {
    private const MAX_PURGE_RATE = 10; // Purges per minute
    private static array $purgeLog = [];

    public static function purgeAsset(string $assetUrl): bool {
        if (!self::validatePurgeRequest($assetUrl)) {
            return false;
        }

        $result = EdgeCacheService::purge($assetUrl);
        
        self::logPurge($assetUrl, $result);
        return $result;
    }

    public static function purgeAll(): bool {
        if (self::rateLimitExceeded()) {
            return false;
        }

        $result = EdgeCacheService::purgeAll();
        self::logPurge('*', $result);
        return $result;
    }

    private static function validatePurgeRequest(string $assetUrl): bool {
        if (self::rateLimitExceeded()) {
            return false;
        }

        $allowedPatterns = [
            '/^https?:\/\/[a-z0-9-\.]+\/assets\/.+/i',
            '/^https?:\/\/[a-z0-9-\.]+\/uploads\/.+/i'
        ];

        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $assetUrl)) {
                return true;
            }
        }

        return false;
    }

    private static function rateLimitExceeded(): bool {
        $minute = (int)(time() / 60);
        $count = self::$purgeLog[$minute] ?? 0;
        return $count >= self::MAX_PURGE_RATE;
    }

    private static function logPurge(string $assetUrl, bool $success): void {
        $minute = (int)(time() / 60);
        self::$purgeLog[$minute] = (self::$purgeLog[$minute] ?? 0) + 1;

        AuditLogger::log('cdn_purge', [
            'asset' => $assetUrl,
            'success' => $success,
            'timestamp' => time()
        ]);
    }
}
