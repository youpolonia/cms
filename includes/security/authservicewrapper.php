<?php
/**
 * AuthServiceWrapper - Provides backward compatibility during auth refactoring
 */
class AuthServiceWrapper {
    private static ?AuthService $instance = null;

    public static function init(): void {
        if (self::$instance === null) {
            $session = new SessionManager();
            self::$instance = new AuthService($session);
        }
    }

    public static function checkAuth(): bool {
        self::init();
        return self::$instance->isAuthenticated();
    }

    public static function checkAdminAuth(): bool {
        self::init();
        return self::$instance->hasRole('admin');
    }

    public static function getUser(): ?array {
        self::init();
        return self::$instance->getCurrentUser();
    }
}
