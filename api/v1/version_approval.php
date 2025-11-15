<?php
require_once __DIR__ . '/../core/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../includes/controllers/versioncontroller.php';

header('Content-Type: application/json');

// Verify authentication
if (!Auth::check()) {
    echo json_encode(['error' => 'Authentication required'], 401);
    exit;
}

$versionController = new \Includes\Controllers\VersionController();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if ($action === 'approve') {
                $versionId = $input['version_id'] ?? null;
                $notes = $input['notes'] ?? 'Approved by ' . Auth::userId();
                
                if (!$versionId) {
                    throw new Exception('Version ID required');
                }
                
                $result = $versionController->approveVersion($versionId, [
                    'notes' => $notes
                ]);
                echo json_encode($result);
                
            } elseif ($action === 'reject') {
                $versionId = $input['version_id'] ?? null;
                $reason = $input['reason'] ?? 'Rejected by ' . Auth::userId();
                
                if (!$versionId) {
                    throw new Exception('Version ID required');
                }
                
                $result = $versionController->rejectVersion($versionId, [
                    'reason' => $reason
                ]);
                echo json_encode($result);
                
            } else {
                throw new Exception('Invalid action');
            }
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['error' => $e->getMessage()]);
}
