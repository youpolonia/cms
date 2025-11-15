<?php
declare(strict_types=1);

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../session_boot.php';
require_once __DIR__ . '/../csrf.php';

class AdminUserController {
    private PDO $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    // Verify admin session
    private function verifyAdminSession(): void {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: /admin/login.php');
            exit;
        }
    }

    // List all admin users
    public function index(): void {
        $this->verifyAdminSession();
        
        $stmt = $this->db->query("SELECT * FROM admin_users ORDER BY created_at DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Render view with $users data
        require_once __DIR__ . '/../../admin/views/users/index.php';
    }

    // Show create form
    public function create(): void {
        $this->verifyAdminSession();
        require_once __DIR__ . '/../../admin/views/users/create.php';
    }

    // Store new admin user
    public function store(): void {
        $this->verifyAdminSession();
        csrf_validate_or_403();

        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
        
        // Validate inputs
        if (empty($username) || empty($password) || !in_array($role, ['admin', 'editor', 'viewer'])) {
            $_SESSION['error'] = 'Invalid input data';
            header('Location: /admin/users/create');
            exit;
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("
            INSERT INTO admin_users (username, password_hash, role, status) 
            VALUES (:username, :password_hash, :role, 'active')
        ");
        $stmt->execute([
            ':username' => $username,
            ':password_hash' => $passwordHash,
            ':role' => $role
        ]);

        header('Location: /admin/users');
    }

    // Show edit form
    public function edit(int $id): void {
        $this->verifyAdminSession();
        
        $stmt = $this->db->prepare("SELECT * FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            header('Location: /admin/users');
            exit;
        }

        require_once __DIR__ . '/../../admin/views/users/edit.php';
    }

    // Update admin user
    public function update(int $id): void {
        $this->verifyAdminSession();
        csrf_validate_or_403();

        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
        $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
        $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
        
        // Validate inputs
        if (empty($username) || !in_array($role, ['admin', 'editor', 'viewer']) || 
            !in_array($status, ['active', 'inactive'])) {
            $_SESSION['error'] = 'Invalid input data';
            header('Location: /admin/users/' . $id . '/edit');
            exit;
        }

        // Update password only if provided
        $updatePassword = !empty($password);
        $passwordHash = $updatePassword ? password_hash($password, PASSWORD_DEFAULT) : null;

        $sql = "UPDATE admin_users SET 
            username = :username,
            role = :role,
            status = :status
            " . ($updatePassword ? ", password_hash = :password_hash" : "") . "
            WHERE id = :id";

        $params = [
            ':username' => $username,
            ':role' => $role,
            ':status' => $status,
            ':id' => $id
        ];

        if ($updatePassword) {
            $params[':password_hash'] = $passwordHash;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        header('Location: /admin/users');
    }

    // Delete admin user
    public function destroy(int $id): void {
        $this->verifyAdminSession();
        csrf_validate_or_403();

        $stmt = $this->db->prepare("DELETE FROM admin_users WHERE id = :id");
        $stmt->execute([':id' => $id]);

        header('Location: /admin/users');
    }

    // Update last login timestamp
    public function updateLastLogin(int $userId): void {
        $stmt = $this->db->prepare("
            UPDATE admin_users 
            SET last_login_at = CURRENT_TIMESTAMP 
            WHERE id = :id
        ");
        $stmt->execute([':id' => $userId]);
    }
}
