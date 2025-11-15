<?php

class SecurityLogger {
    /**
     * Logs a security event
     * 
     * @param string $eventType Type of security event
     * @param string $message Descriptive message
     * @param array $context Additional context data (optional)
     * @return bool True if log was written successfully
     */
    public static function log(string $eventType, string $message, array $context = []): bool {
        $logDir = __DIR__ . '/../../../../logs';
        $logFile = $logDir . '/security.log';
        
        // Ensure logs directory exists
        if (!file_exists($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                return false;
            }
        }

        // Prepare context JSON
        $contextJson = '';
        if (!empty($context)) {
            try {
                $contextJson = ' ' . json_encode($context, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $contextJson = ' {"json_error": "Failed to encode context"}';
            }
        }

        // Format log entry
        $timestamp = date('c');
        $logEntry = "[$timestamp] [$eventType] $message$contextJson" . PHP_EOL;

        // Write with exclusive lock
        $result = file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        return $result !== false;
    }
}
