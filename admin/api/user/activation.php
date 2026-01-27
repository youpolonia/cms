<?php
require_once __DIR__ . '/../../../includes/admin_header.php';
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../core/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    // Verify CSRF token
    if (empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        throw new Exception('CSRF token missing');
    }
    if ($_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    // Check permissions
    if (!hasAnyPermission(['manage_user_activation'])) {
        throw new Exception('Insufficient permissions');
    }

    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Validate input
    if (empty($input['user_id']) || !isset($input['is_active'])) {
        throw new Exception('Missing required fields');
    }

    $userId = (int)$input['user_id'];
    $isActive = (bool)$input['is_active'];

    // Update user status
    $db = \core\Database::connection();
    $stmt = $db->prepare('UPDATE users SET is_active = :is_active WHERE id = :id');
    $stmt->execute([
        ':is_active' => $isActive ? 1 : 0,
        ':id' => $userId
    ]);

    // Log the action
    $stmt = $db->prepare('INSERT INTO admin_logs (admin_id, action, target_id) VALUES (:admin_id, :action, :target_id)');
    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':action' => $isActive ? 'user_activated' : 'user_deactivated',
        ':target_id' => $userId
    ]);

    // Send email notification
    $stmt = $db->prepare('SELECT email FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $userEmail = $stmt->fetchColumn();
    
    if ($userEmail) {
        $subject = $isActive ? 'Account Activated' : 'Account Deactivated';
        $message = $isActive
            ? "Your account has been activated by an administrator."
            : "Your account has been deactivated by an administrator.";
        
        mail($userEmail, $subject, $message, 'From: no-reply@example.com');
    }

    echo json_encode([
        'success' => true,
        'message' => 'User activation status updated',
        'is_active' => $isActive
    ]);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Database error, please try again later.'
    ]);
}
