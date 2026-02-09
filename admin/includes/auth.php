<?php
// Admin Authentication v2.0
// Session-based authentication for admin area
// Uses session keys set by admin/login.php: admin_id, admin_role, admin_username

class AdminAuth {
    const IDLE_TIMEOUT = 1800; // 30 minutes

    public static function isAuthenticated(): bool {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }

        // Check idle timeout via last_regeneration (set by login.php)
        if (isset($_SESSION['last_regeneration'])) {
            if (time() - $_SESSION['last_regeneration'] > self::IDLE_TIMEOUT) {
                self::logout();
                return false;
            }
            $_SESSION['last_regeneration'] = time();
        }

        return true;
    }

    public static function logout(): void {
        unset($_SESSION['admin_id'], $_SESSION['admin_role'], $_SESSION['admin_username']);
        unset($_SESSION['admin_authenticated'], $_SESSION['admin_user_id']);
    }

    public static function hasRole(string $role): bool {
        if (!self::isAuthenticated()) {
            return false;
        }
        return ($_SESSION['admin_role'] ?? '') === $role;
    }

    public static function getCurrentAdmin(): ?array {
        if (!self::isAuthenticated()) {
            return null;
        }
        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null,
            'role' => $_SESSION['admin_role'] ?? null,
        ];
    }
}
