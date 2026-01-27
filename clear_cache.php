<?php
/**
 * Clear all PHP caches
 * Visit: http://localhost/clear_cache.php
 */

// Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared<br>";
} else {
    echo "ℹ️ OPcache not available<br>";
}

// Clear realpath cache
clearstatcache(true);
echo "✅ Realpath cache cleared<br>";

// Check files
$formPath = '/var/www/html/cms/app/views/admin/articles/form.php';
echo "<br><strong>File check:</strong><br>";
echo "Modified: " . date('Y-m-d H:i:s', filemtime($formPath)) . "<br>";
echo "Size: " . filesize($formPath) . " bytes<br>";

$content = file_get_contents($formPath);
echo "Contains 'TEST MARKER': " . (strpos($content, 'TEST MARKER') !== false ? '✅ YES' : '❌ NO') . "<br>";
echo "Contains 'media-tab': " . (substr_count($content, 'media-tab')) . " times<br>";
echo "Contains 'Stock Photos': " . (substr_count($content, 'Stock Photos')) . " times<br>";

echo "<br><strong>Now try:</strong><br>";
echo "<a href='/admin/articles/4/edit'>Open Article Editor</a> (use Ctrl+Shift+R to hard refresh)";
