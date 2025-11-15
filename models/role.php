<?php

require_once __DIR__ . '/../config.php';

class Role {
    private static $db;

    public static function init() {
        self::$db = \core\Database::connection();
    }

    // Create a new role
    public static function create(array $data): ?int {
        try {
            $sql = "INSERT INTO user_roles (role_name, description) VALUES (?, ?)";
            $stmt = self::$db->prepare($sql);
            $success = $stmt->execute([$data['role_name'], $data['description'] ?? null]);

            return $success ? self::$db->lastInsertId() : null;
        } catch (\Exception $e) {
            error_log("Role::create() error: " . $e->getMessage());
            return null;
        }
    }

    // Get role by ID
    public static function getById(int $id): ?array {
        try {
            $sql = "SELECT * FROM user_roles WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            error_log("Role::getById() error: " . $e->getMessage());
            return null;
        }
    }

    // Update role
    public static function update(int $id, array $data): bool {
        try {
            $sql = "UPDATE user_roles SET role_name = ?, description = ? WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$data['role_name'], $data['description'] ?? null, $id]);
        } catch (\Exception $e) {
            error_log("Role::update() error: " . $e->getMessage());
            return false;
        }
    }

    // Delete role
    public static function delete(int $id): bool {
        try {
            $sql = "DELETE FROM user_roles WHERE id = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Role::delete() error: " . $e->getMessage());
            return false;
        }
    }

    // Add permission to role
    public static function addPermission(int $roleId, string $permission): bool {
        try {
            $sql = "INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$roleId, $permission]);
        } catch (\Exception $e) {
            error_log("Role::addPermission() error: " . $e->getMessage());
            return false;
        }
    }

    // Remove permission from role
    public static function removePermission(int $roleId, string $permission): bool {
        try {
            $sql = "DELETE FROM role_permissions WHERE role_id = ? AND permission = ?";
            $stmt = self::$db->prepare($sql);
            return $stmt->execute([$roleId, $permission]);
        } catch (\Exception $e) {
            error_log("Role::removePermission() error: " . $e->getMessage());
            return false;
        }
    }

    // Get all permissions for role
    public static function getPermissions(int $roleId): array {
        try {
            $sql = "SELECT permission FROM role_permissions WHERE role_id = ?";
            $stmt = self::$db->prepare($sql);
            $stmt->execute([$roleId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (\Exception $e) {
            error_log("Role::getPermissions() error: " . $e->getMessage());
            return [];
        }
    }

    // Get all roles
    public static function getAll(): array {
        try {
            $sql = "SELECT * FROM user_roles ORDER BY role_name";
            $stmt = self::$db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Role::getAll() error: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize the Role class with database connection
Role::init();
