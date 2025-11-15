<?php

namespace Includes\Auth;

interface AuthServiceInterface
{
    /**
     * Authenticate a user with credentials
     * @param string $username Username or email
     * @param string $password Plain text password
     * @param string|null $tenantId Optional tenant ID for multi-tenant systems
     * @return array|null User data array or null if authentication fails
     */
    public function authenticate(string $username, string $password, string $tenantId = null): ?array;

    /**
     * Check if user has specific permission
     * @param int $userId User ID to check
     * @param string $permission Permission key to verify
     * @return bool True if user has permission
     */
    public function checkPermission(int $userId, string $permission): bool;

    /**
     * Check if user has specific role
     * @param int $userId User ID to check
     * @param string $role Role name to verify
     * @return bool True if user has role
     */
    public function hasRole(int $userId, string $role): bool;
}
