<?php
require_once __DIR__ . '/../../includes/admin_auth.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

// Check permissions
if (!hasPermission('notifications', 'view')) {
    header('Location: /admin/');
    exit;
}

$notificationId = $_GET['id'] ?? 0;
$notification = [];

// Fetch notification details
if ($notificationId) {
    $db = \core\Database::connection();
    $notification = $db->querySingleRow(
        "SELECT n.*, u.name as user_name, t.name as tenant_name 
         FROM notifications n
         LEFT JOIN users u ON n.user_id = u.id
         LEFT JOIN tenants t ON n.tenant_id = t.id
         WHERE n.id = ?",
        [$notificationId]
    );

    // Mark as read if unread
    if ($notification && !$notification['read_at']) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $db->update(
            'notifications',
            ['read_at' => date('Y-m-d H:i:s')],
            ['id' => $notificationId]
        );
    }
}

// Set page title
$pageTitle = $notification ? $notification['title'] : 'Notification Not Found';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Admin</title>
    <link href="/admin/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/admin/assets/css/admin.css" rel="stylesheet">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/admin_header.php'; 
?>    <div class="container-fluid">
        <div class="row">
            <?php require_once __DIR__ . '/../includes/admin_sidebar.php'; 
?>            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Notification Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/notifications/" class="btn btn-sm btn-outline-secondary">
                            Back to Notifications
                        </a>
                    </div>
                </div>

                <?php if ($notification): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><?= htmlspecialchars($notification['title']) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Sent:</strong> 
                                <?= date('M j, Y g:i a', strtotime($notification['created_at'])) 
?>                            </div>
                            <div class="mb-3">
                                <strong>To:</strong> 
                                <?= htmlspecialchars($notification['user_name']) ?>
                                (<?= htmlspecialchars($notification['tenant_name']) ?>)
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> 
                                <?= $notification['read_at'] ? 'Read' : 'Unread' 
?>                            </div>
                            <div class="notification-content">
                                <?= nl2br(htmlspecialchars($notification['content'])) 
?>                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        Notification not found.
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="/admin/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
