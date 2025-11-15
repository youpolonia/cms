<?php
namespace Includes\Security;

class SecurityMiddleware {
    /**
     * @deprecated Use \Includes\Security\CSRF::generate() instead
     */
    public static function csrfToken() {
        return \Includes\Security\CSRF::generate();
    }

    /**
     * @deprecated Use \Includes\Security\CSRF::validate() instead
     */
    public static function validateCsrf($token) {
        return \Includes\Security\CSRF::validate($token);
    }

    public static function validateSession() {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/session_boot.php';
        cms_session_start('public');
        if (empty($_SESSION['user_id'])) {
            header('HTTP/1.1 401 Unauthorized');
            exit('Unauthorized access');
        }
    }

    public static function hasPermission($requiredRole) {
        return isset($_SESSION['role']) && 
               $_SESSION['role'] === $requiredRole;
    }

    public static function sanitizeInput($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
