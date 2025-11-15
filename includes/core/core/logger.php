<?php
/**
 * Simple file logger for CMS
 */
declare(strict_types=1);

namespace Includes\Core;

class Logger
{
    protected static string $logPath = '';
    protected static int $maxFileSize = 1048576; // 1MB
    protected static int $maxAgeDays = 30;

    /**
     * Initialize logger with config path
     */
    public static function init(string $logPath, ?int $maxFileSize = null, ?int $maxAgeDays = null): void
    {
        self::$logPath = \CMS_ROOT . '/' . ltrim($logPath, '/');
        if ($maxFileSize !== null) {
            self::$maxFileSize = $maxFileSize;
        }
        if ($maxAgeDays !== null) {
            self::$maxAgeDays = $maxAgeDays;
        }
    }

    /**
     * Get current log path
     */
    public static function getLogPath(): string
    {
        return self::$logPath;
    }

    /**
     * Log message with timestamp
     */
    public static function log(string $message): void
    {
        if (empty(self::$logPath)) {
            throw new \RuntimeException("Logger not initialized - call init() first");
        }

        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message" . PHP_EOL;
        
        // Ensure logs directory exists and is writable
        $logDir = dirname(self::$logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0775, true);
        }
        if (!is_writable($logDir)) {
            throw new \RuntimeException("Log directory not writable: $logDir");
        }

        file_put_contents(self::$logPath, $logEntry, FILE_APPEND);
        self::rotateIfNeeded();
    }

    /**
     * Rotate logs if size or age exceeds limits
     */
    protected static function rotateIfNeeded(): void
    {
        if (!file_exists(self::$logPath)) {
            return;
        }

        $needsRotation = filesize(self::$logPath) > self::$maxFileSize ||
            (time() - filemtime(self::$logPath)) > (self::$maxAgeDays * 86400);

        if ($needsRotation) {
            self::rotateLogs();
        }
    }

    /**
     * Rotate current log file
     */
    protected static function rotateLogs(): void
    {
        $archivePath = self::$logPath . '.' . date('Y-m-d_His') . '.gz';
        $data = file_get_contents(self::$logPath);
        file_put_contents('compress.zlib://' . $archivePath, $data);
        unlink(self::$logPath);
    }

    /**
     * Get list of archived logs
     */
    public static function getArchivedLogs(): array
    {
        $logDir = dirname(self::$logPath);
        $pattern = basename(self::$logPath) . '.*.gz';
        return glob($logDir . '/' . $pattern) ?: [];
    }
}
