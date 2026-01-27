<?php
// Restore original template-edit.php
$src = '/mnt/user-data/outputs/template-edit-original.php';
$dst = '/var/www/html/cms/app/views/admin/theme-builder/template-edit.php';

// Backup current
copy($dst, $dst . '.before-restore-' . date('His'));

// Read from outputs
$content = file_get_contents($src);
if ($content === false) {
    echo "ERROR: Cannot read source\n";
    exit(1);
}

// Write
if (file_put_contents($dst, $content) === false) {
    echo "ERROR: Cannot write\n";
    exit(1);
}

echo "RESTORED: " . strlen($content) . " bytes\n";
echo "Lines: " . substr_count($content, "\n") . "\n";
