<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__.'/../database/migrations/2025_phase10_tenants.php';
require_once __DIR__.'/../database/migrations/2025_phase10_federation_metadata.php';

class Phase10MigrationController {
    public static function handleRequest(\PDO $pdo, string $action): array {
        switch ($action) {
            case 'migrate-tenants':
                $success = Phase10_Tenants_Migration::migrate($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Tenants table created' : 'Tenants migration failed'
                ];

            case 'rollback-tenants':
                $success = Phase10_Tenants_Migration::rollback($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Tenants table dropped' : 'Tenants rollback failed'
                ];

            case 'test-tenants':
                $success = Phase10_Tenants_Migration::test($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Tenants test passed' : 'Tenants test failed'
                ];

            case 'migrate-federation':
                $success = Phase10_Federation_Metadata_Migration::migrate($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Federation metadata table created' : 'Federation migration failed'
                ];

            case 'rollback-federation':
                $success = Phase10_Federation_Metadata_Migration::rollback($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Federation metadata table dropped' : 'Federation rollback failed'
                ];

            case 'test-federation':
                $success = Phase10_Federation_Metadata_Migration::test($pdo);
                return [
                    'status' => $success ? 'success' : 'error',
                    'message' => $success ? 'Federation test passed' : 'Federation test failed'
                ];

            default:
                return [
                    'status' => 'error',
                    'message' => 'Invalid action'
                ];
        }
    }
}
