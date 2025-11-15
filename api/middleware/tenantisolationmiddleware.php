<?php

class TenantIsolationMiddleware {
    /**
     * Validate tenant context for API requests
     */
    public static function handle() {
        try {
            $tenantId = self::getTenantFromHeaders();
            if (!$tenantId || !self::validateTenant($tenantId)) {
                throw new Exception('Invalid tenant context', 403);
            }
            
            // Set tenant context for subsequent processing
            self::setCurrentTenant($tenantId);
            return true;
        } catch (Exception $e) {
            http_response_code($e->getCode());
            header('Content-Type: application/json');
            echo json_encode([
                'error' => [
                    'code' => 'TENANT_VIOLATION',
                    'message' => $e->getMessage()
                ]
            ]);
            exit;
        }
    }

    private static function getTenantFromHeaders() {
        $headers = getallheaders();
        return $headers['X-Tenant-Context'] ?? null;
    }

    private static function validateTenant($tenantId) {
        // First validate the format
        $isValidFormat = preg_match('/^tenant-[a-z0-9]{8}$/', $tenantId);
        if (!$isValidFormat) {
            return false;
        }

        // Then check session if active
        if (session_status() === PHP_SESSION_ACTIVE) {
            if (isset($_SESSION['tenant_id'])) {
                return $tenantId === $_SESSION['tenant_id'];
            }
        }

        return true; // Allow if no session tenant set
    }

    private static function setCurrentTenant($tenantId) {
        $GLOBALS['current_tenant'] = $tenantId;
        $_SESSION['tenant_id'] = $tenantId; // Store in session
    }
}
