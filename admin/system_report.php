<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

$root = dirname(__DIR__);
$paths = [
  'uploads' => $root . '/uploads',
  'themes' => $root . '/themes',
  'logs' => $root . '/logs',
  'n8n_workflows' => $root . '/n8n/workflows',
];

$modules = [
  '/admin/dashboard.php',
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

function exist_map(array $list): array {
  $out = [];
  foreach ($list as $p) { $out[$p] = file_exists($_SERVER['DOCUMENT_ROOT'] . $p) ? 'EXIST' : 'MISSING'; }
  return $out;
}

$mods = exist_map($modules);
$dirs = [];
foreach ($paths as $k=>$p) { $dirs[$k] = is_dir($p) ? 'EXIST' : 'MISSING'; }

require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('System Report');
echo '<h2>Runtime</h2>';
echo '<ul>';
echo '<li>DEV_MODE: ' . (defined('DEV_MODE') && DEV_MODE === true ? 'true' : 'false') . '</li>';
echo '<li>PHP: ' . PHP_VERSION . '</li>';
echo '<li>Server: ' . htmlspecialchars(PHP_SAPI, ENT_QUOTES, 'UTF-8') . '</li>';
echo '</ul>';

echo '<h2>Key Directories</h2><ul>';
foreach ($dirs as $k=>$v) { echo '<li>' . htmlspecialchars($k, ENT_QUOTES, 'UTF-8') . ': ' . $v . '</li>'; }
echo '</ul>';

echo '<h2>Modules</h2><ul>';
ksort($mods);
foreach ($mods as $p=>$st) {
  echo '<li>' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . ': ' . $st . '</li>';
}
echo '</ul>';

echo '<h2>n8n Workflows</h2>';
$wfDir = $paths['n8n_workflows'];
if (is_dir($wfDir)) {
  $items = array_values(array_filter(scandir($wfDir), fn($n)=>$n!=='.' && $n!=='..' && preg_match('/\\.json$/i',$n)));
  if ($items) {
    echo '<ul>';
    foreach ($items as $n) {
      echo '<li>' . htmlspecialchars($n, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul>';
  } else {
    echo '<p>No *.json workflows.</p>';
  }
} else {
  echo '<p>/n8n/workflows: MISSING</p>';
}
<?php admin_render_page_end();
