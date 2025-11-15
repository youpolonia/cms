<?php
namespace Database;

class QueryLogger {
    private const LOG_FILE = __DIR__ . '/../../storage/logs/query.log';
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB

    public static function log(string $query, float $duration): void {
        if (!defined('DEV_MODE') || !DEV_MODE) {
            return;
        }

        self::rotateIfNeeded();
        
        $logEntry = sprintf(
            "[%s] %.2fms - %s\n",
            date('Y-m-d H:i:s'),
            $duration * 1000,
            self::sanitizeQuery($query)
        );

        file_put_contents(self::LOG_FILE, $logEntry, FILE_APPEND);
    }

    private static function rotateIfNeeded(): void {
        if (!file_exists(self::LOG_FILE)) {
            return;
        }

        if (filesize(self::LOG_FILE) >= self::MAX_SIZE) {
            $backup = self::LOG_FILE . '.' . date('Ymd-His');
            rename(self::LOG_FILE, $backup);
        }
    }

    private static function sanitizeQuery(string $query): string {
        // Remove extra whitespace and newlines for cleaner logs
        return preg_replace('/\s+/', ' ', trim($query));
    }
}
