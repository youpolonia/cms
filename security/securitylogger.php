<?php
/**
 * Security Event Logger
 * 
 * Implements comprehensive security event logging as per security verification requirements
 * 
 * Features:
 * - Logs failed login attempts
 * - Records CSRF token failures
 * - Tracks rate limit triggers
 * - Maintains audit trail of security events
 * 
 * @package Security
 * @version 1.0.0
 */
class SecurityLogger {
    private static $logFile = __DIR__ . '/../logs/security.log';
    
    /**
     * Log a security event
     * 
     * @param string $eventType Type of security event (login_failure, csrf_failure, etc)
     * @param string $message Detailed message about the event
     * @param array $context Additional context data (IP, user agent, etc)
     * @return bool True if logged successfully
     */
    public static function logEvent(string $eventType, string $message, array $context = []): bool {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $eventType,
            'message' => $message,
            'context' => $context,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logLine = json_encode($entry) . PHP_EOL;
        
        return file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX) !== false;
    }
    
    /**
     * Log a failed login attempt
     * 
     * @param string $username Attempted username
     * @param string $reason Failure reason
     * @return bool True if logged successfully
     */
    public static function logFailedLogin(string $username, string $reason): bool {
        return self::logEvent('login_failure', "Failed login attempt for $username", [
            'username' => $username,
            'reason' => $reason
        ]);
    }
    
    /**
     * Log a CSRF token failure
     * 
     * @param string $route Affected route
     * @return bool True if logged successfully
     */
    public static function logCsrfFailure(string $route): bool {
        return self::logEvent('csrf_failure', "CSRF token validation failed for $route", [
            'route' => $route
        ]);
    }
}
