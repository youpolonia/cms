<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

header('Content-Type: text/plain');
echo "=== SESSION DEBUG ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Status: " . session_status() . " (2=active)\n\n";

echo "=== COOKIE PARAMS ===\n";
$params = session_get_cookie_params();
foreach ($params as $k => $v) {
    echo "$k: " . var_export($v, true) . "\n";
}

echo "\n=== HTTPS Detection ===\n";
echo "HTTPS server var: " . ($_SERVER['HTTPS'] ?? 'not set') . "\n";
echo "X-Forwarded-Proto: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'not set') . "\n";
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
      || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
echo "Detected as HTTPS: " . ($https ? 'YES' : 'NO') . "\n";

echo "\n=== SESSION CONTENTS ===\n";
print_r($_SESSION);

echo "\n=== COOKIE CONTENTS ===\n";
print_r($_COOKIE);

echo "\n=== REQUEST HEADERS ===\n";
if (function_exists('getallheaders')) {
    foreach (getallheaders() as $name => $value) {
        if (stripos($name, 'cookie') !== false || stripos($name, 'host') !== false) {
            echo "$name: $value\n";
        }
    }
} else {
    echo "(getallheaders not available - CLI mode)\n";
}

echo "\n=== SERVER VARS ===\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'not set') . "\n";
echo "HTTP_COOKIE: " . ($_SERVER['HTTP_COOKIE'] ?? 'not set') . "\n";
