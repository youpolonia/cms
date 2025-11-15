<?php
declare(strict_types=1);

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__.'/../../modules/admin/includes/security.php';
require_once __DIR__.'/../../includes/content/lockmanager.php';

header('Content-Type: application/json');
Security::validateAdminSession();

try {
    $action = $_GET['action'] ?? '';
    $contentId = (int)($_GET['content_id'] ?? 0);
    
    switch ($action) {
        case 'acquire':
            $lockId = LockManager::acquire($contentId, $_SESSION['user_id']);
            echo json_encode(['status' => 'success', 'lock_id' => $lockId]);
            break;
            
        case 'release':
            $lockId = (int)($_GET['lock_id'] ?? 0);
            $success = LockManager::release($lockId);
            echo json_encode(['status' => $success ? 'success' : 'error']);
            break;
            
        case 'status':
            $lockId = (int)($_GET['lock_id'] ?? 0);
            $status = LockManager::getStatus($lockId);
            echo json_encode(['status' => 'success', 'data' => $status]);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
