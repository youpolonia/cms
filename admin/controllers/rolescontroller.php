<?php
require_once __DIR__ . '/../../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/csrf.php';
// RolesController.php - Admin interface for managing user roles and permissions

class RolesController {
    private $db;
    private $csrfToken;
    
    public function __construct($db) {
        $this->db = $db;
        $this->csrfToken = bin2hex(random_bytes(32));
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->csrfToken;
        }
    }

    // List all roles
    public function index() {
        $this->checkAdminAccess();
        $roles = $this->db->query("SELECT * FROM user_roles ORDER BY name ASC")->fetchAll();
        // Render roles index view
        require_once __DIR__ . '/../views/roles/index.php';
    }

    // Show create role form
    public function create() {
        $this->checkAdminAccess();
        // Render role creation view
        require_once __DIR__ . '/../views/roles/create.php';
    }

    // Store new role
    public function store() {
        csrf_validate_or_403();
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if (!in_array($name, Roles::all())) {
            $_SESSION['error'] = "Invalid role name";
            header("Location: ?action=create");
            exit;
        }
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        
        $stmt = $this->db->prepare("INSERT INTO user_roles (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $description]);
        
        header("Location: ?action=index");
    }

    // Show edit role form
    public function edit($id) {
        $this->checkAdminAccess();
        $role = $this->db->query("SELECT * FROM user_roles WHERE id = ?", [$id])->fetch();
        // Render role edit view
        require_once __DIR__ . '/../views/roles/edit.php';
    }

    // Update role
    public function update($id) {
        csrf_validate_or_403();
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        if (!in_array($name, Roles::all())) {
            $_SESSION['error'] = "Invalid role name";
            header("Location: ?action=edit&id=$id");
            exit;
        }
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        
        $stmt = $this->db->prepare("UPDATE user_roles SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$name, $description, $id]);
        
        header("Location: ?action=index");
    }

    // Delete role
    public function destroy($id) {
        csrf_validate_or_403();
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        // Check if role is assigned to any users first
        $userCount = $this->db->query("SELECT COUNT(*) FROM user_role_assignments WHERE role_id = ?", [$id])->fetchColumn();
        
        if ($userCount > 0) {
            $_SESSION['error'] = "Cannot delete role assigned to users";
            header("Location: ?action=index");
            return;
        }
        
        $this->db->query("DELETE FROM user_roles WHERE id = ?", [$id]);
        header("Location: ?action=index");
    }

    // Show permission assignment form
    public function permissions($roleId) {
        $this->checkAdminAccess();
        $role = $this->db->query("SELECT * FROM user_roles WHERE id = ?", [$roleId])->fetch();
        $permissions = $this->db->query("SELECT * FROM permissions ORDER BY name ASC")->fetchAll();
        $rolePermissions = $this->db->query("SELECT permission_id FROM role_permissions WHERE role_id = ?", [$roleId])->fetchAll(PDO::FETCH_COLUMN);
        
        // Render role permissions view
        require_once __DIR__ . '/../views/roles/permissions.php';
    }

    // Update role permissions
    public function updatePermissions($roleId) {
        csrf_validate_or_403();
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $this->db->beginTransaction();
        try {
            // Clear existing permissions
            $this->db->query("DELETE FROM role_permissions WHERE role_id = ?", [$roleId]);
            
            // Add selected permissions
            if (isset($_POST['permissions'])) {
                $stmt = $this->db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
                foreach ($_POST['permissions'] as $permissionId) {
                    $stmt->execute([$roleId, $permissionId]);
                }
            }
            
            $this->db->commit();
            $_SESSION['success'] = "Permissions updated successfully";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Failed to update permissions";
        }
        
        header("Location: ?action=permissions&id=$roleId");
    }

    // Show user assignment form
    public function users($roleId) {
        $this->checkAdminAccess();
        $role = $this->db->query("SELECT * FROM user_roles WHERE id = ?", [$roleId])->fetch();
        $users = $this->db->query("SELECT id, username FROM users ORDER BY username ASC")->fetchAll();
        $roleUsers = $this->db->query("SELECT user_id FROM user_role_assignments WHERE role_id = ?", [$roleId])->fetchAll(PDO::FETCH_COLUMN);
        
        // Render role users view
        require_once __DIR__ . '/../views/roles/users.php';
    }

    // Update role users
    public function updateUsers($roleId) {
        csrf_validate_or_403();
        $this->checkAdminAccess();
        $this->validateCsrf();
        
        $this->db->beginTransaction();
        try {
            // Clear existing user assignments
            $this->db->query("DELETE FROM user_role_assignments WHERE role_id = ?", [$roleId]);
            
            // Add selected users
            if (isset($_POST['users'])) {
                $stmt = $this->db->prepare("INSERT INTO user_role_assignments (role_id, user_id) VALUES (?, ?)");
                foreach ($_POST['users'] as $userId) {
                    $stmt->execute([$roleId, $userId]);
                }
            }
            
            $this->db->commit();
            $_SESSION['success'] = "User assignments updated successfully";
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = "Failed to update user assignments";
        }
        
        header("Location: ?action=users&id=$roleId");
    }

    private function checkAdminAccess() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
            header("Location: /admin/login.php");
            exit;
        }
    }

    private function validateCsrf() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['error'] = "Invalid CSRF token";
            header("Location: /admin");
            exit;
        }
    }
}
