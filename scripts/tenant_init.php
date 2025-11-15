<?php
/**
 * Tenant Initialization Script
 * Creates and configures new tenant environments
 */

require_once __DIR__ . '/../includes/core/tenantmanager.php';

class TenantInitializer {
    public static function initializeTenant(string $tenantId, array $config): array {
        $result = [
            'success' => false,
            'errors' => []
        ];

        try {
            // 1. Validate input
            if (empty($tenantId) || !preg_match('/^[a-z0-9_\-]+$/i', $tenantId)) {
                throw new InvalidArgumentException('Invalid tenant ID format');
            }

            // 2. Create tenant directories
            self::createTenantDirectories($tenantId);

            // 3. Initialize tenant database
            $dbResult = TenantManager::createTenantSchema($tenantId);
            if (!$dbResult['success']) {
                throw new RuntimeException('Database initialization failed: ' . implode(', ', $dbResult['errors']));
            }

            // 4. Apply tenant-specific configuration
            TenantManager::configureTenant($tenantId, $config);

            $result['success'] = true;
        } catch (Exception $e) {
            $result['errors'][] = $e->getMessage();
            // Cleanup partial setup
            self::cleanupFailedTenant($tenantId);
        }

        return $result;
    }

    private static function createTenantDirectories(string $tenantId): void {
        $paths = [
            "storage/tenants/$tenantId",
            "storage/tenants/$tenantId/uploads",
            "storage/tenants/$tenantId/cache"
        ];

        foreach ($paths as $path) {
            if (!is_dir($path) && !mkdir($path, 0755, true)) {
                throw new RuntimeException("Failed to create directory: $path");
            }
        }
    }

    private static function cleanupFailedTenant(string $tenantId): void {
        // Remove any created directories
        $basePath = "storage/tenants/$tenantId";
        if (is_dir($basePath)) {
            array_map('unlink', glob("$basePath/*"));
            rmdir($basePath);
        }
    }
}

// CLI execution handler
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    $tenantId = $argv[1];
    $config = json_decode($argv[2] ?? '{}', true);
    
    $result = TenantInitializer::initializeTenant($tenantId, $config);
    
    echo json_encode($result, JSON_PRETTY_PRINT) . PHP_EOL;
    exit($result['success'] ? 0 : 1);
}
