<?php

declare(strict_types=1);

namespace core;

/**
 * Class Auth
 * Handles user authentication and permissions.
 */
class Auth
{
    private static ?array $permissionsConfig = null;

    /**
     * Loads the permissions configuration file.
     *
     * @return array The permissions configuration.
     */
    private static function loadPermissionsConfig(): array
    {
        if (self::$permissionsConfig === null) {
            // Determine the base path of the application
            // This assumes Auth.php is in includes/Core/ and permissions.php is in config/
            // Adjust the path as necessary if your directory structure is different.
            $basePath = dirname(__DIR__, 2); // Moves up two levels from includes/core to the project root
            $permissionsFilePath = $basePath . '/config_core/permissions.php';

            if (!file_exists($permissionsFilePath)) {
                // Handle error: permissions file not found
                // You might want to log this error or throw an exception
                return [];
            }
            self::$permissionsConfig = require_once $permissionsFilePath;
        }
        return self::$permissionsConfig;
    }

    /**
     * Checks if a given role has a specific permission.
     *
     * @param string $role The role to check.
     * @param string $permission The permission to check for.
     * @return bool True if the role has the permission, false otherwise.
     */
    public static function checkPermission(string $role, string $permission): bool
    {
        $config = self::loadPermissionsConfig();
        if (isset($config['roles'][$role]['permissions']) &&
            in_array($permission, $config['roles'][$role]['permissions'], true)) {
            return true;
        }
        return false;
    }

    /**
     * Gets all permissions for a given role.
     *
     * @param string $role The role to get permissions for.
     * @return array An array of permissions for the role, or an empty array if the role doesn't exist or has no permissions.
     */
    public static function getRolePermissions(string $role): array
    {
        $config = self::loadPermissionsConfig();
        return $config['roles'][$role]['permissions'] ?? [];
    }

    /**
     * Gets the label for a given role.
     *
     * @param string $role The role to get the label for.
     * @return string|null The label for the role, or null if the role doesn't exist.
     */
    public static function getRoleLabel(string $role): ?string
    {
        $config = self::loadPermissionsConfig();
        return $config['roles'][$role]['label'] ?? null;
    }

    /**
     * Gets the description for a given permission.
     *
     * @param string $permission The permission to get the description for.
     * @return string|null The description for the permission, or null if the permission doesn't exist.
     */
    public static function getPermissionDescription(string $permission): ?string
    {
        $config = self::loadPermissionsConfig();
        return $config['permissions_map'][$permission] ?? null;
    }

    /**
     * Get all defined roles with their labels.
     * @return array An associative array where keys are role slugs and values are role labels.
     */
    public static function getAllRoles(): array
    {
        $config = self::loadPermissionsConfig();
        $roles = [];
        if (isset($config['roles'])) {
            foreach ($config['roles'] as $roleKey => $roleDetails) {
                $roles[$roleKey] = $roleDetails['label'] ?? $roleKey;
            }
        }
        return $roles;
    }

    /**
     * Get all defined permissions with their descriptions.
     * @return array An associative array where keys are permission slugs and values are their descriptions.
     */
    public static function getAllPermissions(): array
    {
        $config = self::loadPermissionsConfig();
        return $config['permissions_map'] ?? [];
    }

    /**
     * Checks if a user is currently logged in.
     * Assumes session_start() has been called.
     *
     * @return bool True if the user is logged in, false otherwise.
     */
    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Gets the currently logged-in user's details.
     * Assumes session_start() has been called.
     *
     * @return array|null An array with user details (e.g., id, role) or null if not logged in.
     */
    public static function user(): ?\Core\User
    {
        if (!self::check() || !isset($_SESSION['user'])) {
            return null;
        }
        
        $user = json_decode($_SESSION['user'], true);
        return $user ? new \Core\User($user) : null;
    }

    /**
     * Checks if the currently logged-in user has a specific role.
     * Assumes session_start() has been called.
     *
     * @param string $role The role to check for.
     * @return bool True if the user is logged in and has the specified role, false otherwise.
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        return $user ? $user->hasRole($role) : false;
    }
}
