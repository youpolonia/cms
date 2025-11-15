<?php
/**
 * GDPR Action Logger
 * 
 * Logs all GDPR-related actions for compliance tracking
 */
class GDPRLog {
    // Action types
    const ACTION_DATA_ACCESS = 'data_access';
    const ACTION_DATA_DELETION = 'data_deletion';
    const ACTION_CONSENT_CHANGE = 'consent_change';
    const ACTION_EXPORT_REQUEST = 'export_request';
    const ACTION_COMPLAINT = 'complaint';

    // Log directory (relative to CMS root)
    private static $logDir = '../storage/logs/gdpr/';

    /**
     * Log a GDPR action
     * 
     * @param string $actionType One of the ACTION_* constants
     * @param string $userId Affected user ID
     * @param string $description Action description
     * @param array $metadata Additional context data
     * @return bool True if logged successfully
     */
    public static function logAction(
        string $actionType,
        string $userId,
        string $description = '',
        array $metadata = []
    ): bool {
        // Ensure log directory exists
        if (!self::ensureLogDirectory()) {
            return false;
        }

        $logEntry = [
            'timestamp' => date('c'),
            'action' => $actionType,
            'user_id' => $userId,
            'description' => $description,
            'metadata' => $metadata,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $logFile = self::getLogFilePath();
        $logLine = json_encode($logEntry) . PHP_EOL;

        return file_put_contents($logFile, $logLine, FILE_APPEND) !== false;
    }

    /**
     * Get all GDPR logs for a user
     * 
     * @param string $userId User ID to filter by
     * @param int $limit Maximum number of entries to return
     * @return array Array of log entries
     */
    public static function getUserLogs(string $userId, int $limit = 100): array {
        $logFile = self::getLogFilePath();
        if (!file_exists($logFile)) {
            return [];
        }

        $logs = [];
        $file = fopen($logFile, 'r');
        if ($file) {
            while (($line = fgets($file)) !== false) {
                $entry = json_decode($line, true);
                if ($entry && $entry['user_id'] === $userId) {
                    $logs[] = $entry;
                    if (count($logs) >= $limit) {
                        break;
                    }
                }
            }
            fclose($file);
        }

        return $logs;
    }

    /**
     * Ensure log directory exists and is writable
     * 
     * @return bool True if directory is ready
     */
    private static function ensureLogDirectory(): bool {
        $fullPath = __DIR__ . '/' . self::$logDir;
        if (!file_exists($fullPath)) {
            return mkdir($fullPath, 0700, true);
        }
        return is_writable($fullPath);
    }

    /**
     * Get path to current log file (daily rotation)
     * 
     * @return string Full path to log file
     */
    private static function getLogFilePath(): string {
        $date = date('Y-m-d');
        return __DIR__ . '/' . self::$logDir . "gdpr_{$date}.log";
    }
}
