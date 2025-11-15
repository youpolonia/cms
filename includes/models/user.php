<?php
/**
 * User Model
 *
 * Handles database interactions for the users table.
 *
 * @package CMS
 * @subpackage Models
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

class User extends BaseModel
{
    protected static $table = 'users';
    protected static $columnWhitelist = [
        'id', 'username', 'email', 'first_name', 'last_name', 
        'is_active', 'created_at', 'updated_at', 'tenant_id'
    ];

    /**
     * Finds a user by their ID.
     *
     * @param int $id The user's ID.
     * @return array|false The user data as an associative array, or false if not found.
     */
    public static function findById($id, $tenantId = null)
    {
        $query = static::query()
            ->select(static::$columnWhitelist)
            ->where('id', $id)
            ->where('is_active', 1);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->first();
    }

    /**
     * Finds a user by their username.
     *
     * @param string $username The user's username.
     * @return array|false The user data as an associative array (including password_hash), or false if not found.
     */
    public static function findByUsername($username, $tenantId = null)
    {
        $query = static::query()
            ->select(['id', 'username', 'password_hash', 'email', 'is_active'])
            ->where('username', $username);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->first();
    }

    /**
     * Finds a user by their email address.
     *
     * @param string $email The user's email address.
     * @return array|false The user data as an associative array (including password_hash), or false if not found.
     */
    public static function findByEmail($email, $tenantId = null)
    {
        $query = static::query()
            ->select(['id', 'username', 'password_hash', 'email', 'is_active'])
            ->where('email', $email);

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->first();
    }

    /**
     * Verifies a user's password.
     *
     * @param string $password The plain-text password to verify.
     * @param string $hashedPassword The hashed password from the database.
     * @return bool True if the password matches, false otherwise.
     */
    public static function verifyPassword($password, $hashedPassword)
    {
        return password_verify($password, $hashedPassword);
    }

    /**
     * Creates a new user.
     *
     * @param string $username
     * @param string $password Plain text password
     * @param string $email
     * @param string|null $firstName
     * @param string|null $lastName
     * @param int $isActive
     * @return int|false The ID of the newly created user, or false on failure.
     */
    public static function create($username, $password, $email, $tenantId = null, $firstName = null, $lastName = null, $isActive = 1)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!$passwordHash) {
            return false;
        }

        $data = [
            'username' => $username,
            'password_hash' => $passwordHash,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_active' => $isActive
        ];

        if ($tenantId !== null) {
            $data['tenant_id'] = $tenantId;
        }

        return static::query()->insert($data);
    }

    /**
     * Assigns a role to a user.
     *
     * @param int $userId
     * @param int $roleId
     * @return bool True on success, false on failure or if already assigned.
     */
    public static function assignRole($userId, $roleId)
    {
        $exists = static::query()
            ->from('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();

        if ($exists) {
            return true;
        }

        return static::query()
            ->from('user_roles')
            ->insert(['user_id' => $userId, 'role_id' => $roleId]);
    }

    /**
     * Removes a role from a user.
     *
     * @param int $userId
     * @param int $roleId
     * @return bool True on success, false on failure.
     */
    public static function removeRole($userId, $roleId)
    {
        return static::query()
            ->from('user_roles')
            ->where('user_id', $userId)
            ->where('role_id', $roleId)
            ->delete();
    }

    /**
     * Gets all roles assigned to a specific user.
     *
     * @param int $userId
     * @return array An array of role data (id, name).
     */
    public static function getUserRoles($userId)
    {
        return static::query()
            ->select(['r.id', 'r.name'])
            ->from('roles r')
            ->join('user_roles ur', 'r.id = ur.role_id')
            ->where('ur.user_id', $userId)
            ->get();
    }

    /**
     * Checks if a user has a specific role.
     *
     * @param int $userId
     * @param string $roleName
     * @return bool True if the user has the role, false otherwise.
     */
    public static function hasRole($userId, $roleName)
    {
        $roles = static::getUserRoles($userId);
        foreach ($roles as $role) {
            if ($role['name'] === $roleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if a user has a specific permission.
     *
     * @param int $userId
     * @param string $permissionName The name of the permission (e.g., 'create_page').
     * @return bool True if the user has the permission, false otherwise.
     */
    public static function hasPermission($userId, $permissionName)
    {
        $count = static::query()
            ->selectRaw('COUNT(*) as count')
            ->from('user_roles ur')
            ->join('role_permissions rp', 'ur.role_id = rp.role_id')
            ->join('permissions p', 'rp.permission_id = p.id')
            ->where('ur.user_id', $userId)
            ->where('p.name', $permissionName)
            ->value('count');

        return $count > 0;
    }

    /**
     * Counts the total number of users in the system.
     *
     * @return int The total number of users.
     */
    public static function countAll($tenantId = null)
    {
        $query = static::query()->selectRaw('COUNT(*) as total');
        
        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return (int)$query->value('total');
    }

    /**
     * Creates a password reset token for a user
     *
     * @param int $userId
     * @return string|false The reset token or false on failure
     */
    public static function createPasswordResetToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updated = static::query()
            ->where('id', $userId)
            ->update([
                'reset_token' => $token,
                'reset_token_expires' => $expires
            ]);

        return $updated ? $token : false;
    }

    /**
     * Validates a password reset token
     *
     * @param string $token
     * @return array|false User data if valid token, false otherwise
     */
    public static function validatePasswordResetToken($token)
    {
        return static::query()
            ->select(['id', 'email'])
            ->where('reset_token', $token)
            ->where('reset_token_expires', '>', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Updates a user's password and clears reset token
     *
     * @param int $userId
     * @param string $newPassword
     * @return bool True on success, false on failure
     */
    public static function updatePassword($userId, $newPassword)
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!$passwordHash) {
            return false;
        }

        return static::query()
            ->where('id', $userId)
            ->update([
                'password_hash' => $passwordHash,
                'reset_token' => null,
                'reset_token_expires' => null
            ]);
    }
}
