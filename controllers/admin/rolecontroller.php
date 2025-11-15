<?php
class RoleController {
    private Database $db;
    private AuthService $auth;

    public function __construct(Database $db, AuthService $auth) {
        $this->db = $db;
        $this->auth = $auth;
    }

    public function managePermissions(int $roleId): void {
        if (!$this->auth->currentUserHasPermission('manage_roles')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $permissions = $input['permissions'] ?? [];

        try {
            $this->db->beginTransaction();
            
            // Clear existing permissions
            $this->db->query(
                "DELETE FROM role_permissions WHERE role_id = ?",
                [$roleId]
            );

            // Add new permissions
            foreach ($permissions as $permission) {
                $this->db->query(
                    "INSERT INTO role_permissions (role_id, permission) VALUES (?, ?)",
                    [$roleId, $permission]
                );
            }

            $this->db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update permissions']);
        }
    }

    public function getRolePermissions(int $roleId): void {
        if (!$this->auth->currentUserHasPermission('view_roles')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $permissions = $this->db->query(
            "SELECT permission FROM role_permissions WHERE role_id = ?",
            [$roleId]
        )->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['permissions' => $permissions]);
    }

    public function listAllPermissions(): void {
        if (!$this->auth->currentUserHasPermission('view_roles')) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $permissions = $this->db->query(
            "SELECT DISTINCT permission FROM system_permissions"
        )->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode(['permissions' => $permissions]);
    }
}
