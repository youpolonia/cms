<?php
require_once __DIR__ . '/../models/blogmanager.php';
require_once __DIR__ . '/../core/csrf.php';

csrf_validate_or_403();
$file = filter_input(INPUT_POST, 'file', FILTER_SANITIZE_STRING);
if (!$file || !file_exists($file)) {
    die('Invalid version file');
}

$data = json_decode(file_get_contents($file), true);
if (!$data) {
    die('Invalid version data');
}
?><!DOCTYPE html>
<html>
<head>
    <title>Version Preview: <?= htmlspecialchars($data['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .version-meta { background: #f5f5f5; padding: 15px; margin-bottom: 20px; }
        .version-content { line-height: 1.6; }
    </style>
</head>
<body>
    <div class="version-meta">
        <h1><?= htmlspecialchars($data['title']) ?></h1>
        <p>Saved: <?= date('Y-m-d H:i:s', filemtime($file)) ?></p>
        <p>Tags: <?= htmlspecialchars(implode(', ', $data['tags'])) ?></p>
    </div>
    <div class="version-content">
        <?= nl2br(htmlspecialchars($data['body'])) ?>
    </div>
<div style="margin-top: 20px;">
        <button onclick="window.close()">Close Preview</button>
    </div>
</body>
</html>
