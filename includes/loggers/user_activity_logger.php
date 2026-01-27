<?php
/**
 * User Activity Logger
 *
 * Logs user actions to JSONL audit trail
 */

class UserActivityLogger
{
    /**
     * Log a user activity event
     *
     * @param string $action Action identifier (e.g., 'user.create', 'profile.update')
     * @param array $details Additional context data
     * @return void
     */
    public static function log(string $action, array $details = []): void
    {
        // Ensure CMS_ROOT is defined
        if (!defined('CMS_ROOT')) {
            define('CMS_ROOT', dirname(__DIR__, 2));
        }

        // Build log file path
        $logPath = CMS_ROOT . '/logs/user_activity.log';

        // Ensure logs directory exists
        $logDir = dirname($logPath);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        // Build log entry
        $entry = [
            'ts' => gmdate('c'),
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? 'unknown',
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'details' => $details
        ];

        // Encode to JSON (one line)
        $json = json_encode($entry, JSON_UNESCAPED_UNICODE);

        // Append to log file (silent failure on error)
        @file_put_contents($logPath, $json . "\n", FILE_APPEND | LOCK_EX);
    }
}
