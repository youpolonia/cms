<?php
header('Content-Type: application/json');

// Load required files
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ . '/../../core/taskhandler.php';

// Check for manual trigger
$manualTrigger = isset($_GET['manual']) && $_GET['manual'] === 'true';

try {
    // Initialize task handler
    $taskHandler = new TaskHandler();
    
    // Process tasks
    $result = $taskHandler->processDueTasks($manualTrigger);
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'data' => $result,
        'timestamp' => date('c')
    ]);
    
} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('c')
    ]);
}
