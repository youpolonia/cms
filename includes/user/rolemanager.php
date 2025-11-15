<?php

namespace CMS\User;

class RoleManager {
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
            $this->db->execute("DELETE FROM user_roles WHERE role_id = ?", [$roleId]);
            $this->db->execute("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
            $result = $this->db->execute("DELETE FROM roles WHERE id = ?", [$roleId]);
            
            $this->db->commit();
            return $result;
        } catch (\Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function assignRoleToUser(int $userId, int $roleId): bool {
        return $this->db->execute(
            "INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)",
            [$userId, $roleId]
        );
    }

    public function removeRoleFromUser(int $userId, int $roleId): bool {
        return $this->db->execute(
            "DELETE FROM user_roles WHERE user_id = ? AND role_id = ?",
            [$userId, $roleId]
        );
    }

    public function getUserRoles(int $userId): array {
        return $this->db->queryAll(
            "SELECT r.* FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?",
            [$userId]
        );
    }

    public function getAllRoles(): array {
        return $this->db->queryAll("SELECT * FROM roles");
    }
}
