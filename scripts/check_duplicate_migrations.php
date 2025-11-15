<?php

$migrationDir = __DIR__ . '/../database/migrations';
$files = glob("$migrationDir/*.php");
$tables = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/Schema::create\(\'([^\']+)\'/', $content, $matches)) {
        $table = $matches[1];
        if (isset($tables[$table])) {
            $tables[$table][] = basename($file);
        } else {
            $tables[$table] = [basename($file)];
        }
    }
}

$hasDuplicates = false;
foreach ($tables as $table => $migrations) {
    if (count($migrations) > 1) {
        echo "Duplicate table creation found for '$table' in:\n";
        echo "  - " . implode("\n  - ", $migrations) . "\n\n";
        $hasDuplicates = true;
    }
}

if ($hasDuplicates) {
    exit(1);
}

echo "No duplicate table creations found\n";
exit(0);
