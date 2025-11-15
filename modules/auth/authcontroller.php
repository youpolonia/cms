<?php
namespace modules\auth;

require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/session_boot.php';

class AuthController
{
    public static function login(string $username, string $password): bool
    {
        cms_session_start('admin');

        $user = authenticateAdmin($username, $password, 'admins');
        if (!$user) {
            $user = authenticateAdmin($username, $password, 'users');
        }
        if (!$user) {
            return false;
        }

        // Preserve CSRF token before regenerating session
        $csrfToken = $_SESSION['csrf_token'] ?? null;
        session_regenerate_id(true);
        // Restore CSRF token after regeneration
        if ($csrfToken) {
            $_SESSION['csrf_token'] = $csrfToken;
        }
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_username'] = $user['username'] ?? $username;
        $_SESSION['admin_user_id'] = $user['id'] ?? null;
        return true;
    }

    /**
     * Ensure admin session exists and user is authenticated,
     * otherwise redirect to /admin/login.php.
     */
    public static function requireLogin(): void
    {
        cms_session_start('admin');
        if (empty($_SESSION['admin_authenticated'])) {
            header('Location: /admin/login.php', true, 302);
            exit;
        }
    }

    /**
     * Return current admin username or a safe fallback.
     */
    public static function getCurrentUsername(): string
    {
        cms_session_start('admin');
        $u = $_SESSION['admin_username'] ?? null;
        return is_string($u) && $u !== '' ? $u : 'Guest';
    }

    public static function logout(): void
    {
        cms_session_start('admin');
        header('Location: /admin/logout.php', true, 303);
        exit;
    }
}
