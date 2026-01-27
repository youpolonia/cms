<?php
// Temporary installer - delete after use
$source = __DIR__ . '/_ai_settings_new.php';
$dest = __DIR__ . '/admin/ai-settings.php';

if (!file_exists($source)) {
    die("Source file not found at: " . $source);
}

$content = file_get_contents($source);
if ($content === false) {
    die("Cannot read source");
}

$result = file_put_contents($dest, $content);
if ($result === false) {
    die("Cannot write to destination: " . $dest);
}

echo "Successfully installed ai-settings.php ($result bytes)";
// Clean up
unlink($source); // Delete source
unlink(__FILE__); // Self-delete
