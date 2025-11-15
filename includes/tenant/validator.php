<?php
namespace Tenant;

class Validator {
    /**
     * Validate user has access to tenant
     * @param int $tenantId Tenant ID to validate
     * @param int $userId User ID to check
     * @return bool True if user has access
     */
    public static function validateAccess(int $tenantId, int $userId): bool {
        // Check user_tenants table for association
        $query = "SELECT 1 FROM user_tenants 
                 WHERE user_id = :userId AND tenant_id = :tenantId";
        $result = Database::query($query, [
            ':userId' => $userId,
            ':tenantId' => $tenantId
        ]);
        return !empty($result);
    }
    
    /**
     * Validate resource belongs to tenant
     * @param int $tenantId Tenant ID to validate against
     * @param string $table Table name containing resource
     * @param int $resourceId Resource ID to check
     * @return bool True if resource belongs to tenant
     */
    public static function validateResourceOwnership(int $tenantId, string $table, int $resourceId): bool {
        // Verify table has tenant_id column
        if (!Database::columnExists($table, 'tenant_id')) {
            return false;
        }
        
        $query = "SELECT 1 FROM $table 
                 WHERE id = :resourceId AND tenant_id = :tenantId";
        $result = Database::query($query, [
            ':resourceId' => $resourceId,
            ':tenantId' => $tenantId
        ]);
        return !empty($result);
    }
}
