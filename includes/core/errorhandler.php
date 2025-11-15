<?php

use core\models\LogModel;

/**
 * Global Error Handler for CMS
 *
 * Handles PHP errors, exceptions, and shutdown events
 * Logs errors via LogModel
 * Provides production-safe error messages
 */
class ErrorHandler {
    private static string $securityLogFile = __DIR__ . '/../logs/security.log';
    private static ?int $currentUserId = null;
    private static bool $initialized = false;
    private static ?string $fingerprint = null;
    private static string $logFile = __DIR__ . '/../logs/error_log.txt'; // Kept for backward compatibility

    /**
     * Initialize error handling
     * @param ?string $fingerprint Optional security fingerprint
     * @param ?int $userId Current user ID for logging
     */
    public static function init(?string $fingerprint = null, ?int $userId = null): void {
        if (self::$initialized) {
            return;
        }
        self::$fingerprint = $fingerprint;
        self::$currentUserId = $userId;
        
        // Set handlers
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$initialized = true;
    }

    /**
     * Handle PHP errors
     */
    private static function validateFingerprint(): bool {
        if (self::$fingerprint === null) {
            return true;
        }
        
        $current = hash('sha256',
            ($_SERVER['REMOTE_ADDR'] ?? '') .
            ($_SERVER['HTTP_USER_AGENT'] ?? '') .
            ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')
        );
        
        if ($current !== self::$fingerprint) {
            self::logSecurityEvent('Invalid fingerprint detected');
            return false;
        }
        return true;
    }

    /**
     * Handle PHP errors
     */
    private static function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ): bool {
        $type = match($errno) {
            E_ERROR => 'error',
            E_WARNING => 'warning',
            E_NOTICE => 'notice',
            E_USER_ERROR => 'user_error',
            E_USER_WARNING => 'user_warning',
            E_USER_NOTICE => 'user_notice',
            default => 'error'
        };

        self::logErrorEvent($type, $errstr, [
            'file' => $errfile,
            'line' => $errline,
            'error_code' => $errno
        ]);

        // Don't execute PHP internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $e): void {
        $logger = LoggerFactory::create();
        $logger->error('Uncaught Exception: ' . get_class($e), [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'code' => $e->getCode()
        ]);

        if (defined('APP_ENV') && APP_ENV === 'development') {
            // Show detailed error in development
            $logger->debug('Exception details', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        http_response_code(500);
        require_once __DIR__.'/../views/errors/500.php';
    }

    /**
     * Handle shutdown events
     */
    public static function handleShutdown(): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logErrorEvent('fatal', $error['message'], [
                'file' => $error['file'],
                'line' => $error['line'],
                'type' => $error['type']
            ]);
        }
    }

    /**
     * Log security events (keeps file-based logging for security)
     */
    private static function logSecurityEvent(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $logEntry = "[$timestamp] [$ip] $message\n";
        file_put_contents(self::$securityLogFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Alias for init() for backward compatibility
     */
    public static function register(bool $debugMode = false): void {
        self::init();
    }
    
    /**
     * Configure logging levels
     */
    /**
     * Configure which error levels to log
     * @param array $levels Array of E_* constants to log
     */
    public static function setLogLevels(array $levels): void {
        error_reporting(array_reduce($levels, fn($carry, $level) => $carry | $level, 0));
    }

    /**
     * Configure logging options (backward compatibility)
     * @param array $options Associative array of options:
     *   - 'log_file' => path to log file
     *   - 'security_log' => path to security log
     */
    public static function setLoggingOptions(array $options): void {
        if (isset($options['log_file'])) {
            self::$logFile = $options['log_file'];
        }
        if (isset($options['security_log'])) {
            self::$securityLogFile = $options['security_log'];
        }
    }


    /**
     * Log error to file
     */
    private static function logError(string $message): void {
        // First log to file for backward compatibility
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $timestamp = date('Y-m-d H:i:s');
        $entry = "[$timestamp] $message" . PHP_EOL;
        file_put_contents(self::$logFile, $entry, FILE_APPEND);

        // Then log via LogModel
        $type = 'error';
        if (preg_match('/\[(\w+)\]/', $message, $matches)) {
            $type = strtolower($matches[1]);
        }
        LogModel::logEvent($type, $message, self::$currentUserId);
    }
}
