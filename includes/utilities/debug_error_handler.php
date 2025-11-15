<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
namespace Includes\Debug;

class ErrorHandler {
    public static function register(): void {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    public static function handleError(
        int $errno, 
        string $errstr, 
        string $errfile = null, 
        int $errline = null
    ): bool {
        $logEntry = sprintf(
            "[%s] ERROR %d: %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errno,
            $errstr,
            $errfile ?? 'unknown',
            $errline ?? 0
        );

        error_log($logEntry);
        return false; // Continue with normal error handling
    }

    public static function handleException(\Throwable $e): void {
        $logEntry = sprintf(
            "[%s] EXCEPTION %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        error_log($logEntry);
    }

    public static function logServiceFailure(string $service, string $message): void {
        error_log(sprintf(
            "[%s] SERVICE FAILURE: %s - %s",
            date('Y-m-d H:i:s'),
            $service,
            $message
        ));
    }
}
