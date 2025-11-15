<?php
/**
 * Phase 9 Core Engine - Content Federation System
 */

class TenantManager {
    /**
     * Get current tenant from request headers
     */
    public static function getCurrentTenant(): string {
        $headers = getallheaders();
        return $headers['X-Tenant-ID'] ?? 'default';
    }

    /**
     * Validate tenant exists in database
     */
    public static function validateTenant(\PDO $pdo, string $tenantId): bool {
        $stmt = $pdo->prepare("SELECT 1 FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get merged tenant configuration
     */
    public static function getTenantConfig(\PDO $pdo, string $tenantId): array {
        $configPath = __DIR__ . '/../../config/global.php';
        if (!is_file($configPath)) {
            error_log("SECURITY: blocked dynamic include: config/global.php not found");
            return [];
        }
        $base = realpath(__DIR__ . '/../../config');
        $target = realpath($configPath);
        if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
            error_log("SECURITY: blocked dynamic include: invalid config/global.php path");
            return [];
        }
        $globalConfig = require_once $target;
        $tenantConfig = self::fetchTenantConfig($pdo, $tenantId);
        return array_merge($globalConfig, $tenantConfig);
    }

    private static function fetchTenantConfig(\PDO $pdo, string $tenantId): array {
        $stmt = $pdo->prepare("SELECT config FROM tenant_configs WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return json_decode($stmt->fetchColumn() ?? '{}', true);
    }
}

class ContentFederator {
    /**
     * Share content with target sites
     */
    public static function shareContent(
        \PDO $pdo,
        string $contentId,
        array $targetTenants,
        string $sourceTenant
    ): bool {
        try {
            $pdo->beginTransaction();
            
            // Record federation metadata
            $stmt = $pdo->prepare(
                "INSERT INTO content_federation 
                (content_id, source_tenant, target_tenant, status) 
                VALUES (?, ?, ?, 'pending')"
            );

            foreach ($targetTenants as $tenant) {
                $stmt->execute([$contentId, $sourceTenant, $tenant]);
            }

            $pdo->commit();
            return true;
        } catch (\PDOException $e) {
            $pdo->rollBack();
            error_log("Federation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Track status transitions
     */
    public static function logStatusTransition(
        \PDO $pdo,
        string $entityType,
        int $entityId,
        string $fromStatus,
        string $toStatus,
        ?string $reason = null
    ): bool {
        $sql = "INSERT INTO status_transitions 
                (entity_type, entity_id, from_status, to_status, reason) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([$entityType, $entityId, $fromStatus, $toStatus, $reason]);
    }
}

// Web-accessible test endpoint
if (php_sapi_name() !== 'cli' && isset($_GET['test'])) {
    $dbPath = __DIR__ . '/../../core/database.php';
    $base = realpath(__DIR__ . '/../../core');
    $target = realpath($dbPath);
    if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
        error_log("SECURITY: blocked dynamic include: core/database.php");
        http_response_code(500);
        return;
    }
    require_once $target;
    $pdo = \core\Database::connection();
    
    // Test tenant validation
    $tenantId = TenantManager::getCurrentTenant();
    $valid = TenantManager::validateTenant($pdo, $tenantId);
    
    // Test status transition logging
    $logged = ContentFederator::logStatusTransition(
        $pdo,
        'post',
        123,
        'draft',
        'published',
        'Initial publish'
    );
    
    echo json_encode([
        'tenant_validation' => $valid,
        'status_logged' => $logged
    ]);
}
