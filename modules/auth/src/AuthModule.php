<?php
namespace Modules\Auth;

use PDO;
use Includes\Auth\WorkerAuthController;
use Includes\Core\Request;
use Includes\Core\Response;

class AuthModule {
    private $db;
    private $authController;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->authController = new WorkerAuthController($db);
    }

    /**
     * Register a new user
     */
    public function registerUser(array $userData): array {
        // Validate required fields
        $required = ['tenant_id', 'name', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'message' => "$field is required"];
            }
        }

        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$userData['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        // Insert user
        $stmt = $this->db->prepare("
            INSERT INTO users (tenant_id, name, email, password)
            VALUES (?, ?, ?, ?)
        ");
        $success = $stmt->execute([
            $userData['tenant_id'],
            $userData['name'],
            $userData['email'],
            $hashedPassword
        ]);

        if (!$success) {
            return ['success' => false, 'message' => 'Registration failed'];
        }

        return ['success' => true, 'message' => 'Registration successful'];
    }

    /**
     * Login user (delegates to WorkerAuthController)
     */
    public function login(string $email, string $password): array {
        return $this->authController->login($email, $password);
    }

    /**
     * Logout user (delegates to WorkerAuthController)
     */
    public function logout(): array {
        return $this->authController->logout();
    }

    /**
     * Initiate password reset
     */
    public function initiatePasswordReset(string $email): array {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store token in database
        $stmt = $this->db->prepare("
            UPDATE users 
            SET reset_token = ?, reset_expires = ?
            WHERE email = ?
        ");
        $success = $stmt->execute([$token, $expires, $email]);

        if (!$success || $stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Password reset failed'];
        }

        // TODO: Send email with reset link
        return ['success' => true, 'message' => 'Password reset initiated'];
    }

    /**
     * Complete password reset
     */
    public function completePasswordReset(string $token, string $newPassword): array {
        // Validate token
        $stmt = $this->db->prepare("
            SELECT id FROM users 
            WHERE reset_token = ? AND reset_expires > NOW()
        ");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired token'];
        }

        // Update password and clear token
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE id = ?
        ");
        $success = $stmt->execute([$hashedPassword, $user['id']]);

        if (!$success) {
            return ['success' => false, 'message' => 'Password update failed'];
        }

        return ['success' => true, 'message' => 'Password updated successfully'];
    }

    /**
     * Check if user is logged in (delegates to WorkerAuthController)
     */
    public function isLoggedIn(Request $request): bool {
        return $this->authController->isLoggedIn($request);
    }

    /**
     * Get current user ID (delegates to WorkerAuthController)
     */
    public function getUserId(Request $request): ?int {
        return $this->authController->getUserId($request);
    }

    /**
     * Check if user has role (delegates to WorkerAuthController)
     */
    public function hasRole(Request $request, string $role): bool {
        return $this->authController->hasRole($request, $role);
    }
    /**
     * Show login form (route handler)
     */
    public function handleShowLoginForm(): string {
        return file_get_contents(__DIR__ . '/../../templates/auth/login.html');
    }

    /**
     * Handle login form submission (route handler)
     */
    public function handleLogin(): void {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = $this->login($email, $password);
        if ($result['success']) {
            header('Location: /dashboard');
        } else {
            header('Location: /auth/login?error=' . urlencode($result['message']));
        }
        exit;
    }

    /**
     * Show registration form (route handler)
     */
    public function handleShowRegistrationForm(): string {
        return file_get_contents(__DIR__ . '/../../templates/auth/register.html');
    }

    /**
     * Handle registration form submission (route handler)
     */
    public function handleRegister(): void {
        $userData = [
            'tenant_id' => $_POST['tenant_id'] ?? '',
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? ''
        ];

        $result = $this->registerUser($userData);
        if ($result['success']) {
            header('Location: /auth/login?success=' . urlencode($result['message']));
        } else {
            header('Location: /auth/register?error=' . urlencode($result['message']));
        }
        exit;
    }

    /**
     * Handle logout (route handler)
     */
    public function handleLogout(): void {
        $this->logout();
        header('Location: /auth/login');
        exit;
    }
}
