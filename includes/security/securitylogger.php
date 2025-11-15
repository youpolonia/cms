<?php
namespace Security;

class SecurityLogger {
    private const LOG_FILE = __DIR__.'/../logs/security.log';
    
    public function logViolation(array $data): void {
        $logEntry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $data['type'] ?? 'SECURITY_VIOLATION',
            $data['message'] ?? 'Unknown security violation'
        );
        
        file_put_contents(
            self::LOG_FILE,
            $logEntry,
            FILE_APPEND | LOCK_EX
        );
    }
    
    public static function logCsrfViolation(string $ip): void {
        $logger = new self();
        $logger->logViolation([
            'type' => 'CSRF_FAILURE',
            'message' => "CSRF token validation failed for IP: $ip"
        ]);
    }

    public static function logFailedLogin(string $username, string $reason): void {
        $logger = new self();
        $logger->logViolation([
            'type' => 'LOGIN_FAILURE',
            'message' => "Failed login attempt for $username: $reason"
        ]);
    }
}
