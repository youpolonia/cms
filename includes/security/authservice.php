<?php

namespace Includes\Auth;

use Includes\Core\DatabaseConnection;
use Includes\Auth\AuthServiceInterface;

/**
 * Authentication and authorization service for CMS users
 *
 * Handles user authentication, permission checks, and role verification.
 * Implements AuthServiceInterface to ensure consistent authentication API.
 *
 * @package Includes\Auth
 * @version 2.1.0
 * @since 2025-01-15
 * @author CMS Development Team
 */
class AuthService implements AuthServiceInterface
{
    private \PDO $db;

    /**
     * Initialize authentication service with database connection
     *
     * @param DatabaseConnection $dbConnection Pre-configured database connection
     * @throws \PDOException If database connection fails
     */
    public function __construct(DatabaseConnection $dbConnection)
    {
        $this->db = \core\Database::connection();
    }

    /**
     * Authenticate user with credentials
     *
     * @param string $username Username or email
     * @param string $password Plain text password
     * @param string|null $tenantId Optional tenant ID for multi-tenant systems
     * @return array|null User data array on success, null on failure
     * @throws \PDOException On database errors
     */
    public function authenticate(string $username, string $password, string $tenantId = null): ?array
    {
        $query = "
            SELECT id, username, password_hash, role, tenant_id
            FROM cms_users
            WHERE (username = :username OR email = :username)
        ";
        
        $params = [':username' => $username];
        
        if ($tenantId) {
            $query .= " AND tenant_id = :tenant_id";
            $params[':tenant_id'] = $tenantId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return [
                'id' => $user['id'],
                'role' => $user['role'],
                'tenant_id' => $user['tenant_id']
            ];
        }

        return null;
    }

    public function checkPermission(int $userId, string $permission): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM cms_user_permissions up
            JOIN cms_permissions p ON up.permission_id = p.id
            WHERE up.user_id = :userId AND p.key = :permission
        ");
        $stmt->execute([':userId' => $userId, ':permission' => $permission]);
        return (bool)$stmt->fetch();
    }

    public function hasRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM cms_user_roles ur
            JOIN cms_roles r ON ur.role_id = r.id
            WHERE ur.user_id = :userId AND r.name = :role
        ");
        $stmt->execute([':userId' => $userId, ':role' => $role]);
        return (bool)$stmt->fetch();
    }
}
