<?php
namespace Middleware;

use Core\AuthController;

class AdminAuth {
    /**
     * Handle incoming request
     * @param array $sessionData Current session data
     * @param string $redirectUrl URL to redirect if unauthorized
     * @return bool True if authorized, false otherwise
     */
    public static function handle(array $sessionData, string $redirectUrl): bool {
        // Validate session including idle timeout
        if (!AuthController::validateSession($sessionData)) {
            self::redirectToLogin($redirectUrl);
            return false;
        }

        // Update last activity timestamp
        AuthController::updateLastActivity($sessionData);
        
        return true;
    }

    /**
     * Redirect to login page
     * @param string $redirectUrl URL to redirect to
     */
    private static function redirectToLogin(string $redirectUrl): void {
        if (!headers_sent()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
}
