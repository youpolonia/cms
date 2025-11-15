<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../migration_manager.php';
header('Content-Type: text/plain; charset=UTF-8');
if (!defined('DEV_MODE') || DEV_MODE === false) {
    http_response_code(403);
    exit;
}
csrf_boot();
$out = handle_migration_action(['action' => 'preview_all']);
echo is_string($out) ? $out : '';
