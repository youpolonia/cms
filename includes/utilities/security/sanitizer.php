<?php

/**
 * Centralized sanitization service for the CMS
 */
class Sanitizer {
    /**
     * Sanitize HTML input
     * @param string $input HTML to sanitize
     * @param array $allowedTags Allowed HTML tags (default: basic formatting)
     * @return string Sanitized HTML
     */
    public static function html(string $input, array $allowedTags = ['b', 'i', 'u', 'p', 'br', 'ul', 'ol', 'li', 'a']): string {
        $allowed = '';
        foreach ($allowedTags as $tag) {
            $allowed .= "<$tag>";
        }
        
        return strip_tags($input, $allowed);
    }

    /**
     * Sanitize plain text input
     * @param string $input Text to sanitize
     * @return string Sanitized text
     */
    public static function text(string $input): string {
        return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Sanitize email address
     * @param string $input Email to sanitize
     * @return string Sanitized email or empty string if invalid
     */
    public static function email(string $input): string {
        $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);
        return filter_var($sanitized, FILTER_VALIDATE_EMAIL) ? $sanitized : '';
    }

    /**
     * Sanitize URL
     * @param string $input URL to sanitize
     * @return string Sanitized URL or empty string if invalid
     */
    public static function url(string $input): string {
        $sanitized = filter_var($input, FILTER_SANITIZE_URL);
        return filter_var($sanitized, FILTER_VALIDATE_URL) ? $sanitized : '';
    }

    /**
     * Sanitize integer input
     * @param mixed $input Value to sanitize
     * @return int Sanitized integer or 0 if invalid
     */
    public static function int($input): int {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT) ?: 0;
    }

    /**
     * Sanitize float input
     * @param mixed $input Value to sanitize
     * @return float Sanitized float or 0.0 if invalid
     */
    public static function float($input): float {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 0.0;
    }

    /**
     * Sanitize boolean input
     * @param mixed $input Value to sanitize
     * @return bool Sanitized boolean
     */
    public static function bool($input): bool {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }
}
