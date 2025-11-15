<?php
declare(strict_types=1);

/**
 * AuthService - Framework-free authentication service with tenant isolation
 */
class AuthService {
    /**
     * Authenticate a user
     * 
     * @param string $email User email
     * @param string $password User password
     * @param int $tenantId Tenant ID
     * @return array|null User data if authenticated, null otherwise
     */
    public static function authenticate(string $email, string $password, int $tenantId): ?array {
        $pdo = getDatabaseConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT id, email, password, role_id 
                FROM auth_users 
                WHERE email = :email AND tenant_id = :tenant_id
            ");
            $stmt->execute([
                ':email' => $email,
                ':tenant_id' => $tenantId
            ]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Authentication failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has role
     * 
     * @param int $userId User ID
     * @param string $role Role name
     * @param int $tenantId Tenant ID
     * @return bool True if user has role, false otherwise
     */
    public static function hasRole(int $userId, string $role, int $tenantId): bool {
        $pdo = getDatabaseConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT 1 
                FROM auth_users u
                JOIN auth_roles r ON u.role_id = r.id
                WHERE u.id = :user_id 
                AND r.name = :role 
                AND u.tenant_id = :tenant_id
                AND r.tenant_id = :tenant_id
            ");
            $stmt->execute([
                ':user_id' => $userId,
                ':role' => $role,
                ':tenant_id' => $tenantId
            ]);
            
            return (bool) $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Role check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Start authenticated session
     * 
     * @param array $user User data from authenticate()
     * @return bool True if session started successfully
     */
    public static function startSession(array $user): bool {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/session_boot.php';
        cms_session_start('public');
        
        $_SESSION['auth'] = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'tenant_id' => $user['tenant_id'],
            'authenticated' => true
        ];
        
        return true;
    }

    /**
     * End current session
     */
    public static function endSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * Check if current session is authenticated
     * 
     * @return bool True if authenticated, false otherwise
     */
    public static function isAuthenticated(): bool {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/session_boot.php';
        cms_session_start('public');
        
        return isset($_SESSION['auth']['authenticated']) && $_SESSION['auth']['authenticated'];
    }

    /**
     * Get current authenticated user ID
     * 
     * @return int|null User ID if authenticated, null otherwise
     */
    public static function getCurrentUserId(): ?int {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return $_SESSION['auth']['user_id'];
    }

    /**
     * Get current tenant ID
     * 
     * @return int|null Tenant ID if authenticated, null otherwise
     */
    public static function getCurrentTenantId(): ?int {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return $_SESSION['auth']['tenant_id'];
    }
}
