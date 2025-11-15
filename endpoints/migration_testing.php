<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../database/migrations/2025_phase9_tenant_isolation_optimized.php';

header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../core/database.php';
    $pdo = \core\Database::connect();

    $action = $_GET['action'] ?? '';
    $migration = $_GET['migration'] ?? '';

    switch ($action) {
        case 'migrate':
            $result = Migration_2025_Phase9_Tenant_Isolation_Optimized::migrate($pdo);
            echo json_encode(['status' => $result ? 'success' : 'failed']);
            break;

        case 'rollback':
            $result = Migration_2025_Phase9_Tenant_Isolation_Optimized::rollback($pdo);
            echo json_encode(['status' => $result ? 'success' : 'failed']);
            break;

        case 'test':
            $result = Migration_2025_Phase9_Tenant_Isolation_Optimized::test($pdo);
            echo json_encode(['status' => $result ? 'success' : 'failed']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
