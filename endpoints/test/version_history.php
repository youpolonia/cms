<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once dirname(__DIR__, 2) . '/config.php';
require_once __DIR__ . '/../../database/migrations/20250603_version_history.php';

header('Content-Type: application/json');

try {
    $pdo = \core\Database::connection();

    $action = $_GET['action'] ?? '';
    $response = ['status' => 'error', 'message' => 'Invalid action'];

    switch ($action) {
        case 'migrate':
            $success = VersionHistoryMigration::migrate($pdo);
            $response = [
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Version history table created' : 'Migration failed'
            ];
            break;
            
        case 'rollback':
            $success = VersionHistoryMigration::rollback($pdo);
            $response = [
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Version history table dropped' : 'Rollback failed'
            ];
            break;
            
        case 'test':
            $success = VersionHistoryMigration::test($pdo);
            $response = [
                'status' => $success ? 'success' : 'error',
                'message' => $success ? 'Test completed successfully' : 'Test failed'
            ];
            break;
    }

    echo json_encode($response);
} catch (\PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
