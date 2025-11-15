<?php
/**
 * Security Utilities for CMS
 * Implements XSS protection, input sanitization, and CSRF protection
 */
class SecurityUtilities {
    /**
     * Escape output to prevent XSS
     * @param string $data The data to escape
     * @return string Escaped data
     */
    public static function escapeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize input data
     * @param mixed $data Input data to sanitize
     * @return mixed Sanitized data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }

    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Validate CSRF token
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Remove Laravel remnants from code
     * @param string $code Code to clean
     * @return string Cleaned code
     */
    public static function removeLaravelRemnants($code) {
        $patterns = [
            '/use Illuminate\\\\.*?;/',
            '/->with\(.*?\)/',
            '/Route::.*?\(.*?\)/',
            '/Schema::.*?\(.*?\)/'
        ];
        return preg_replace($patterns, '', $code);
    }
}
