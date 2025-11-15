<?php

namespace Includes\Auth;

class Session
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../../config/session.php';
            
            session_set_cookie_params([
                'lifetime' => $config['lifetime'],
                'path' => $config['path'],
                'domain' => $config['domain'],
                'secure' => $config['secure'],
                'httponly' => $config['http_only'],
                'samesite' => $config['same_site'],
                'partitioned' => $config['cookie_partitioned'] ?? false
            ]);

            if ($config['encrypt']) {
                ini_set('session.use_strict_mode', 1);
                ini_set('session.cookie_httponly', 1);
                ini_set('session.cookie_secure', 1);
                ini_set('session.cookie_samesite', 'Strict');
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_partitioned', $config['cookie_partitioned'] ? 1 : 0);
            }

            require_once __DIR__ . '/../../../../config.php';
            require_once __DIR__ . '/../../../../core/session_boot.php';
            cms_session_start('public');
        }
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function invalidate(): void
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

    public function flash(string $key, $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
