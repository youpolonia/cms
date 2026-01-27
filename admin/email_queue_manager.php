<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
if (!defined('EMAIL_QUEUE_MANAGER_INCLUDED')) { define('EMAIL_QUEUE_MANAGER_INCLUDED', true); }
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }
require_once __DIR__ . '/../core/csrf.php';

function handle_email_queue_action(array $request) {
    $action = isset($request['action']) ? (string)$request['action'] : 'status';
    if ($action === 'run') {
        require_once __DIR__ . '/../core/tasks/emailqueuetask.php';
        $ok = \EmailQueueTask::run();
        return json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_SLASHES);
    }
    if ($action === 'logs') {
        $path = CMS_ROOT . '/logs/email_queue.log';
        if (!is_file($path)) {
            return "";
        }
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if ($lines === false) {
            return "";
        }
        $tail = array_slice($lines, -100);
        return implode("\n", $tail) . "\n";
    }
    return "Email Queue Manager OK\n";
}

function display_email_queue_ui(): string {
    csrf_boot();

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
    ob_start();
    csrf_field();
    $token = ob_get_clean();
    return "
<pre>Email Queue Manager</pre>" . $token;
}
