<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit;
}
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/includes/systemalert.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

csrf_validate_or_403();

$id = isset($_POST['alert_id']) ? (int)$_POST['alert_id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid alert id.';
    exit;
}

SystemAlert::resolve_alert($id);

header('Location: /admin/alerts/index.php', true, 303);
exit;
