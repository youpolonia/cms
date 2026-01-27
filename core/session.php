<?php
declare(strict_types=1);

namespace Core;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        // Only configure if session not started yet and headers not sent
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

                @session_name('CMSSESSID_ADMIN');
                @session_set_cookie_params([
                    'lifetime' => 0,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isHttps,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                @ini_set('session.use_strict_mode', '1');
                @ini_set('session.use_only_cookies', '1');
            }

            @session_start();
        }
        
        self::$started = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']);
    }

    public static function getAdminId(): ?int
    {
        return $_SESSION['admin_id'] ?? null;
    }

    public static function getAdminRole(): ?string
    {
        return $_SESSION['admin_role'] ?? null;
    }

    public static function getAdminUsername(): ?string
    {
        return $_SESSION['admin_username'] ?? null;
    }

    public static function setAdmin(int $id, string $username, string $role = 'admin'): void
    {
        self::regenerate();
        $_SESSION['admin_id'] = $id;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $role;
        $_SESSION['login_time'] = time();
    }

    public static function logout(): void
    {
        self::destroy();
    }
}
