<?php

namespace Includes\Auth;

use Includes\Multisite\SiteManager;
use Includes\Database\DatabaseConnection;

/**
 * SharedAuthHandler - Handles shared authentication across multiple sites
 */
class SharedAuthHandler
{
    /**
     * @var Auth
     */
    private Auth $auth;
    
    /**
     * @var SiteManager
     */
    private SiteManager $siteManager;
    
    /**
     * @var DatabaseConnection
     */
    private DatabaseConnection $db;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->auth = new Auth();
        $this->siteManager = new SiteManager();
        $this->db = \core\Database::connection();
    }
    
    /**
     * Authenticate user across sites
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function authenticate(string $username, string $password): bool
    {
        // Use the standard authentication method
        $result = $this->auth->authenticate($username, $password);
        
        if ($result) {
            // Log cross-site login
            $this->logCrossSiteLogin($this->auth->getCurrentUser()['id']);
        }
        
        return $result;
    }
    
    /**
     * Log cross-site login
     *
     * @param int $userId
     * @return void
     */
    private function logCrossSiteLogin(int $userId): void
    {
        $this->db->insert('auth_logs', [
            'user_id' => $userId,
            'event_type' => 'cross_site_login',
            'site_id' => $this->siteManager->getCurrentSite(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get user's accessible sites
     *
     * @param int $userId
     * @return array
     */
    public function getUserAccessibleSites(int $userId): array
    {
        $accessibleSites = [];
        $allSites = $this->siteManager->getAllSites();
        
        // Get user's roles
        $userRoles = $this->getUserRoles($userId);
        
        // Check if user has global admin role
        $isGlobalAdmin = in_array('global_admin', $userRoles);
        
        if ($isGlobalAdmin) {
            // Global admins have access to all sites
            return $allSites;
        }
        
        // Get site-specific roles
        $siteRoles = $this->getUserSiteRoles($userId);
        
        foreach ($allSites as $siteId => $siteConfig) {
            // Check if user has site admin role for this site
            if (isset($siteRoles[$siteId]) && in_array('site_admin', $siteRoles[$siteId])) {
                $accessibleSites[$siteId] = $siteConfig;
                continue;
            }
            
            // Check if user has any role for this site
            if (isset($siteRoles[$siteId]) && !empty($siteRoles[$siteId])) {
                $accessibleSites[$siteId] = $siteConfig;
            }
        }
        
        return $accessibleSites;
    }
    
    /**
     * Get user's roles
     *
     * @param int $userId
     * @return array
     */
    private function getUserRoles(int $userId): array
    {
        $roles = [];
        
        $userRoles = $this->db->query(
            "SELECT r.name FROM user_roles ur
             JOIN roles r ON ur.role_id = r.id
             WHERE ur.user_id = ?",
            [$userId]
        )->fetchAll();
        
        foreach ($userRoles as $role) {
            $roles[] = $role['name'];
        }
        
        return $roles;
    }
    
    /**
     * Get user's site-specific roles
     *
     * @param int $userId
     * @return array
     */
    private function getUserSiteRoles(int $userId): array
    {
        $siteRoles = [];
        
        $userSiteRoles = $this->db->query(
            "SELECT r.name, usr.site_id FROM user_site_roles usr
             JOIN roles r ON usr.role_id = r.id
             WHERE usr.user_id = ?",
            [$userId]
        )->fetchAll();
        
        foreach ($userSiteRoles as $role) {
            if (!isset($siteRoles[$role['site_id']])) {
                $siteRoles[$role['site_id']] = [];
            }
            
            $siteRoles[$role['site_id']][] = $role['name'];
        }
        
        return $siteRoles;
    }
    
    /**
     * Check if user has permission for a specific site
     *
     * @param int $userId
     * @param string $permission
     * @param string|null $siteId
     * @return bool
     */
    public function hasPermission(int $userId, string $permission, ?string $siteId = null): bool
    {
        $siteId = $siteId ?? $this->siteManager->getCurrentSite();
        
        // Get user's roles
        $userRoles = $this->getUserRoles($userId);
        
        // Check if user has global admin role
        if (in_array('global_admin', $userRoles)) {
            return true;
        }
        
        // Get site-specific roles
        $siteRoles = $this->getUserSiteRoles($userId);
        
        // Check if user has site admin role for this site
        if (isset($siteRoles[$siteId]) && in_array('site_admin', $siteRoles[$siteId])) {
            return true;
        }
        
        // Check if user has the specific permission
        $rolePermissions = $this->getRolePermissions(array_merge(
            $userRoles,
            $siteRoles[$siteId] ?? []
        ));
        
        return in_array($permission, $rolePermissions);
    }
    
    /**
     * Get permissions for roles
     *
     * @param array $roles
     * @return array
     */
    private function getRolePermissions(array $roles): array
    {
        $permissions = [];
        
        foreach ($roles as $role) {
            $rolePermissions = $this->db->query(
                "SELECT p.name FROM role_permissions rp
                 JOIN permissions p ON rp.permission_id = p.id
                 JOIN roles r ON rp.role_id = r.id
                 WHERE r.name = ?",
                [$role]
            )->fetchAll();
            
            foreach ($rolePermissions as $permission) {
                $permissions[] = $permission['name'];
            }
        }
        
        return array_unique($permissions);
    }
    
    /**
     * Assign role to user for a specific site
     *
     * @param int $userId
     * @param string $roleName
     * @param string|null $siteId
     * @return bool
     */
    public function assignRole(int $userId, string $roleName, ?string $siteId = null): bool
    {
        // Get role ID
        $role = $this->db->query(
            "SELECT id FROM roles WHERE name = ?",
            [$roleName]
        )->fetch();
        
        if (!$role) {
            return false;
        }
        
        if ($siteId) {
            // Assign site-specific role
            if (!$this->siteManager->siteExists($siteId)) {
                return false;
            }
            
            // Check if assignment already exists
            $existing = $this->db->query(
                "SELECT id FROM user_site_roles 
                 WHERE user_id = ? AND role_id = ? AND site_id = ?",
                [$userId, $role['id'], $siteId]
            )->fetch();
            
            if ($existing) {
                return true; // Already assigned
            }
            
            // Insert new assignment
            return $this->db->insert('user_site_roles', [
                'user_id' => $userId,
                'role_id' => $role['id'],
                'site_id' => $siteId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Assign global role
            
            // Check if assignment already exists
            $existing = $this->db->query(
                "SELECT id FROM user_roles 
                 WHERE user_id = ? AND role_id = ?",
                [$userId, $role['id']]
            )->fetch();
            
            if ($existing) {
                return true; // Already assigned
            }
            
            // Insert new assignment
            return $this->db->insert('user_roles', [
                'user_id' => $userId,
                'role_id' => $role['id'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }
    
    /**
     * Remove role from user for a specific site
     *
     * @param int $userId
     * @param string $roleName
     * @param string|null $siteId
     * @return bool
     */
    public function removeRole(int $userId, string $roleName, ?string $siteId = null): bool
    {
        // Get role ID
        $role = $this->db->query(
            "SELECT id FROM roles WHERE name = ?",
            [$roleName]
        )->fetch();
        
        if (!$role) {
            return false;
        }
        
        if ($siteId) {
            // Remove site-specific role
            return $this->db->query(
                "DELETE FROM user_site_roles 
                 WHERE user_id = ? AND role_id = ? AND site_id = ?",
                [$userId, $role['id'], $siteId]
            )->rowCount() > 0;
        } else {
            // Remove global role
            return $this->db->query(
                "DELETE FROM user_roles 
                 WHERE user_id = ? AND role_id = ?",
                [$userId, $role['id']]
            )->rowCount() > 0;
        }
    }
}
