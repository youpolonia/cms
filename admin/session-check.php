<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

header('Content-Type: text/plain');

echo "=== SESSION DEBUG ===\n\n";
echo "Session Name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . session_status() . " (2=active)\n";
echo "Session File: /var/lib/php/sessions/sess_" . session_id() . "\n\n";

echo "=== COOKIES RECEIVED ===\n";
print_r($_COOKIE);

echo "\n=== SESSION DATA ===\n";
print_r($_SESSION);

echo "\n=== AUTH CHECK ===\n";
echo "admin_id: " . (isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : 'NOT SET') . "\n";
echo "admin_role: " . (isset($_SESSION['admin_role']) ? $_SESSION['admin_role'] : 'NOT SET') . "\n";
echo "admin_authenticated: " . (isset($_SESSION['admin_authenticated']) ? ($_SESSION['admin_authenticated'] ? 'true' : 'false') : 'NOT SET') . "\n";

echo "\n=== WOULD PASS AUTH? ===\n";
$wouldPass = isset($_SESSION['admin_id']) && isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin';
echo $wouldPass ? "YES - would be granted access" : "NO - would be denied";
echo "\n";
