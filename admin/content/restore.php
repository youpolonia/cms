<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/contenthistorymanager.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $contentId = (int)($input['contentId'] ?? 0);
    $versionNumber = (int)($input['versionNumber'] ?? 0);

    // Validate permissions
    Auth::checkContentAccess($contentId);

    // Restore version
    $historyManager = new ContentHistoryManager();
    $success = $historyManager->restoreVersion($contentId, $versionNumber);

    if ($success) {
        // TODO: Add notification trigger here
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to restore version']);
    }
} catch (\Throwable $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Internal error']);
}
