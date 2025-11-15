<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__.'/../securitymiddleware.php';
require_once __DIR__.'/hookdebugger.php';

// Verify access
SecurityMiddleware::verifyDebugAccess();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $filters = $input['filters'] ?? [];

    $debugger = HookDebugger::getInstance();
    
    // Apply filters
    if (isset($filters['hook_type'])) {
        $debugger->setFilter('hook_type', $filters['hook_type']);
    }
    if (isset($filters['search_term'])) {
        $debugger->setFilter('search_term', $filters['search_term']);
    }

    echo json_encode([
        'hooks' => $debugger->getFilteredHooks(),
        'apiCalls' => $debugger->getFilteredApiCalls()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
