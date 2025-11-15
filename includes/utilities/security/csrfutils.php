<?php
/**
 * CSRF Protection Utilities
 */
class CsrfUtils {
    const SESSION_KEY = 'csrf_token';

    /**
     * Generate and store a CSRF token
     */
    public static function generateToken(): string {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Validate a submitted CSRF token
     */
    public static function validateToken(string $token): bool {
        return isset($_SESSION[self::SESSION_KEY]) && 
               hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
}
