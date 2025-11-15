<?php
require_once __DIR__ . '/../core/csrf.php';
// Verify admin access
require_once __DIR__ . '/../../admin/includes/auth.php';
if (!AdminAuth::isLoggedIn()) {
    header('Location: /admin/login.php');
    exit;
}

csrf_boot('admin');

// Get current page for pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get filter if set
$filter = isset($_GET['filter']) && in_array($_GET['filter'], ['info', 'success', 'warning', 'error']) 
    ? $_GET['filter'] 
    : null;

// Handle mark as read action
if (isset($_POST['mark_as_read']) && isset($_POST['notification_id'])) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    $success = NotificationManager::markAsRead((int)$_POST['notification_id']);
    if ($success) {
        $message = 'Notification marked as read';
    }
}

// Get notifications for current admin user
$notifications = NotificationManager::getAll(AdminAuth::getUserId());
$totalNotifications = count($notifications);

// Apply filter if set
if ($filter) {
    $notifications = array_filter($notifications, fn($n) => $n['type'] === $filter);
}

// Paginate results
$paginatedNotifications = array_slice($notifications, $offset, $perPage);
$totalPages = ceil(count($notifications) / $perPage);

// Include view template
require_once __DIR__ . '/../../admin/views/notifications/list.php';
