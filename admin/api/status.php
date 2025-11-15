<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();
header('Content-Type: application/json; charset=UTF-8');

$root = dirname(__DIR__, 2);
$paths = [
  'uploads' => $root . '/uploads',
  'themes' => $root . '/themes',
  'logs' => $root . '/logs',
  'n8n_workflows' => $root . '/n8n/workflows',
];
$modules = [
  '/admin/modules.php',
  '/admin/articles.php',
  '/admin/ai-content.php',
  '/admin/pages.php',
  '/admin/categories.php',
  '/admin/comments.php',
  '/admin/media.php',
  '/admin/galleries.php',
  '/admin/themes.php',
  '/admin/theme-builder.php',
  '/admin/migrations.php',
  '/admin/migration_manager.php',
  '/admin/scheduler.php',
  '/admin/email-queue.php',
  '/admin/backup.php',
  '/admin/maintenance.php',
  '/admin/extensions.php',
  '/admin/seo.php',
  '/admin/security.php',
  '/admin/automations.php',
  '/admin/logs.php',
  '/admin/users.php',
  '/admin/settings.php',
  '/admin/content.php',
];

$mods = [];
foreach ($modules as $p) { $mods[$p] = file_exists($_SERVER['DOCUMENT_ROOT'] . $p); }
$dirs = [];
foreach ($paths as $k=>$p) { $dirs[$k] = is_dir($p); }

echo json_encode([
  'ok' => true,
  'dev_mode' => (defined('DEV_MODE') && DEV_MODE === true),
  'php' => PHP_VERSION,
  'paths' => $dirs,
  'modules' => $mods,
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
