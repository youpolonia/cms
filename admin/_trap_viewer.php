<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

// Admin-only trap for diagnosing HTTP 500 in migration_log_viewer.php
// NOT LARAVEL. No CLI assumptions. Plain-text diagnostics.

header('Content-Type: text/plain; charset=utf-8');

// 1) Max error visibility
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('log_errors', '0');

// 2) Turn warnings/notices into exceptions
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) { return false; }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// 3) Paths
$ADMIN_DIR = __DIR__;
$ROOT_DIR  = dirname(__DIR__);
$VIEWER    = $ADMIN_DIR . '/migration_log_viewer.php';
$AUTH      = $ADMIN_DIR . '/includes/auth.php';
$CSRF      = $ADMIN_DIR . '/includes/csrf.php';
$SEC       = $ADMIN_DIR . '/includes/security.php';
$VAL       = $ADMIN_DIR . '/includes/validation_helpers.php';
$LOG_FILE  = $ROOT_DIR . '/includes/migrations/migrations_log.json';

// 4) Require auth (fatal here is still informative)
if (!is_file($AUTH)) {
    echo "FATAL: admin/includes/auth.php not found at: {$AUTH}\n";
    echo "ADMIN_DIR={$ADMIN_DIR}\nROOT_DIR={$ROOT_DIR}\n";
    exit(1);
}
require_once __DIR__ . '/includes/auth.php';

// 5) CSRF helpers may or may not exist; this is just for completeness
$csrf_source = null;
if (is_file($CSRF)) { require_once __DIR__ . '/includes/csrf.php'; $csrf_source = 'csrf.php'; }
elseif (is_file($SEC)) { require_once __DIR__ . '/includes/security.php'; $csrf_source = 'security.php'; }
elseif (is_file($VAL)) { require_once __DIR__ . '/includes/validation_helpers.php'; $csrf_source = 'validation_helpers.php'; }
else { $csrf_source = '(none)'; }

// 6) Render viewer with trapping
try {
    ob_start();
    require_once __DIR__ . '/migration_log_viewer.php';
    $html = (string)ob_get_clean();

    echo "OK: rendered viewer\n";
    echo "PHP: " . PHP_VERSION . "\n";
    echo "ADMIN_DIR: {$ADMIN_DIR}\nROOT_DIR: {$ROOT_DIR}\nVIEWER: {$VIEWER}\n";
    echo "CSRF source: {$csrf_source}\n";
    echo "LOG_FILE: {$LOG_FILE} (exists=" . (is_file($LOG_FILE)?'yes':'no') . ", readable=" . (is_readable($LOG_FILE)?'yes':'no') . ")\n";
    echo "\n--- HTML head (first 400 chars) ---\n";
    echo substr($html, 0, 400);
    echo "\n--- end ---\n";
} catch (Throwable $e) {
    if (ob_get_length()) { ob_end_clean(); }
    echo "ERROR CLASS: " . get_class($e) . "\n";
    echo "MESSAGE    : " . $e->getMessage() . "\n";
    echo "FILE       : " . $e->getFile() . "\n";
    echo "LINE       : " . $e->getLine() . "\n";
    echo "PHP        : " . PHP_VERSION . "\n";
    echo "ADMIN_DIR  : {$ADMIN_DIR}\nROOT_DIR: {$ROOT_DIR}\n";
    echo "VIEWER     : {$VIEWER} (exists=" . (is_file($VIEWER)?'yes':'no') . ", readable=" . (is_readable($VIEWER)?'yes':'no') . ")\n";
    echo "AUTH       : {$AUTH} (exists=" . (is_file($AUTH)?'yes':'no') . ", readable=" . (is_readable($AUTH)?'yes':'no') . ")\n";
    echo "CSRF       : {$CSRF} (exists=" . (is_file($CSRF)?'yes':'no') . ")\n";
    echo "SECURITY   : {$SEC} (exists=" . (is_file($SEC)?'yes':'no') . ")\n";
    echo "VALIDATION : {$VAL} (exists=" . (is_file($VAL)?'yes':'no') . ")\n";
    echo "LOG_FILE   : {$LOG_FILE} (exists=" . (is_file($LOG_FILE)?'yes':'no') . ", readable=" . (is_readable($LOG_FILE)?'yes':'no') . ")\n";
    $last = error_get_last();
    if ($last) {
        echo "LAST ERROR : [" . ($last['type'] ?? '?') . '] ' . ($last['message'] ?? '') . " @ " . ($last['file'] ?? '') . ':' . ($last['line'] ?? '') . "\n";
    }
    echo "\nTRACE (top 10):\n";
    $i = 0;
    foreach ($e->getTrace() as $t) {
        if ($i++ >= 10) break;
        $fn = $t['function'] ?? '';
        $fl = $t['file'] ?? '';
        $ln = $t['line'] ?? '';
        echo "  #{$i} {$fn} @ {$fl}:{$ln}\n";
    }
    exit(1);
}
