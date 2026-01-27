<?php
/**
 * Admin Email Queue - Retry Endpoint
 *
 * Resets a failed/pending email to retry by marking it as 'pending' with zero attempts.
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
if (!ai_rate_limit_allow('email_queue_retry', 10, 300)) {
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

// Step 11: Fetch email_queue row
try {
    $stmt = $db->prepare('SELECT id, status, attempts, last_error FROM email_queue WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $email = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$email) {
        http_response_code(404);
        echo 'Email not found';
        exit;
    }

    // Step 12: Status logic
    if ($email['status'] === 'sent') {
        // Do not retry sent emails
        header('Location: /admin/email-queue/?retry=not_allowed', true, 303);
        exit;
    }

    // Step 13: DESCRIBE-based safe update
    $descStmt = $db->query('DESCRIBE email_queue');
    $columns = $descStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    $setClauses = [];
    $params = [];

    if (in_array('status', $columns, true)) {
        $setClauses[] = 'status = ?';
        $params[] = 'pending';
    }

    if (in_array('attempts', $columns, true)) {
        $setClauses[] = 'attempts = ?';
        $params[] = 0;
    }

    if (in_array('last_error', $columns, true)) {
        $setClauses[] = 'last_error = ?';
        $params[] = null;
    }

    if (in_array('updated_at', $columns, true)) {
        $setClauses[] = 'updated_at = ?';
        $params[] = date('Y-m-d H:i:s');
    }

    if (count($setClauses) > 0) {
        $params[] = $id;
        $sql = 'UPDATE email_queue SET ' . implode(', ', $setClauses) . ' WHERE id = ?';
        $updateStmt = $db->prepare($sql);
        $updateStmt->execute($params);
    }

    // Step 14: Redirect on success
    header('Location: /admin/email-queue/?retried=1', true, 303);
    exit;

} catch (Exception $e) {
    error_log('Email Queue Retry Error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal Server Error';
    exit;
}
