<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';

/**
 * Authentication Middleware
 *
 * Handles role-based access control for API endpoints
 */
class AuthMiddleware {
    private $allowedRoles;

    public function __construct(array $allowedRoles) {
        $this->allowedRoles = $allowedRoles;
    }

    public function authenticate(): void {
        cms_session_start('public');
        $userRole = $_SESSION['user_role'] ?? null;
        
        if (!$userRole || !in_array($userRole, $this->allowedRoles)) {
            throw new Exception('Unauthorized', 401);
        }
    }
}
