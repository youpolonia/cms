<?php
/**
 * Enhanced Permission Management System
 */
class PermissionManager {
    const GDPR_PERMISSIONS = [
        'gdpr_data_access',
        'gdpr_data_deletion',
        'gdpr_consent_management'
    ];

    private $db;

    public function __construct() {
        $this->db = \core\Database::connection();
    }

    /**
     * Get all inherited permissions for a user
     */
    public function getInheritedPermissions(int $userId): array {
        $stmt = $this->db->prepare(
            "WITH RECURSIVE permission_hierarchy AS (
                SELECT up.permission_id, p.name, 1 AS level
                FROM user_permissions up
                JOIN permissions p ON up.permission_id = p.id
                WHERE up.user_id = ?
                
                UNION ALL
                
                SELECT ph.permission_id, p.name, ph.level + 1
                FROM permission_hierarchy ph
                JOIN permission_inheritance pi ON ph.permission_id = pi.parent_id
                JOIN permissions p ON pi.child_id = p.id
            )
            SELECT DISTINCT name FROM permission_hierarchy"
        );
        
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Check if user has any GDPR permissions
     */
    public function hasGdprAccess(int $userId): bool {
        $permissions = $this->getInheritedPermissions($userId);
        return count(array_intersect($permissions, self::GDPR_PERMISSIONS)) > 0;
    }

    /**
     * Check if user can access content based on access level
     */
    public function canAccessContent(int $userId, string $accessLevel): bool {
        // Public content is accessible to everyone
        if ($accessLevel === 'public') {
            return true;
        }

        // Private content requires authenticated user
        if ($accessLevel === 'private') {
            return $userId > 0;
        }

        // Restricted content requires specific permissions
        if ($accessLevel === 'restricted') {
            $permissions = $this->getInheritedPermissions($userId);
            return in_array('content_access_restricted', $permissions);
        }

        // Default deny
        return false;
    }
}
