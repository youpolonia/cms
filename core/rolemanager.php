<?php
require_once __DIR__ . '/../config.php';

/**
 * Role Manager - Handles user roles and permissions
 */

require_once __DIR__ . '/auditlogger.php';

class RoleManager {
    private static $instance;
    private $db;

    private function __construct() {
        // Database connection handled by centralized connection
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, string $role): bool {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("
            INSERT INTO user_roles (user_id, role) 
            VALUES (:user_id, :role)
            ON DUPLICATE KEY UPDATE role = :role
        ");
        $result = $stmt->execute([
            ':user_id' => $userId,
            ':role' => $role
        ]);

        if ($result) {
            AuditLogger::log(
                $_SESSION['user_id'] ?? 0,
                'assign_role',
                'user',
                $userId,
                "Assigned role '{$role}'"
            );
        }

        return $result;
    }

    /**
     * Get user's role
     */
    public function getUserRole(int $userId): ?string {
        $pdo = \core\Database::connection();

        $stmt = $pdo->prepare("SELECT role FROM user_roles WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Check if user has permission for action
     */
    public function hasPermission(int $userId, string $action): bool {
        $role = $this->getUserRole($userId);

        // Admin has all permissions
        if ($role === 'admin') return true;

        // Define role permissions
        $permissions = [
            'editor' => ['edit_content', 'submit_review'],
            'viewer' => ['view_content']
        ];

        return in_array($action, $permissions[$role] ?? []);
    }
}
