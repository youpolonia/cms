<?php
/**
 * Email Test Endpoint (DEV MODE ONLY)
 * Sends test email to verify email configuration
 */

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';

// DEV_MODE gate
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}

// Session and auth
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

// CSRF protection
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();

// Require admin role
require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

// Load email sender
require_once CMS_ROOT . '/core/email.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Validate CSRF
csrf_validate_or_403();

// Set JSON response header
header('Content-Type: application/json; charset=UTF-8');

// Get recipient from POST
$to = trim($_POST['to'] ?? '');

if ($to === '') {
    echo json_encode(['ok' => false, 'error' => 'Recipient email required']);
    exit;
}

// Build test message
$subject = 'CMS Email Test';
$body = "This is a test email sent on " . date('c') . "\n\n";
$body .= "If you received this message, your email configuration is working correctly.\n";

// Send email
$result = email_send([
    'to' => $to,
    'subject' => $subject,
    'body' => $body
]);

// Return result
echo json_encode(['ok' => $result]);
