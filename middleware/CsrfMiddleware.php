<?php
/**
 * CSRF Protection Middleware
 */
class CsrfMiddleware {
    private const TOKEN_NAME = 'csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generateToken(): string {
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return bin2hex(openssl_random_pseudo_bytes(self::TOKEN_LENGTH));
    }

    public static function validateToken(string $token): bool {
        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[self::TOKEN_NAME], $token);
    }

    public static function getToken(): string {
        if (empty($_SESSION[self::TOKEN_NAME])) {
            $_SESSION[self::TOKEN_NAME] = self::generateToken();
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[self::TOKEN_NAME] ?? '';
            if (!self::validateToken($token)) {
                http_response_code(403);
                die('Invalid CSRF token');
            }
        }
        
        // Regenerate token after validation
        $_SESSION[self::TOKEN_NAME] = self::generateToken();
    }
}
