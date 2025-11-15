<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

csrf_boot();
cms_session_start('admin');
csrf_validate_or_403();

try {
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    
    if (!$commentId || !in_array($action, ['approve', 'reject'])) {
        http_response_code(400);
        exit;
    }
    
    $db = \core\Database::connection();
    $status = ($action === 'approve') ? 'approved' : 'rejected';
    
    $stmt = $db->prepare("UPDATE comments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $commentId]);
    
    http_response_code(200);
    echo json_encode(['success' => true, 'status' => $status]);
    
} catch (\Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit;
}
