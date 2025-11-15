<?php
class SecureLogger {
    private static $logPath = __DIR__ . '/../logs/secure_errors.log';
    
    public static function logError(Exception $e, string $context = '') {
        if (!file_exists(dirname(self::$logPath))) {
            mkdir(dirname(self::$logPath), 0700, true);
        }
        
        $logMessage = sprintf(
            "[%s] [%s] %s: %s\nStack Trace:\n%s\n",
            date('Y-m-d H:i:s'),
            $context,
            get_class($e),
            $e->getMessage(),
            $e->getTraceAsString()
        );
        
        file_put_contents(
            self::$logPath,
            $logMessage,
            FILE_APPEND | LOCK_EX
        );
    }
}
