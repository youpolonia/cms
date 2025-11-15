<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();
header('Content-Type: application/json; charset=UTF-8');

$modules = [
  ['Dashboard','/admin/dashboard.php'],
  ['Articles','/admin/articles.php'],
  ['AI Content Creator','/admin/ai-content.php'],
  ['AI SEO Assistant','/admin/ai-seo.php'],
  ['Pages','/admin/pages.php'],
  ['Categories & Tags','/admin/categories.php'],
  ['Comments','/admin/comments.php'],
  ['Media Library','/admin/media.php'],
  ['Galleries','/admin/galleries.php'],
  ['Themes','/admin/themes.php'],
  ['Theme Builder','/admin/theme-builder.php'],
  ['Migrations','/admin/migrations.php'],
  ['Migration Manager','/admin/migration_manager.php'],
  ['Scheduler','/admin/scheduler.php'],
  ['Email Queue','/admin/email-queue.php'],
  ['Backup','/admin/backup.php'],
  ['Maintenance','/admin/maintenance.php'],
  ['Extensions','/admin/extensions.php'],
  ['SEO','/admin/seo.php'],
  ['Security','/admin/security.php'],
  ['Automations (n8n)','/admin/automations.php'],
  ['Logs Viewer','/admin/logs.php'],
  ['Users','/admin/users.php'],
  ['Settings','/admin/settings.php'],
  ['Content (legacy)','/admin/content.php'],
  ['Admin Modules Hub','/admin/modules.php'],
  ['Menus','/admin/menus.php'],
  ['Widgets','/admin/widgets.php'],
  ['Search','/admin/search.php'],
  ['URL Manager','/admin/urls.php'],
];

usort($modules, fn($a,$b)=>strcmp($a[0], $b[0]));
echo json_encode([
  'status' => 'ok',
  'count' => count($modules),
  'modules' => array_map(fn($m)=>['label'=>$m[0],'href'=>$m[1]], $modules),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
