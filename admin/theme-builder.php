<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/csrf.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
csrf_boot();

$name = isset($_GET['name']) ? (string)$_GET['name'] : '';

// If no theme name provided, show theme selection
if ($name === '') {
    require_once __DIR__ . '/includes/admin_layout.php';
    admin_render_page_start('Theme Builder - Select Theme');
    echo '<p><a href="/admin/themes.php">← Back to themes</a></p>';
    echo '<h2>Select a Theme to Edit</h2>';

    $themesBaseDir = dirname(__DIR__) . '/themes';
    if (is_dir($themesBaseDir)) {
        $allThemes = scandir($themesBaseDir);
        $availableThemes = [];
        foreach ($allThemes as $item) {
            if ($item === '.' || $item === '..') continue;
            $itemPath = $themesBaseDir . '/' . $item;
            if (is_dir($itemPath)) {
                $availableThemes[] = $item;
            }
        }

        if (!empty($availableThemes)) {
            sort($availableThemes, SORT_NATURAL | SORT_FLAG_CASE);
            echo '<ul style="list-style:none;padding:0">';
            foreach ($availableThemes as $themeName) {
                $url = '/admin/theme-builder.php?name=' . urlencode($themeName);
                echo '<li style="margin:12px 0;padding:12px;border:1px solid #ddd;border-radius:4px">';
                echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="font-size:1.1rem;font-weight:500">';
                echo htmlspecialchars($themeName, ENT_QUOTES, 'UTF-8');
                echo '</a>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>No themes found.</p>';
        }
    } else {
        echo '<p>Themes directory not found.</p>';
    }

    admin_render_page_end();
    exit;
}

// Validate theme name
if (!preg_match('/^[A-Za-z0-9._-]+$/', $name)) {
    http_response_code(400);
    echo 'Invalid theme name';
    exit;
}

$themeDir = dirname(__DIR__) . '/themes/' . $name;
if (!is_dir($themeDir)) {
    http_response_code(404);
    echo 'Theme not found';
    exit;
}

require_once __DIR__ . '/includes/admin_layout.php';
admin_render_page_start('Theme Builder');
echo '<p><a href="/admin/themes.php">← Back to themes</a></p>';
echo '<h2>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</h2>';

$all = scandir($themeDir);
$templates = [];
$assets = [];
foreach ($all as $f) {
    if ($f === '.' || $f === '..') { continue; }
    $p = $themeDir . '/' . $f;
    if (!is_file($p)) { continue; }
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    if (in_array($ext, ['php','html'], true)) { $templates[] = $f; }
    elseif (in_array($ext, ['css','js'], true)) { $assets[] = $f; }
}
sort($templates, SORT_NATURAL | SORT_FLAG_CASE);
sort($assets, SORT_NATURAL | SORT_FLAG_CASE);

echo '<h3>Templates</h3>';
if (empty($templates)) {
    echo '<p>None</p>';
} else {
    echo '<ul>';
    foreach ($templates as $tpl) {
        $p = $themeDir . '/' . $tpl;
        $raw = @file_get_contents($p);
        $hasContentSlot = is_string($raw) && preg_match('/\\{\\{\\s*content\\s*\\}\\}/i', $raw);
        $hasHeadSlot = is_string($raw) && preg_match('/\\{\\{\\s*head\\s*\\}\\}/i', $raw);
        $flags = [];
        if ($hasContentSlot) { $flags[] = 'content-slot'; }
        if ($hasHeadSlot) { $flags[] = 'head-slot'; }
        echo '<li>' . htmlspecialchars($tpl, ENT_QUOTES, 'UTF-8') . (empty($flags) ? '' : ' [' . htmlspecialchars(implode(', ', $flags), ENT_QUOTES, 'UTF-8') . ']') . '</li>';
    }
    echo '</ul>';
}

echo '<h3>Assets</h3>';
if (empty($assets)) {
    echo '<p>None</p>';
} else {
    echo '<ul>';
    foreach ($assets as $a) {
        echo '<li>' . htmlspecialchars($a, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    echo '</ul>';
}

echo '<h3>Preview</h3>';
$shot = null;
foreach (['screenshot.png','screenshot.jpg','screenshot.jpeg','screenshot.webp','preview.png'] as $sc) {
    if (is_file($themeDir . '/' . $sc)) { $shot = '/themes/' . rawurlencode($name) . '/' . rawurlencode($sc); break; }
}
if ($shot) {
    echo '<img src="' . $shot . '" alt="" style="max-width:640px;height:auto;border:1px solid #ddd">';
} else {
    echo '<p>No screenshot found.</p>';
}

admin_render_page_end();
