<?php
/**
 * Comprehensive error logging system
 */
class ErrorLogger {
    private static $logFile = __DIR__.'/../logs/errors.log';
    private static $devMode = false;

    /**
     * Log an error with context
     */
    public static function logError($message, $exception = null) {
        $logEntry = self::buildLogEntry($message, $exception);
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
        
        if (self::$devMode) {
            self::displayError($message, $exception);
        }
    }

    /**
     * Build complete log entry with context
     */
    private static function buildLogEntry($message, $exception) {
        $timestamp = date('Y-m-d H:i:s');
        $requestData = json_encode($_REQUEST, JSON_PRETTY_PRINT);
        $stackTrace = $exception ? $exception->getTraceAsString() : debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        return sprintf(
            "[%s] %s\nRequest Data: %s\nStack Trace:\n%s\n\n",
            $timestamp,
            $message,
            $requestData,
            is_string($stackTrace) ? $stackTrace : print_r($stackTrace, true)
        );
    }

    /**
     * Display error in development mode
     */
    private static function displayError($message, $exception) {
        echo "
<div style='background:#fdd;padding:1em;border:2px solid red;margin:1em;'>";
        echo "
<h3>DEV ERROR: {$message}</h3>";
        if ($exception) {
            echo "
<pre>Exception: ".htmlspecialchars(
$exception->getMessage())."</pre>";
            echo "
<pre>".htmlspecialchars($exception->getTraceAsString())."</pre>";
        }
        echo "
<pre>Request Data: ".htmlspecialchars(print_r(
$_REQUEST, true))."</pre>";
        echo "</div>";
    }

    /**
     * Set development mode
     */
    public static function setDevMode($enabled) {
        self::$devMode = $enabled;
    }
}
