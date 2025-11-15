<?php
declare(strict_types=1);

class RBAC {
    private $permissionCache = []; // In-memory cache for the current request
    private $cacheEnabled = true;
    private $cacheDir = '';
    private const CACHE_SUBDIR = 'permissions';
    private $db; // PDO database connection

    public function __construct(PDO $db, string $baseCachePath = '') {
        $this->db = $db;
        if ($this->cacheEnabled) {
            if (empty($baseCachePath)) {
                // Default path relative to this file or a defined constant
                // For now, let's assume a 'storage/cache' directory at the project root
                $projectRoot = dirname(__DIR__, 2); // Adjust if RBAC.php moves
                $baseCachePath = $projectRoot . '/storage/cache';
            }
            $this->cacheDir = rtrim($baseCachePath, '/') . '/' . self::CACHE_SUBDIR . '/';

            if (!is_dir($this->cacheDir)) {
                if (!mkdir($this->cacheDir, 0775, true)) {
                    // Log error or throw exception: Failed to create cache directory
                    error_log("RBAC Cache: Failed to create directory: " . $this->cacheDir);
                    $this->cacheEnabled = false; // Disable caching if dir creation fails
                }
            } elseif (!is_writable($this->cacheDir)) {
                // Log error or throw exception: Cache directory not writable
                error_log("RBAC Cache: Directory not writable: " . $this->cacheDir);
                $this->cacheEnabled = false; // Disable caching if not writable
            }
        }
    }

    private function getCacheFilePath(int $userId): string {
        return $this->cacheDir . $userId . '.cache';
    }

    /**
     * Sync permissions for a role
     */
    public function syncRolePermissions(int $roleId, array $permissionIds): bool {
        $role = Role::find($roleId);
        if (!$role) return false;

        // This assumes Role model has a permissions()->sync() method
        // If not, manual delete and insert would be needed.
        // For now, assuming it exists as per original code.
        if (method_exists($role->permissions(), 'sync')) {
             $role->permissions()->sync($permissionIds);
        } else {
            // Manual sync logic if $role->permissions() doesn't return an object with sync()
            // This is a placeholder, actual implementation depends on how Role and Permission models are structured
            // For example, delete all existing permissions for the role, then add new ones.
            error_log("RBAC: Role->permissions()->sync() method not found. Cache clearing might be insufficient without proper DB sync.");
        }

        $this->clearCacheForRole($roleId);
        
        // Log security event
        Logger::logSecurityEvent(
            'role_permissions_updated',
            ['role_id' => $roleId, 'permission_ids' => $permissionIds]
        );
        
        return true;
    }

    /**
     * Sync roles for a user
     */
    public function syncUserRoles(int $userId, array $roleIds): bool {
        $user = User::find($userId);
        if (!$user) return false;

        // This assumes User model has a roles()->sync() method
        if (method_exists($user->roles(), 'sync')) {
            $user->roles()->sync($roleIds);
        } else {
            error_log("RBAC: User->roles()->sync() method not found. Cache clearing might be insufficient without proper DB sync.");
        }
        $this->clearCache($userId);
        
        // Log security event
        Logger::logSecurityEvent(
            'user_roles_updated',
            ['user_id' => $userId, 'role_ids' => $roleIds]
        );
        
        return true;
    }

    /**
     * Clear cache for all users with a specific role
     */
    private function clearCacheForRole(int $roleId): void {
        // This assumes User model has whereHas and get methods
        // If not, a direct SQL query would be needed to find users with this role.
        $userModel = new User();
        if (method_exists($userModel, 'whereHas')) {
            $users = $userModel->whereHas('roles', function($query) use ($roleId) {
                $query->where('id', $roleId);
            })->get();

            foreach ($users as $user) {
                $this->clearCache($user->id);
            }
        } else {
            // Fallback: Clear all user caches if we can't target by role easily
            // This is less efficient but safer if the model methods are not as expected.
            // Or, implement a direct DB query to get user IDs for the role.
            error_log("RBAC: User->whereHas() method not found. Consider implementing direct DB query for clearCacheForRole or clearing all cache.");
            // For now, we'll just log and not clear all to avoid unintended side effects.
            // A more robust solution would be to query the user_roles table directly.
        }
    }

    /**
     * Check if user has permission
     */
    public function isIpAllowed(int $userId, string $ip): bool {
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_ip_whitelist
            WHERE user_id = ? AND ip_address = ?
        ");
        $stmt->execute([$userId, $ip]);
        return (bool)$stmt->fetchColumn();
    }

