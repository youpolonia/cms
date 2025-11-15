<?php

class ExceptionHandler
{
    private static ?object $logger = null;
    private static bool $loggerInitialized = false;

    public static function handle(Throwable $exception): void
    {
        try {
            if (!self::$loggerInitialized) {
                self::initializeLogger();
            }

            if (self::$logger !== null) {
                $logLevel = self::getLogLevel();
                $logData = [
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => $exception->getTraceAsString()
                ];

                self::$logger->log($logLevel, 'Exception occurred', $logData);
            } else {
                // Fallback logging when logger fails
                error_log(sprintf(
                    "Exception: %s in %s:%d\nStack trace:\n%s",
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $exception->getTraceAsString()
                ));
            }
        } catch (Throwable $loggingError) {
            // Ultimate fallback if everything fails
            error_log(sprintf(
                "Failed to log exception: %s\nOriginal exception: %s",
                $loggingError->getMessage(),
                $exception->getMessage()
            ));
        }
    }

    private static function initializeLogger(): void
    {
        try {
            self::$logger = LoggerFactory::create('ExceptionHandler');
            self::$loggerInitialized = true;
        } catch (Throwable $e) {
            error_log(sprintf(
                "Failed to initialize ExceptionHandler logger: %s",
                $e->getMessage()
            ));
            self::$logger = null;
            self::$loggerInitialized = true; // Prevent repeated attempts
        }
    }

    private static function getLogLevel(): string
    {
        if (defined('LOG_LEVEL_OVERRIDES') && 
            is_array(LOG_LEVEL_OVERRIDES) &&
            isset(LOG_LEVEL_OVERRIDES['ExceptionHandler'])) {
            return LOG_LEVEL_OVERRIDES['ExceptionHandler'];
        }
        return 'ERROR';
    }
}
