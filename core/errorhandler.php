<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

namespace core;

/**
 * Base error handler with standardized patterns
 */
require_once __DIR__.'/Logger/loggerfactory.php';
require_once __DIR__.'/../includes/security/emergency_mode.php';

class ErrorHandler {
    /**
     * Check if request is from admin interface
     */
    protected static function isAdminRequest(): bool {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') === 0;
    }

    const LOG_LEVELS = [
        'DEBUG' => 'debug',
        'INFO' => 'info',
        'WARNING' => 'warning',
        'ERROR' => 'error',
        'CRITICAL' => 'critical'
    ];

    protected static $logFile = __DIR__.'/../logs/errors.log';
    protected static $logLevel = 'ERROR';
    protected static $alertThreshold = 'CRITICAL';
    protected static $alertRecipients = [];
    protected static $logRotationSize = 1048576; // 1MB

    /**
     * Set log file path
     */
    public static function setLogFile(string $path): void {
        self::$logFile = $path;
    }

    /**
     * Set minimum log level
     */
    public static function setLogLevel(string $level): void {
        if (!array_key_exists($level, self::LOG_LEVELS)) {
            throw new \Exception("Invalid log level: $level");
        }
        self::$logLevel = $level;
    }

    /**
     * Set alert threshold level
     */
    public static function setAlertThreshold(string $level): void {
        if (!array_key_exists($level, self::LOG_LEVELS)) {
            throw new \Exception("Invalid alert level: $level");
        }
        self::$alertThreshold = $level;
    }

    /**
     * Set alert recipients
     */
    public static function setAlertRecipients(array $recipients): void {
        self::$alertRecipients = $recipients;
    }

    /**
     * Handle and log errors with standardized format
     */
    public static function handle(
        Exception $e,
        string $level = 'ERROR',
        array $context = []
    ): array {
        // Emergency mode check - terminate non-admin requests
        if (isEmergencyModeActive() && !self::isAdminRequest()) {
            http_response_code(503);
            header('Content-Type: text/html');
            echo '
<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Mode</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d9534f; }
    </style>
</head>
<body>
    <h1>System Maintenance</h1>
    <p>We are currently performing critical system maintenance.</p>
    <p>Please check back later. Thank you for your patience.</p>
</body>
</html>';
            exit;
        }

        if (!in_array($level, self::LOG_LEVELS)) {
            $level = 'ERROR';
        }

        $shouldLog = array_search($level, self::LOG_LEVELS) >= 
                   array_search(self::$logLevel, self::LOG_LEVELS);

        $shouldAlert = array_search($level, self::LOG_LEVELS) >= 
                     array_search(self::$alertThreshold, self::LOG_LEVELS);

        $errorId = uniqid('err_');
        $timestamp = date('Y-m-d H:i:s');
        
        $errorData = [
            'id' => $errorId,
            'timestamp' => $timestamp,
            'level' => $level,
            'type' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'context' => $context,
            'trace' => $e->getTrace()
        ];

        if ($shouldLog) {
            // Enhanced debug output
            $debugFile = dirname(self::$logFile) . '/debug.log';
            file_put_contents($debugFile, "[DEBUG] Starting handle() for error level: $level\n", FILE_APPEND);
            
            // Dual logging implementation
            $logger = \core\Logger\LoggerFactory::getInstance();
            $psrLevel = self::LOG_LEVELS[$level] ?? 'error';
            file_put_contents($debugFile, "[DEBUG] Calling logger with level: $psrLevel\n", FILE_APPEND);
            $logger->$psrLevel($e->getMessage(), $context);
            
            // Also write directly to error log file
            file_put_contents($debugFile, "[DEBUG] Calling writeLog() for: " . self::$logFile . "\n", FILE_APPEND);
            self::writeLog($errorData);
            file_put_contents($debugFile, "[DEBUG] writeLog() completed\n", FILE_APPEND);
        }

        if ($shouldAlert && !empty(self::$alertRecipients)) {
            self::sendAlert($errorData);
        }

