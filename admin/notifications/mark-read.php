<?php
/**
 * Admin Notifications - Mark as Read Action
 * POST-only endpoint to mark a single database notification as read.
 */

// Step 1: Bootstrap
require_once __DIR__ . '/../../config.php';

// Step 2: DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Step 3: Session
require_once __DIR__ . '/../../core/session_boot.php';
cms_session_start('admin');

// Step 4: Permissions
require_once __DIR__ . '/../includes/permissions.php';
cms_require_admin_role();

// Step 5: CSRF
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot();

// Step 6: Database
require_once __DIR__ . '/../../core/database.php';
$db = \core\Database::connection();

// Step 7: Rate limiting helper
if (!function_exists('ai_rate_limit_allow')) {
    function ai_rate_limit_allow(string $action, int $limit, int $window): bool {
        $key = 'rate_limit_' . $action . '_' . ($_SESSION['admin_id'] ?? session_id());
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }

        $data = $_SESSION[$key];
        if ($now - $data['start'] > $window) {
            $_SESSION[$key] = ['count' => 1, 'start' => $now];
            return true;
        }

        if ($data['count'] >= $limit) {
            return false;
        }

        $_SESSION[$key]['count']++;
        return true;
    }
}

// Step 8: Method guard - POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit;
}

// Step 9: CSRF validation
csrf_validate_or_403();

// Step 10: Rate limiting (10 actions per 5 minutes)
if (!ai_rate_limit_allow('notifications_mark_read', 10, 300)) {
    header('Location: /admin/notifications/?error=rate_limit', true, 303);
    exit;
}

// Step 11: Input validation
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id < 1) {
    http_response_code(400);
    echo 'Invalid notification id';
    exit;
}

// Step 12: Check if notifications table exists
try {
    $checkStmt = $db->query("SHOW TABLES LIKE 'notifications'");
    if ($checkStmt->rowCount() === 0) {
        header('Location: /admin/notifications/?error=no_table', true, 303);
        exit;
    }
} catch (PDOException $e) {
    error_log('Notifications table check failed: ' . $e->getMessage());
    header('Location: /admin/notifications/?error=no_table', true, 303);
    exit;
}

// Step 13: Discover table columns using DESCRIBE
try {
    $descStmt = $db->query('DESCRIBE notifications');
    $columns = $descStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (PDOException $e) {
    error_log('DESCRIBE notifications failed: ' . $e->getMessage());
    header('Location: /admin/notifications/?error=db', true, 303);
    exit;
}

// Step 14: Verify required columns exist
if (!in_array('id', $columns, true)) {
    header('Location: /admin/notifications/?error=db', true, 303);
    exit;
}

if (!in_array('is_read', $columns, true)) {
    header('Location: /admin/notifications/?error=no_is_read', true, 303);
    exit;
}

// Step 15: Fetch the notification (read-before-write)
try {
    $stmt = $db->prepare('SELECT * FROM notifications WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $notification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$notification) {
        http_response_code(404);
        echo 'Notification not found';
        exit;
    }

    // Step 16: Build schema-aware UPDATE
    $setParts = ['is_read = 1'];
    $params = [':id' => $id];

    // Include updated_at if column exists
    if (in_array('updated_at', $columns, true)) {
        $setParts[] = 'updated_at = NOW()';
    }

    // Step 17: Execute UPDATE
    $sql = 'UPDATE notifications SET ' . implode(', ', $setParts) . ' WHERE id = :id';
    $updateStmt = $db->prepare($sql);
    $updateStmt->execute($params);

    // Step 18: Redirect on success
    header('Location: /admin/notifications/?marked=1', true, 303);
    exit;

} catch (PDOException $e) {
    error_log('Mark notification as read failed: ' . $e->getMessage());
    header('Location: /admin/notifications/?error=db', true, 303);
    exit;
}
