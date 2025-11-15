<?php
header('Content-Type: application/json');

$templateDir = __DIR__ . '/../data/workflow_templates/';
$templates = [];

// Scan directory for JSON files
foreach (glob($templateDir . '*.json') as $file) {
    $content = file_get_contents($file);
    $template = json_decode($content, true);
    if ($template) {
        $templates[] = $template;
    }
}

echo json_encode($templates);
