<?php
require_once __DIR__ . '/database.php';

class PermissionManager {
    /**
     * Check if user has a specific permission
     * @param int $user_id
     * @param string $permission_name
     * @return bool
     */
    public static function checkPermission(int $user_id, string $permission_name): bool {
        if ($user_id <= 0 || empty($permission_name)) {
            throw new InvalidArgumentException("Invalid user ID or permission name");
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM role_permissions rp
            JOIN user_roles ur ON rp.role_id = ur.role_id
            JOIN permissions p ON rp.permission_id = p.id
            WHERE ur.user_id = ? AND p.permission_name = ?
        ");
        $stmt->execute([$user_id, $permission_name]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get all roles assigned to a user
     * @param int $user_id
     * @return array
     */
    public static function getUserRoles(int $user_id): array {
        if ($user_id <= 0) {
            throw new InvalidArgumentException("Invalid user ID");
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            SELECT r.id, r.name, r.description
            FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign a role to a user
     * @param int $user_id
     * @param int $role_id
     * @return bool
     */
    public static function assignRole(int $user_id, int $role_id): bool {
        if ($user_id <= 0 || $role_id <= 0) {
            throw new InvalidArgumentException("Invalid user ID or role ID");
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            INSERT INTO user_roles (user_id, role_id)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)
        ");
        return $stmt->execute([$user_id, $role_id]);
    }

    /**
     * Define a new role with permissions
     * @param string $name
     * @param array $permissions
     * @return int Role ID
     */
    public static function defineRole(string $name, array $permissions): int {
        if (empty($name) || empty($permissions)) {
            throw new InvalidArgumentException("Role name and permissions cannot be empty");
        }

        $pdo = \core\Database::connection();
        $pdo->beginTransaction();

        try {
            // Create role
            $stmt = $pdo->prepare("INSERT INTO roles (name) VALUES (?)");
            $stmt->execute([$name]);
            $role_id = $pdo->lastInsertId();

            // Add permissions
            $stmt = $pdo->prepare("
                INSERT INTO role_permissions (role_id, permission_id)
                SELECT ?, p.id FROM permissions p 
                WHERE p.permission_name = ?
            ");

            foreach ($permissions as $permission) {
                $stmt->execute([$role_id, $permission]);
            }

            $pdo->commit();
            return $role_id;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Remove a role from a user
     * @param int $user_id
     * @param int $role_id
     * @return bool
     */
    public static function removeRole(int $user_id, int $role_id): bool {
        if ($user_id <= 0 || $role_id <= 0) {
            throw new InvalidArgumentException("Invalid user ID or role ID");
        }

        $pdo = \core\Database::connection();
        $stmt = $pdo->prepare("
            DELETE FROM user_roles
            WHERE user_id = ? AND role_id = ?
        ");
        return $stmt->execute([$user_id, $role_id]);
    }
}
