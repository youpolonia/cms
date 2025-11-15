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
    $approval = $approvalEngine->getStatus($requestId);
    
    if (!$approval) {
        http_response_code(404);
        echo json_encode(['error' => 'Approval not found']);
        exit;
    }

    echo json_encode($approval);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get approval details']);
}
