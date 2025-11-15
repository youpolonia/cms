<?php
declare(strict_types=1);

/**
 * Marker Activity Logger - Tracks all marker-related activities
 */
class MarkerActivityLogger
{
    private const LOG_LEVEL_INFO = 'info';
    private const LOG_LEVEL_WARNING = 'warning';
    private const LOG_LEVEL_ERROR = 'error';

    /**
     * Log a marker activity
     */
    public static function log(
        int $userId,
        string $action,
        int $markerId,
        array $details = [],
        string $level = self::LOG_LEVEL_INFO
    ): bool {
        $logEntry = [
            'timestamp' => time(),
            'user_id' => $userId,
            'marker_id' => $markerId,
            'action' => $action,
            'level' => $level,
            'details' => $details
        ];

        return self::writeToLog($logEntry);
    }

    /**
     * Write log entry to storage
     */
    private static function writeToLog(array $entry): bool
    {
        $logFile = __DIR__ . '/../../logs/marker_activity.log';
        $entryJson = json_encode($entry, JSON_PRETTY_PRINT) . PHP_EOL;

        try {
            file_put_contents($logFile, $entryJson, FILE_APPEND);
            return true;
        } catch (Exception $e) {
            error_log("Failed to write marker activity log: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get recent activity logs
     */
    public static function getRecentActivity(int $limit = 50): array
    {
        $logFile = __DIR__ . '/../../logs/marker_activity.log';
        
        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $logs = array_map(fn($line) => json_decode($line, true), $lines);
        
        return array_slice(array_reverse($logs), 0, $limit);
    }
}
