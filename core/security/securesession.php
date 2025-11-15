<?php

namespace Core\Security;

class SecureSession
{
    /**
     * Start secure session with recommended settings
     */
    public static function start(): void
    {
        $sessionConfig = [
            'use_cookies' => 1,
            'use_only_cookies' => 1,
            'cookie_httponly' => 1,
            'cookie_secure' => self::shouldUseSecureCookie(),
            'cookie_samesite' => 'Strict',
            'use_strict_mode' => 1,
            'sid_length' => 128,
            'sid_bits_per_character' => 6,
            'gc_maxlifetime' => 1440 // 24 minutes
        ];

        session_start($sessionConfig);
    }

    /**
     * Regenerate session ID (call after login)
     */
    public static function regenerateId(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Destroy session and remove cookie
     */
    public static function destroy(): void
    {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Set session value
     */
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session value
     */
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Remove session value
     */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Check if session has key
     */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Determine if secure cookie should be used
     */
    private static function shouldUseSecureCookie(): bool
    {
        return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    }
}
