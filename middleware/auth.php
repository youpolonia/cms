<?php
require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../core/session_boot.php';

class AuthMiddleware {
    public static function handle($requiredRole = null) {
        // Start session using CMS session manager
        cms_session_start('public');

        // Check if user is authenticated
        if (!\Core\Auth::check()) {
            http_response_code(401);
            header('Location: /auth/login.php');
            exit;
        }

        // Check role if specified
        if ($requiredRole !== null && !\Core\Auth::hasRole($requiredRole)) {
            http_response_code(403);
            echo 'Access denied - insufficient permissions';
            exit;
        }

        // Continue to next middleware/route
        return true;
    }
}
