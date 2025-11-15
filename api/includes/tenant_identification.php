<?php
// Tenant Identification Middleware
// Version: 1.0
// Date: 2025-05-28

class TenantIdentification {
    public static function handle($request) {
        // Get tenant ID from subdomain or header
        $tenantId = self::identifyTenant($request);
        
        if (!$tenantId) {
            throw new Exception('Tenant identification failed');
        }

        // Store tenant context
        $request['tenant_id'] = $tenantId;
        return $request;
    }

    private static function identifyTenant($request) {
        // Check subdomain first
        $hostParts = explode('.', parse_url($request->getHost(), PHP_URL_HOST));
        if (count($hostParts) > 2) {
            $tenantId = $hostParts[0]; // First subdomain is tenant ID
            if (self::validateTenantId($tenantId)) {
                return $tenantId;
            }
        }
        
        // Fallback to X-Tenant-Key header (matches test cases)
        $tenantId = $request->header('X-Tenant-Key');
        if ($tenantId && self::validateTenantId($tenantId)) {
            return $tenantId;
        }
        
        return null;
    }
    
    private static function validateTenantId($tenantId) {
        try {
            // Get database configuration
            require_once __DIR__ . '/../../../core/database.php';

            // Create a PDO instance
            $db = \core\Database::connection();
            
            // Prepare and execute the query
            $stmt = $db->prepare('SELECT id FROM tenants WHERE id = :id');
            $stmt->execute(['id' => $tenantId]);
            
            // Check if the tenant ID exists
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Log the error or handle it appropriately
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    }
}
