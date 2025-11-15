<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/core/apiresponse.php';
require_once __DIR__ . '/../../includes/core/auth.php';

// Verify API key and permissions
if (!Auth::verifyAPIKey()) {
    APIResponse::rejectUnauthorized();
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    APIResponse::rejectMethodNotAllowed();
    exit;
}

try {
    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    $conflictId = (int)($input['id'] ?? 0);
    $resolution = trim($input['resolution'] ?? '');
    
    if ($conflictId <= 0 || empty($resolution)) {
        APIResponse::rejectBadRequest('Invalid conflict ID or resolution');
    }

    // TODO: Implement resolution logic and DB update
    
    APIResponse::success([
        'message' => 'Resolution submitted successfully',
        'conflict_id' => $conflictId
    ]);
} catch (Exception $e) {
    APIResponse::rejectServerError($e->getMessage());
}
