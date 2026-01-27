<?php
/**
 * Tenant Middleware
 * 
 * Validates and sets tenant context for database operations
 */

class TenantMiddleware {
    /**
     * Handle incoming request
     * 
     * @param array $request The request data
     * @param callable $next Next middleware handler
     * @return mixed Response
     */
    public function handle(array $request, callable $next) {
        // Get tenant ID from request (header, subdomain or path)
        $tenantId = $this->extractTenantId($request);
        
        if (!$tenantId) {
            throw new Exception('Tenant identifier is required');
        }
        
        // Validate tenant exists and is active
        $this->validateTenant($tenantId);
        
        // Set tenant context for database operations
        $GLOBALS['current_tenant'] = $tenantId;
        
        return $next($request);
    }
    
    /**
     * Extract tenant ID from request
     */
    protected function extractTenantId(array $request): ?string {
        // Check X-Tenant-ID header first
        if (!empty($request['headers']['X-Tenant-ID'])) {
            return $request['headers']['X-Tenant-ID'];
        }
        
        // Check subdomain if available
        $host = $request['headers']['Host'] ?? '';
        if (preg_match('/^([a-z0-9-]+)\./', $host, $matches)) {
            return $matches[1];
        }
        
        // Check path segment (e.g. /tenant/123/...)
        if (preg_match('#^/tenant/([a-z0-9-]+)#', $request['path'], $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    /**
     * Validate tenant exists and is active
     */
    protected function validateTenant(string $tenantId): void {
        // Get system connection to check tenant status
        $pdo = \core\Database::connection();
        
        $stmt = $pdo->prepare("
            SELECT 1 FROM tenants 
            WHERE tenant_id = ? AND is_active = 1
        ");
        $stmt->execute([$tenantId]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Invalid or inactive tenant: {$tenantId}");
        }
    }
}
