<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../services/CommentManager.php';

// Verify admin access
if (!AdminAuth::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// CSRF protection
if (empty($_POST['csrf_token']) || !AdminAuth::verifyCsrfToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: dashboard.php');
    exit;
}

// Confirmation step
if (empty($_POST['confirm']) || $_POST['confirm'] !== 'yes') {
    $_SESSION['error'] = 'Please confirm deletion';
    header('Location: dashboard.php');
    exit;
}

// Validate comment ID
if (empty($_POST['comment_id'])) {
    $_SESSION['error'] = 'Invalid comment ID';
    header('Location: dashboard.php');
    exit;
}

$commentId = (int)$_POST['comment_id'];
$commentManager = CommentManager::getInstance();

try {
    if ($commentManager->deleteComment($commentId)) {
        $_SESSION['success'] = 'Comment deleted successfully';
    } else {
        $_SESSION['error'] = 'Failed to delete comment';
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Error deleting comment: ' . $e->getMessage();
}

header('Location: dashboard.php');
exit;