        return [
            'error' => [
                'id' => $errorId,
                'message' => "An error occurred (ID: $errorId)",
                'level' => $level,
                'timestamp' => $timestamp
            ]
        ];
    }

    /**
     * Write to log file with rotation check
     */
    protected static function writeLog(array $errorData): void {
        $logFile = self::$logFile;
        $logDir = dirname($logFile);
        $debugFile = $logDir . '/debug.log';
        
        // Write debug info to separate debug log file
        file_put_contents($debugFile, "[DEBUG] Starting writeLog for: $logFile\n", FILE_APPEND);
        
        // Check directory exists and is writable
        if (!is_dir($logDir)) {
            file_put_contents($debugFile, "[DEBUG] Log directory doesn't exist, attempting to create: $logDir\n", FILE_APPEND);
            if (!mkdir($logDir, 0755, true)) {
                file_put_contents($debugFile, "[ERROR] Failed to create log directory: $logDir\n", FILE_APPEND);
                file_put_contents($debugFile, "[ERROR] Last error: " . json_encode(error_get_last()) . "\n", FILE_APPEND);
                return;
            }
            file_put_contents($debugFile, "[DEBUG] Successfully created log directory\n", FILE_APPEND);
        }

        if (!is_writable($logDir)) {
            file_put_contents($debugFile, "[ERROR] Log directory not writable: $logDir\n", FILE_APPEND);
            file_put_contents($debugFile, "[ERROR] Directory permissions: " . decoct(fileperms($logDir) & 0777) . "\n", FILE_APPEND);
            return;
        }

        // Check log rotation needed
        if (file_exists($logFile) && filesize($logFile) > self::$logRotationSize) {
            file_put_contents($debugFile, "[DEBUG] Log rotation needed for: $logFile\n", FILE_APPEND);
            self::rotateLog();
        }

        $logEntry = json_encode($errorData)."\n";
        file_put_contents($debugFile, "[DEBUG] Prepared log entry: " . substr($logEntry, 0, 100) . "...\n", FILE_APPEND);
        
        $result = @file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        if ($result === false) {
            $lastError = error_get_last();
            $debugMsg = "[ERROR] Failed to write to log file: $logFile\n";
            $debugMsg .= "[ERROR] Last error: " . json_encode($lastError) . "\n";
            $debugMsg .= "[ERROR] File exists: " . (file_exists($logFile) ? 'YES' : 'NO') . "\n";
            $debugMsg .= "[ERROR] File writable: " . (is_writable($logFile) ? 'YES' : 'NO') . "\n";
            if (file_exists($logFile)) {
                $debugMsg .= "[ERROR] File permissions: " . decoct(fileperms($logFile) & 0777) . "\n";
            }
            file_put_contents($debugFile, $debugMsg, FILE_APPEND);
            
            // Also log to system log as fallback
            error_log("Failed to write to error log: " . ($lastError['message'] ?? 'Unknown error'));
        } else {
            file_put_contents($debugFile, "[DEBUG] Successfully wrote $result bytes to log file\n", FILE_APPEND);
        }
        
        // Dual logging fallback - ensure at least one log succeeds
        if ($result === false) {
            error_log($logEntry);
        }
    }

    /**
     * Rotate log file
     */
    protected static function rotateLog(): void {
        $backupFile = self::$logFile . '.' . date('YmdHis');
        rename(self::$logFile, $backupFile);
    }

    /**
     * Send alert to configured recipients
     */
    protected static function sendAlert(array $errorData): void {
        // Basic email alert implementation
        $subject = "[{$errorData['level']}] {$errorData['type']}";
        $message = "Error ID: {$errorData['id']}\n";
        $message .= "Message: {$errorData['message']}\n";
        $message .= "File: {$errorData['file']}:{$errorData['line']}\n";
        
        foreach (self::$alertRecipients as $recipient) {
            mail($recipient, $subject, $message);
        }
    }

    /**
     * Standardized try/catch wrapper
     */
    public static function wrap(callable $fn, array $context = []) {
        try {
            return $fn();
        } catch (Exception $e) {
            return self::handle($e, 'ERROR', $context);
        }
    }
}
