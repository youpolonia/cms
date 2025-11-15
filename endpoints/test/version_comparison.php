<?php
declare(strict_types=1);

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__.'/../../modules/admin/includes/security.php';
require_once __DIR__ . '/../../includes/content/versionmanager.php';

header('Content-Type: application/json');
Security::validateAdminSession();

try {
    $action = $_GET['action'] ?? '';
    $version1 = (int)($_GET['version1'] ?? 0);
    $version2 = (int)($_GET['version2'] ?? 0);
    
    switch ($action) {
        case 'compare':
            $diff = VersionManager::compareVersions($version1, $version2);
            echo json_encode(['status' => 'success', 'diff' => $diff]);
            break;
            
        case 'bulk_restore':
            $versions = json_decode($_GET['versions'] ?? '[]', true);
            $count = VersionManager::bulkRestore($versions);
            echo json_encode(['status' => 'success', 'restored' => $count]);
            break;
            
        case 'visualize':
            $diffType = $_GET['diff_type'] ?? 'side_by_side';
            $visualization = VersionManager::visualizeDiff($version1, $version2, $diffType);
            echo json_encode(['status' => 'success', 'visualization' => $visualization]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
