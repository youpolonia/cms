<?php
require_once __DIR__ . '/../../includes/permission/permissionmanager.php';
require_once __DIR__ . '/../../core/csrf.php';

// Test endpoint
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $result = PermissionManager::revoke(
        (int)($input['roleId'] ?? 0),
        (int)($input['permissionId'] ?? 0)
    );
    
    echo json_encode($result);
    exit;
}
