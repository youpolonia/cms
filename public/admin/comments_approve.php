<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/auth/admin-auth.php';
require_once __DIR__ . '/../../core/commentmanager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    require_once __DIR__ . '/../../includes/auth/csrf-validate.php';
    
    if (!isset($_POST['comment_id'])) {
        $_SESSION['error'] = 'Missing comment ID';
        header('Location: /admin/dashboard.php');
        exit;
    }

    $commentId = (int)$_POST['comment_id'];
    $success = CommentManager::approveComment($commentId);

    if ($success) {
        $_SESSION['success'] = 'Comment approved successfully';
    } else {
        $_SESSION['error'] = 'Failed to approve comment';
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('Location: /admin/dashboard.php');
exit;
