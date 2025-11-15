<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/approvalengine.php';

header('Content-Type: application/json');

try {
    $approvalEngine = new ApprovalEngine();
    $pending = $approvalEngine->getPendingForUser($_SESSION['user_id'] ?? '');
    echo json_encode($pending);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get pending approvals']);
}
