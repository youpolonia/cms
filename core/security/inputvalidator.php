<?php

namespace Core\Security;

class InputValidator
{
    /**
     * Validate email address
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate username (3-20 chars, alphanumeric + underscore)
     * @param string $username
     * @return bool
     */
    public static function validateUsername(string $username): bool
    {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) === 1;
    }

    /**
     * Validate password (min 8 chars, at least 1 letter and 1 number)
     * @param string $password
     * @return bool
     */
    public static function validatePassword(string $password): bool
    {
        return strlen($password) >= 8 
            && preg_match('/[A-Za-z]/', $password) 
            && preg_match('/[0-9]/', $password);
    }

    /**
     * Sanitize text input (strip tags, trim, escape special chars)
     * @param string $text
     * @return string
     */
    public static function sanitizeText(string $text): string
    {
        return htmlspecialchars(trim(strip_tags($text)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize HTML input (allow basic HTML tags)
     * @param string $html
     * @return string
     */
    public static function sanitizeHTML(string $html): string
    {
        $allowedTags = '<p><a><strong><em><ul><ol><li><h1><h2><h3><h4><h5><h6><br><hr>';
        return strip_tags($html, $allowedTags);
    }

    /**
     * Check if input is numeric
     * @param mixed $input
     * @return bool
     */
    public static function isNumeric($input): bool
    {
        return is_numeric($input);
    }

    /**
     * Basic CSRF token check
     * @param string $token
     * @return bool
     */
    public static function checkCSRF(string $token): bool
    {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}