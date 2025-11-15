<?php
require_once __DIR__ . '/../core/notificationmanager.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot();

// Check admin permissions
cms_session_start('admin');
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php');
    exit;
}

// Handle mark all as read
if (isset($_POST['mark_all_read'])) {
    csrf_validate_or_403();
    $success = NotificationManager::markAllAsRead();
    if ($success) {
        $message = "All notifications marked as read";
    }
}

// Get notifications
$notifications = NotificationManager::getQueuedNotifications();
$filter = $_GET['filter'] ?? 'all';
if ($filter !== 'all') {
    $notifications = array_filter($notifications, fn($n) => $n['type'] === $filter);
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Notifications</title>
    <style>
        .notification {
            border-left: 5px solid;
            padding: 15px;
            margin: 10px 0;
            background: #f9f9f9;
        }
        .notification.unread {
            background: #fff;
            font-weight: bold;
        }
        .notification.info { border-color: #3498db; }
        .notification.warning { border-color: #f39c12; }
        .notification.error { border-color: #e74c3c; }
        .notification.system { border-color: #2ecc71; }
        .notification .context {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .notification .timestamp {
            font-size: 0.8em;
            color: #999;
        }
        .filter-controls {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>System Notifications</h1>

    <?php if (isset($message) && !empty($message)): ?>
        <div class="notification info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <div class="filter-controls">
        <form method="get">
            <label for="filter">Filter by type:</label>
            <select name="filter" id="filter" onchange="this.form.submit()">
                <option value="all"<?= $filter === 'all' ? ' selected' : '' ?>>All Types</option>
                <option value="info"<?= $filter === 'info' ? ' selected' : '' ?>>Info</option>
                <option value="warning"<?= $filter === 'warning' ? ' selected' : '' ?>>Warning</option>
                <option value="error"<?= $filter === 'error' ? ' selected' : '' ?>>Error</option>
                <option value="system"<?= $filter === 'system' ? ' selected' : '' ?>>System</option>
            </select>
        </form>

        <form method="post">
            <?= csrf_field(); 
?>            <button type="submit" name="mark_all_read">Mark All as Read</button>
        </form>
    </div>

    <?php if (empty($notifications)): ?>
        <p>No notifications found.</p>
    <?php else: ?>        <?php foreach ($notifications as $notification): ?>
            <div class="notification <?= $notification['type'] ?> <?= empty($notification['read']) ? 'unread' : '' ?>">
                <div class="timestamp">
                    <?= date('Y-m-d H:i:s', $notification['timestamp'] ?? time()) 
?>                </div>
                <div class="message"><?= htmlspecialchars($notification['message'] ?? '') ?></div>
                <?php if (!empty($notification['context'])): ?>
                    <div class="context">
                        <?= htmlspecialchars(is_array($notification['context']) ? json_encode($notification['context']) : ($notification['context'] ?? '')) 
?>                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>    <?php endif; ?>
</body>
</html>
