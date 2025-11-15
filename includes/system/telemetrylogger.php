<?php
namespace System;

class TelemetryLogger {
    const TYPE_INFO = 'info';
    const TYPE_WARN = 'warn';
    const TYPE_ERROR = 'error';
    const TYPE_SUCCESS = 'success';
    const TYPE_USAGE = 'usage';
    
    const MAX_LOG_SIZE = 10485760; // 10MB in bytes
    const LOG_FILE = __DIR__ . '/../../logs/telemetry.log';

    private function __construct() {
        // Prevent instantiation
    }

    public static function logEvent(string $type, string $message, array $context = []): bool {
        if (!self::ensureLogDirectory()) {
            return false;
        }

        $logEntry = [
            'timestamp' => gmdate('c'), // ISO 8601 UTC
            'type' => $type,
            'message' => $message,
            'context' => $context
        ];

        $logLine = json_encode($logEntry) . PHP_EOL;
        
        if (self::needsRotation()) {
            self::rotateLog();
        }

        return file_put_contents(self::LOG_FILE, $logLine, FILE_APPEND | LOCK_EX) !== false;
    }

    private static function ensureLogDirectory(): bool {
        $logDir = dirname(self::LOG_FILE);
        if (!file_exists($logDir)) {
            return mkdir($logDir, 0755, true);
        }
        return is_writable($logDir);
    }

    private static function needsRotation(): bool {
        return file_exists(self::LOG_FILE) && 
               filesize(self::LOG_FILE) > self::MAX_LOG_SIZE;
    }

    private static function rotateLog(): bool {
        $backupFile = self::LOG_FILE . '.' . date('YmdHis');
        return rename(self::LOG_FILE, $backupFile);
    }
}
