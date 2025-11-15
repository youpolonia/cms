<?php
namespace Core\Middleware;

use Core\Tenant\TenantManager;
use PDO;

class TenantValidation {
    public function handle(): void {
        if (!TenantManager::validateCurrentTenant()) {
            http_response_code(403);
            echo 'Invalid tenant access';
            exit;
        }
    }

    public function validateTenantAccess(PDO $db, int $userId): void {
        $stmt = $db->prepare("
            SELECT tenant_id FROM user_tenants
            WHERE user_id = ? AND is_active = 1
        ");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() === 0) {
            http_response_code(403);
            echo 'No valid tenant access';
            exit;
        }
    }
}
