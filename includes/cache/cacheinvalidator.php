<?php
/**
 * Cache Invalidator Service
 * Handles safe cache invalidation across the system
 */
class CacheInvalidator {
    private static array $log = [];
    private static string $logFile = __DIR__ . '/../../memory-bank/cache_invalidation.log';

    /**
     * Clear cache for specific content item
     */
    public static function clearContentCache(int $contentId): bool {
        $cacheKey = 'content_' . $contentId;
        return self::clearByPattern($cacheKey);
    }

    /**
     * Clear cache by key pattern
     */
    public static function clearByPattern(string $pattern): bool {
        try {
            $fileCache = new FileCache();
            $cleared = $fileCache->clear($pattern);
            
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }

            self::log("Cleared cache for pattern: $pattern");
            return $cleared;
        } catch (Exception $e) {
            self::log("Failed to clear cache for $pattern: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Clear entire cache system
     */
    public static function clearAll(): bool {
        try {
            $fileCache = new FileCache();
            $cleared = $fileCache->clearAll();
            
            if (function_exists('apcu_clear_cache')) {
                apcu_clear_cache();
            }

            self::log("Cleared all cache");
            return $cleared;
        } catch (Exception $e) {
            self::log("Failed to clear all cache: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Log cache invalidation events
     */
    private static function log(string $message, string $level = 'info'): void {
        $entry = date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL;
        self::$log[] = $entry;
        
        // Write to log file
        file_put_contents(self::$logFile, $entry, FILE_APPEND);
    }

    /**
     * Get recent invalidation logs
     */
    public static function getLogs(int $limit = 50): array {
        if (file_exists(self::$logFile)) {
            $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            return array_slice($lines, -$limit, $limit, true);
        }
        return [];
    }
}
