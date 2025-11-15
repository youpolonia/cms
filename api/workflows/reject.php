<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/approvalengine.php';

header('Content-Type: application/json');

$requestId = basename($_SERVER['REQUEST_URI']);
if (empty($requestId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Request ID required']);
    exit;
}

try {
    $approvalEngine = new ApprovalEngine();
    $success = $approvalEngine->rejectStage($requestId, $_SESSION['user_id'] ?? '');
    
    if (!$success) {
        http_response_code(400);
        echo json_encode(['error' => 'Rejection failed']);
        exit;
    }

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to reject']);
}
