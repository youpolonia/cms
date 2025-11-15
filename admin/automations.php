<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

$workflowsDir = dirname(__DIR__) . '/n8n/workflows';
$items = [];
if (is_dir($workflowsDir)) {
    $scan = scandir($workflowsDir);
    foreach ($scan as $name) {
        if ($name === '.' || $name === '..') { continue; }
        $path = $workflowsDir . '/' . $name;
        if (is_file($path) && preg_match('/^[A-Za-z0-9._-]+\\.json$/', $name)) {
            $items[] = [
                'name' => $name,
                'size' => filesize($path),
                'mtime' => filemtime($path),
            ];
        }
    }
}

usort($items, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

if (isset($_GET['view'])) {
    $file = (string)$_GET['view'];
    if (!preg_match('/^[A-Za-z0-9._-]+\\.json$/', $file)) {
        http_response_code(400); echo 'Invalid file name'; exit;
    }
    $path = $workflowsDir . '/' . $file;
    if (!is_file($path)) { http_response_code(404); echo 'Not found'; exit; }
    header('Content-Type: application/json; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    readfile($path);
    exit;
}

require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('Automations (n8n)');
if (empty($items)) {
    echo "<p>No workflows found in /n8n/workflows.</p>";
} else {
    echo "<ul>";
    foreach ($items as $wf) {
        $n = htmlspecialchars($wf['name'], ENT_QUOTES, 'UTF-8');
        $sz = (int)$wf['size'];
        $mt = date('Y-m-d H:i:s', (int)$wf['mtime']);
        echo "<li><a href=\"/admin/automations.php?view={$n}\">{$n}</a> ({$sz} bytes, {$mt})</li>";
    }
    echo "</ul>";
}
<?php admin_render_page_end();
