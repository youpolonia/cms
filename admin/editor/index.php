<?php
require_once __DIR__ . '/../../includes/core/auth.php';
require_once __DIR__ . '/../../includes/editor/blockmanager.php';

// Check authentication
if (!Auth::check()) {
    header('Location: /admin/login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI-Assisted Content Editor</title>
    <link rel="stylesheet" href="/admin/editor/editor.css">
</head>
<body>
    <div class="editor-container">
        <div class="block-palette">
            <!-- Block types will be loaded here -->
        </div>
        <div class="editing-canvas" id="editor">
            <!-- Content blocks will be rendered here -->
        </div>
        <div class="ai-tools-panel">
            <!-- AI tools will be loaded here -->
        </div>
    </div>

    <script src="/admin/editor/editor.js"></script>
</body>
</html>
