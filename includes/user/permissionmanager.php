<?php

namespace CMS\User;

class PermissionManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createRole(string $name, string $description = ''): bool {
        return $this->db->execute(
            "INSERT INTO roles (name, description) VALUES (?, ?)",
            [$name, $description]
        );
    }

    public function updateRole(int $roleId, string $name, string $description = ''): bool {
        return $this->db->execute(
            "UPDATE roles SET name = ?, description = ? WHERE id = ?",
            [$name, $description, $roleId]
        );
    }

    public function deleteRole(int $roleId): bool {
        $this->db->beginTransaction();
        
        try {
            $this->db->execute("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
            $result = $this->db->execute("DELETE FROM roles WHERE id = ?", [$roleId]);
            
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function createPermission(string $name, string $description = ''): bool {
        return $this->db->execute(
            "INSERT INTO permissions (name, description) VALUES (?, ?)",
            [$name, $description]
        );
    }

    public function updatePermission(int $permissionId, string $name, string $description = ''): bool {
        return $this->db->execute(
            "UPDATE permissions SET name = ?, description = ? WHERE id = ?",
            [$name, $description, $permissionId]
        );
    }

    public function deletePermission(int $permissionId): bool {
        $this->db->beginTransaction();
        
        try {
            $this->db->execute("DELETE FROM role_permissions WHERE permission_id = ?", [$permissionId]);
            $result = $this->db->execute("DELETE FROM permissions WHERE id = ?", [$permissionId]);
            
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function assignPermissionToRole(int $roleId, int $permissionId): bool {
        return $this->db->execute(
            "INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)",
            [$roleId, $permissionId]
        );
    }

    public function revokePermissionFromRole(int $roleId, int $permissionId): bool {
        return $this->db->execute(
            "DELETE FROM role_permissions WHERE role_id = ? AND permission_id = ?",
            [$roleId, $permissionId]
        );
    }

    public function getRolePermissions(int $roleId): array {
        return $this->db->queryAll(
            "SELECT p.* FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = ?",
            [$roleId]
        );
    }

    public function getAllRoles(): array {
        return $this->db->queryAll("SELECT * FROM roles");
    }

    public function getAllPermissions(): array {
        return $this->db->queryAll("SELECT * FROM permissions");
    }

    public function userHasPermission(int $userId, string $permissionName): bool {
        $result = $this->db->query(
            "SELECT COUNT(*) as count FROM permissions p
            JOIN role_permissions rp ON p.id = rp.permission_id
            JOIN user_roles ur ON rp.role_id = ur.role_id
            WHERE ur.user_id = ? AND p.name = ?",
            [$userId, $permissionName]
        );

        return $result['count'] > 0;
    }

    public function assignRoleToUser(int $userId, int $roleId): bool {
        return $this->db->execute(
            "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)",
            [$userId, $roleId]
        );
    }

    public function revokeRoleFromUser(int $userId, int $roleId): bool {
        return $this->db->execute(
            "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $roleId]
        );
    }
}
