<?php
/**
 * Admin Email Queue - Delete Endpoint
 *
 * Permanently removes an email from the queue.
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

// Rate limiting helper
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

// Step 7: Method guard - POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo 'Method Not Allowed';
    exit;
}

// Step 8: CSRF validation
csrf_validate_or_403();

// Step 9: Rate limiting
if (!ai_rate_limit_allow('email_queue_delete', 10, 300)) {
    http_response_code(429);
    echo 'Too Many Requests';
    exit;
}

// Step 10: Input validation
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id < 1) {
    http_response_code(400);
    echo 'Invalid email id';
    exit;
}

// Step 11: Existence check
try {
    $stmt = $db->prepare('SELECT id FROM email_queue WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$email) {
        http_response_code(404);
        echo 'Email not found';
        exit;
    }

    // Step 12: Delete
    $deleteStmt = $db->prepare('DELETE FROM email_queue WHERE id = ?');
    $deleteStmt->execute([$id]);

    // Step 13: Redirect on success
    header('Location: /admin/email-queue/?deleted=1', true, 303);
    exit;

} catch (Exception $e) {
    error_log('Email Queue Delete Error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal Server Error';
    exit;
}
