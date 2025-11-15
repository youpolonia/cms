<?php

class RoleManager {
    private $db;
    private $permissionManager;

    public function __construct($dbConnection, PermissionManager $permissionManager) {
        $this->db = $dbConnection;
        $this->permissionManager = $permissionManager;
    }

    public function createRole(string $name, string $description): int {
        $stmt = $this->db->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        return $this->db->lastInsertId();
    }

    public function assignPermissionToRole(int $roleId, string $permissionId): bool {
        if (!$this->permissionManager->validatePermission($permissionId)) {
            return false;
        }
        $stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        return $stmt->execute([$roleId, $permissionId]);
    }

    public function getUserRole(int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT r.* FROM roles r
            JOIN user_roles ur ON r.id = ur.role_id
            WHERE ur.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function getPermissionsForRole(int $roleId): array {
        $stmt = $this->db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        $stmt->execute([$roleId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function userHasRole(int $userId, int $roleId, ?int $siteId = null): bool {
        $sql = "SELECT COUNT(*) FROM user_site_roles WHERE user_id = ? AND role_id = ?";
        $params = [$userId, $roleId];
        
        if ($siteId !== null) {
            $sql .= " AND site_id = ?";
            $params[] = $siteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
