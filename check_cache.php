<?php
// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared\n";
}

// Check which file is being loaded
$formPath = '/var/www/html/cms/app/views/admin/articles/form.php';
echo "File exists: " . (file_exists($formPath) ? "YES" : "NO") . "\n";
echo "File size: " . filesize($formPath) . " bytes\n";
echo "File modified: " . date('Y-m-d H:i:s', filemtime($formPath)) . "\n";

// Check for media-tab in file
$content = file_get_contents($formPath);
$count = substr_count($content, 'media-tab');
echo "media-tab occurrences: $count\n";

// Check for Stock Photos
$stockCount = substr_count($content, 'Stock Photos');
echo "Stock Photos occurrences: $stockCount\n";
