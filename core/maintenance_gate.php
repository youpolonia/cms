<?php
// core/maintenance_gate.php — global maintenance gate (no frameworks)
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__)); }

$flagNew    = CMS_ROOT . '/config/maintenance.flag';
$flagLegacy = CMS_ROOT . '/maintenance.flag';

// Optional: allowlist from config.php: define('MAINTENANCE_ALLOW_IPS', ['127.0.0.1'])
$allowIps = (defined('MAINTENANCE_ALLOW_IPS') && is_array(MAINTENANCE_ALLOW_IPS)) ? MAINTENANCE_ALLOW_IPS : [];
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';

$enabled = (defined('MAINTENANCE_MODE') && MAINTENANCE_MODE === true) || file_exists($flagNew) || file_exists($flagLegacy);

if ($enabled && !in_array($clientIp, $allowIps, true)) {
    http_response_code(503);
    header('Retry-After: 3600');
    $tpl = CMS_ROOT . '/public/maintenance.php';
    if (is_file($tpl)) { require_once $tpl; } else { echo "Service temporarily unavailable (maintenance)."; }
    exit;
}
