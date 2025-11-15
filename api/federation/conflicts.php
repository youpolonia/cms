<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/core/apiresponse.php';
require_once __DIR__ . '/../../includes/core/auth.php';

// Verify API key and permissions
if (!Auth::verifyAPIKey()) {
    APIResponse::rejectUnauthorized();
    exit;
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    APIResponse::rejectMethodNotAllowed();
    exit;
}

try {
    // Get pending conflicts from database
    $conflicts = []; // TODO: Replace with actual DB query
    
    APIResponse::success([
        'conflicts' => $conflicts,
        'count' => count($conflicts)
    ]);
} catch (Exception $e) {
    APIResponse::rejectServerError($e->getMessage());
}
