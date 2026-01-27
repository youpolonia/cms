<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('CMS_ROOT', dirname(__DIR__));
define('CMS_APP', CMS_ROOT . '/app');

require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
require_once CMS_ROOT . '/core/csrf.php';
require_once CMS_ROOT . '/app/helpers/functions.php';
require_once CMS_ROOT . '/core/theme-builder/init.php';

tb_init();

echo '<h1>Theme Builder Debug</h1>';

$modules = tb_get_all_modules();
echo '<h2>1. Modules from tb_get_all_modules()</h2>';
echo '<p>Count: ' . count($modules) . '</p>';
echo '<p>First 5 keys: ' . implode(', ', array_slice(array_keys($modules), 0, 5)) . '</p>';

$modulesJson = json_encode($modules, JSON_UNESCAPED_UNICODE);
echo '<h2>2. modulesJson length: ' . strlen($modulesJson) . ' bytes</h2>';

// Simulate what controller passes
$data = [
    'page' => ['id' => 7, 'title' => 'Test', 'slug' => 'test', 'status' => 'draft'],
    'pageId' => 7,
    'contentJson' => '{"sections":[]}',
    'modulesJson' => $modulesJson,
    'categoriesJson' => json_encode(tb_get_category_labels(), JSON_UNESCAPED_UNICODE),
    'revisions' => []
];

echo '<h2>3. Data array keys: ' . implode(', ', array_keys($data)) . '</h2>';

// Simulate extract like view() does
extract($data);

echo '<h2>4. After extract()</h2>';
echo '<p>isset($modulesJson): ' . (isset($modulesJson) ? 'YES ('.strlen($modulesJson).' bytes)' : 'NO') . '</p>';
echo '<p>isset($contentJson): ' . (isset($contentJson) ? 'YES' : 'NO') . '</p>';
echo '<p>isset($categoriesJson): ' . (isset($categoriesJson) ? 'YES' : 'NO') . '</p>';

// Now simulate what view HEADER does
echo '<h2>5. View header simulation</h2>';

// These are the lines from edit.php header:
$pageTitle_test = 'Test Page';
$pageId_test = (int)($page['id'] ?? $pageId ?? 0);

// Check the condition
echo '<p>Before condition - isset($modulesJson): ' . (isset($modulesJson) ? 'YES' : 'NO') . '</p>';
echo '<p>Before condition - empty($modulesJson): ' . (empty($modulesJson) ? 'YES' : 'NO') . '</p>';

if (!isset($modulesJson) || empty($modulesJson)) {
    echo '<p style="color:red">PROBLEM: Condition is TRUE - will overwrite!</p>';
    $modulesJson = json_encode($modules ?? [], JSON_UNESCAPED_UNICODE);
} else {
    echo '<p style="color:green">OK: Condition is FALSE - will keep existing value</p>';
}

echo '<h2>6. Final modulesJson length: ' . strlen($modulesJson) . '</h2>';
echo '<h2>7. First 200 chars:</h2>';
echo '<pre>' . htmlspecialchars(substr($modulesJson, 0, 200)) . '</pre>';

// Check actual file
echo '<h2>8. View file lines 8-24:</h2>';
$viewFile = CMS_APP . '/views/admin/theme-builder/edit.php';
if (file_exists($viewFile)) {
    $lines = file($viewFile);
    echo '<pre style="background:#1e1e2e;color:#cdd6f4;padding:10px">';
    for ($i = 7; $i < min(24, count($lines)); $i++) {
        echo htmlspecialchars(sprintf('%3d: %s', $i+1, $lines[$i]));
    }
    echo '</pre>';
} else {
    echo '<p style="color:red">View file not found!</p>';
}
