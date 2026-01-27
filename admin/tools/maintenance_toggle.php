<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

require_once CMS_ROOT . '/core/maintenance_gate.php';

header('Cache-Control: no-store');
$flag = CMS_ROOT . '/config/maintenance.flag';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'POST') {
  csrf_validate_or_403();
  $action = $_POST['action'] ?? '';
  if ($action === 'enable') {
    @mkdir(CMS_ROOT . '/config', 0775, true);
    $ok = @file_put_contents($flag, "ON\n") !== false;
    echo $ok ? "ENABLED" : "FAILED";
    exit;
  } elseif ($action === 'disable') {
    $ok = @unlink($flag);
    echo $ok ? "DISABLED" : (is_file($flag) ? "FAILED" : "DISABLED");
    exit;
  }
  http_response_code(400); exit('Bad action');
}

$enabled = is_file($flag);
?><!DOCTYPE html><meta charset="utf-8">
<title>Maintenance Toggle</title>
<body style="font-family:system-ui;max-width:480px;margin:40px auto">
<h1>Maintenance: <?= $enabled ? 'ENABLED' : 'DISABLED' ?></h1>
<p>Your IP: <code><?= htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></p>
<form method="post">
  <?php csrf_field(); ?>
  <?php if ($enabled): ?>
    <button name="action" value="disable">Disable</button>
  <?php else: ?>
    <button name="action" value="enable">Enable</button>
  <?php endif; ?>
</form>
<p><small>To access while enabled, add your IP to MAINTENANCE_ALLOW_IPS in root/config.php.</small></p>
</body>
