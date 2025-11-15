<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../../includes/taskscheduler.php';
require_once __DIR__ . '/../../includes/auth.php';

csrf_boot('admin');

// Verify admin access
verifyAdminAccess();

header('Content-Type: application/json');

csrf_validate_or_403();

// Get task name and action from request
$taskName = $_POST['name'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($taskName) || !in_array($action, ['activate', 'deactivate'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $tenantId = $_SESSION['tenant_id'] ?? 'default';
    $scheduler = new TaskScheduler($tenantId);
    $scheduler->initialize();

    $newStatus = $action === 'activate';
    $scheduler->updateTask($taskName, ['active' => $newStatus]);

    echo json_encode([
        'success' => true,
        'active' => $newStatus
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to update task: ' . $e->getMessage()
    ]);
}
