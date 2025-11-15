<?php

namespace Core\Security;

class CSRFToken
{
    private const SESSION_KEY = '_csrf_token';

    /**
     * Generate and store a new CSRF token
     */
    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        SecureSession::set(self::SESSION_KEY, $token);
        return $token;
    }

    /**
     * Validate a token against the stored value
     */
    public static function validate(string $token): bool
    {
        if (!SecureSession::has(self::SESSION_KEY)) {
            return false;
        }

        return hash_equals(
            SecureSession::get(self::SESSION_KEY),
            $token
        );
    }

    /**
     * Get the current CSRF token (generates if none exists)
     */
    public static function getToken(): string
    {
        if (!SecureSession::has(self::SESSION_KEY)) {
            return self::generate();
        }
        return SecureSession::get(self::SESSION_KEY);
    }

    /**
     * Get HTML hidden input field with current token
     */
    public static function getInputField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}