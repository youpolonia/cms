<?php

/**
 * TenantService
 * 
 * Handles tenant ID resolution and validation
 */
class TenantService {
    /**
     * Get the current tenant ID
     * 
     * @return string The current tenant ID
     * @throws RuntimeException If tenant ID cannot be determined
     */
    public function getCurrentTenantId(): string {
        if (empty($_SESSION['tenant_id'])) {
            throw new RuntimeException('No tenant ID found in session');
        }
        
        $tenantId = $_SESSION['tenant_id'];
        $this->validateTenantId($tenantId);
        
        return $tenantId;
    }

    /**
     * Validate a tenant ID format
     * 
     * @param string $tenantId The tenant ID to validate
     * @throws InvalidArgumentException If tenant ID is invalid
     */
    public function validateTenantId(string $tenantId): void {
        if (!preg_match('/^[a-z0-9\-]{36}$/', $tenantId)) {
            throw new InvalidArgumentException(
                "Invalid tenant ID format. Must be 36-character alphanumeric string"
            );
        }
    }

    /**
     * Check if tenant exists in database
     * 
     * @param PDO $db Database connection
     * @param string $tenantId Tenant ID to check
     * @return bool True if tenant exists
     */
    public function tenantExists(PDO $db, string $tenantId): bool {
        try {
            $stmt = $db->prepare("
                SELECT 1 FROM tenants 
                WHERE id = :tenant_id
                LIMIT 1
            ");
            $stmt->execute([':tenant_id' => $tenantId]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log("Error checking tenant existence: " . $e->getMessage());
            return false;
        }
    }
}
