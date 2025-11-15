<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../session_boot.php';
class SecurityService {
    const DEFAULT_EXPIRY = 1800; // 30 minutes in seconds
    const TOKEN_NAMESPACE = 'csrf_token';
    
    public static function generateCSRFToken(string $namespace = '', int $expiry = self::DEFAULT_EXPIRY): string {
        if (session_status() === PHP_SESSION_NONE) { cms_session_start('public'); }
        
        $token = bin2hex(random_bytes(32));
        $tokenKey = self::TOKEN_NAMESPACE . ($namespace ? "_$namespace" : '');
        
        $_SESSION[$tokenKey] = [
            'token' => $token,
            'expires' => time() + $expiry
        ];
        return $token;
    }

    public static function validateCSRFToken(string $token, string $namespace = '', bool $checkHeader = false): bool {
        if ($checkHeader && !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        if (session_status() === PHP_SESSION_NONE) { cms_session_start('public'); }

        $tokenKey = self::TOKEN_NAMESPACE . ($namespace ? "_$namespace" : '');
        
        return isset($_SESSION[$tokenKey]['token'])
            && hash_equals($_SESSION[$tokenKey]['token'], $token)
            && $_SESSION[$tokenKey]['expires'] > time();
    }

    public static function getCSRFHeader(): string {
        return 'X-CSRF-TOKEN';
    }
}
