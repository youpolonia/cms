<?php
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/systemalert.php';

csrf_boot('admin');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (empty($_POST['alert_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

csrf_validate_or_403();

$alert_id = (int)$_POST['alert_id'];
$success = SystemAlert::resolve_alert($alert_id);

if ($success) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to resolve alert']);
}