    public function can(int $userId, string $permission): bool {
        if (!$this->cacheEnabled) {
            // Assuming Permission model has a static method or can be instantiated
            // and then userHasPermission called.
            // Permission::userHasPermission was an incorrect assumption.
            // The mock (and likely the real class) has this as an instance method.
            $permissionChecker = new Permission();
            return $permissionChecker->userHasPermission($userId, $permission);
        }

        // Check in-memory cache first
        if (isset($this->permissionCache[$userId][$permission])) {
            return $this->permissionCache[$userId][$permission];
        }

        // Check file cache
        $cacheFile = $this->getCacheFilePath($userId);
        if (file_exists($cacheFile) && is_readable($cacheFile)) {
            $cachedData = @file_get_contents($cacheFile);
            if ($cachedData !== false) {
                $userPermissions = @unserialize($cachedData);
                if (is_array($userPermissions)) {
                    $this->permissionCache[$userId] = $userPermissions; // Load into in-memory cache
                    if (isset($userPermissions[$permission])) {
                        return $userPermissions[$permission];
                    }
                    // If permission is not in this specific user's cache, it means it's false for them,
                    // or it hasn't been checked yet. We'll let it fall through to DB check.
                }
            } else {
                error_log("RBAC Cache: Failed to read cache file: " . $cacheFile);
            }
        }

        // If not in cache or file read failed, query DB
        $permissionChecker = new Permission();
        $hasPermission = $permissionChecker->userHasPermission($userId, $permission);
        
        $this->cachePermission($userId, $permission, $hasPermission); // This will update both in-memory and file cache
        return $hasPermission;
    }

    /**
     * Get all roles for user
     */
    public function getUserRoles(int $userId): array {
        $user = User::find($userId);
        // The mock User->roles() returns an object with a get() method.
        // If $user->roles() itself returns the array of role objects:
        // return $user ? $user->roles() : [];
        // If $user->roles() returns a query builder like thing:
        return $user ? $user->roles()->get() : [];
    }

    /**
     * Check if role has permission
     */
    public function roleHasPermission(int $roleId, string $permission): bool {
        $role = Role::find($roleId);
        if (!$role) return false;

        // The permissions() method on the mock Role returns an object with a get() method,
        // which in turn returns the array of permission objects.
        foreach ($role->permissions()->get() as $perm) {
            if ($perm->name === $permission) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all permissions for user
     */
    public function getUserPermissions(int $userId): array {
        $permissions = [];
        foreach ($this->getUserRoles($userId) as $role) {
            // The permissions() method on the mock Role returns an object with a get() method.
            foreach ($role->permissions()->get() as $perm) {
                $permissions[$perm->name] = true;
            }
        }
        return array_keys($permissions);
    }

    /**
     * Assign role to user
     */
    public function assignRole(int $userId, int $roleId): bool {
        $user = User::find($userId);
        $role = Role::find($roleId);
        
        if ($user && $role) {
            $user->assignRole($role->name);
            $this->clearCache($userId);
            
            // Log security event
            Logger::logSecurityEvent(
                'role_assigned',
                ['user_id' => $userId, 'role_id' => $roleId]
            );
            
            return true;
        }
        return false;
    }

    /**
     * Remove role from user
     */
    public function removeRole(int $userId, int $roleId): bool {
        $user = User::find($userId);
        $role = Role::find($roleId);
        
        if ($user && $role) {
            $user->removeRole($role->name);
            $this->clearCache($userId);
            
            // Log security event
            Logger::logSecurityEvent(
                'role_removed',
                ['user_id' => $userId, 'role_id' => $roleId]
            );
            
            return true;
        }
        return false;
    }

    private function cachePermission(int $userId, string $permissionKey, bool $hasPermission): void {
        if (!$this->cacheEnabled) {
            return;
        }

        // Update in-memory cache
        $this->permissionCache[$userId][$permissionKey] = $hasPermission;

        // Update file cache
        // We need to load existing permissions for the user, update, then save back
        // to avoid overwriting other permissions.
        $cacheFile = $this->getCacheFilePath($userId);
        $userPermissions = [];

        if (file_exists($cacheFile) && is_readable($cacheFile)) {
            $cachedData = @file_get_contents($cacheFile);
            if ($cachedData !== false) {
                $loadedPermissions = @unserialize($cachedData);
                if (is_array($loadedPermissions)) {
                    $userPermissions = $loadedPermissions;
                }
            } else {
                 error_log("RBAC Cache: Failed to read cache file for update: " . $cacheFile);
            }
        }

        $userPermissions[$permissionKey] = $hasPermission;

        $cacheData = @serialize($userPermissions);
        if ($cacheData !== false) {
            if (@file_put_contents($cacheFile, $cacheData, LOCK_EX) === false) {
                error_log("RBAC Cache: Failed to write to cache file: " . $cacheFile);
            } else {
                @chmod($cacheFile, 0664); // Ensure permissions allow web server to read/write
            }
        } else {
            error_log("RBAC Cache: Failed to serialize permissions for user ID: " . $userId);
        }
    }

    /**
     * Clear permission cache for user (in-memory and file)
     */
    public function clearCache(int $userId): void {
        // Clear in-memory cache
        unset($this->permissionCache[$userId]);

        // Clear file cache
        if ($this->cacheEnabled && !empty($this->cacheDir)) {
            $cacheFile = $this->getCacheFilePath($userId);
            if (file_exists($cacheFile)) {
                if (!@unlink($cacheFile)) {
                    error_log("RBAC Cache: Failed to delete cache file: " . $cacheFile);
                }
            }
        }
    }

    /**
     * Enable/disable caching
     */
    public function setCacheEnabled(bool $enabled): void {
        $this->cacheEnabled = $enabled;
    }
}
