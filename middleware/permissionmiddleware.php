<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../core/session_boot.php';

/**
 * Middleware for handling permission checks
 */
class PermissionMiddleware {
    private $db;
    private $request;

    public function __construct(PDO $db, array $request) {
        $this->db = $db;
        $this->request = $request;
    }

    /**
     * Handle permission check for current request
     */
    public function handle(string $permission): void {
        cms_session_start('public');
        
        // Check if user is authenticated
        if (empty($_SESSION['user_id'])) {
            $this->denyAccess('Authentication required');
        }

        // Get user permissions from session or database
        $permissions = $this->getUserPermissions($_SESSION['user_id']);

        // Check permission directly or through role inheritance
        if (!$this->checkPermission($permission, $permissions)) {
            $this->denyAccess('Insufficient permissions');
        }

        // Validate CSRF token for POST requests
        if ($this->request['method'] === 'POST') {
            $this->validateCsrfToken();
        }
    }

    private function getUserPermissions(int $userId): array {
        // Check session cache first
        if (isset($_SESSION['user_permissions'])) {
            return $_SESSION['user_permissions'];
        }

        // Query database for permissions
        $stmt = $this->db->prepare("
            SELECT p.permission_key 
            FROM user_permissions up
            JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $userId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Cache in session
        $_SESSION['user_permissions'] = $permissions;
        return $permissions;
    }

    private function checkPermission(string $required, array $userPermissions): bool {
        // Direct permission check
        if (in_array($required, $userPermissions)) {
            return true;
        }

        // Check role hierarchy
        $stmt = $this->db->prepare("
            SELECT r.name FROM user_roles ur
            JOIN roles r ON ur.role_id = r.id
            WHERE ur.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $userRoles = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($userRoles as $role) {
            if (PermissionRegistry::roleInherits($role, $required)) {
                return true;
            }
        }

        return false;
    }

    private function validateCsrfToken(): void {
        $token = $this->request['headers']['X-CSRF-Token'] ?? 
                ($this->request['post']['csrf_token'] ?? '');

        if (empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
            $this->denyAccess('Invalid CSRF token');
        }
    }

    private function denyAccess(string $message): void {
        if ($this->request['is_ajax']) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['error' => $message]);
        } else {
            header('HTTP/1.0 403 Forbidden');
            echo "403 Forbidden: $message";
        }
        exit;
    }
}
