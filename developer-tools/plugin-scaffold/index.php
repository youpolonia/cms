<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../../core/constants.php';
require_once __DIR__ . '/pluginscaffold.php';
require_once __DIR__ . '/../securitymiddleware.php';

$security = new DeveloperToolsSecurity();
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $security->checkAccess()) {
    try {
        $scaffold = new PluginScaffold();
        $config = [
            'name' => $_POST['name'] ?? '',
            'version' => $_POST['version'] ?? '1.0.0',
            'author' => $_POST['author'] ?? '',
            'description' => $_POST['description'] ?? ''
        ];
        
        if ($scaffold->generate($config)) {
            $success = true;
        }
    } catch (Exception $e) {
        $error = $security->sanitizeOutput($e->getMessage());
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plugin Scaffold Generator</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        .error { color: #dc3545; margin-top: 10px; }
        .success { color: #28a745; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Plugin Scaffold Generator</h1>
    
    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="success">Plugin scaffold created successfully!</div>
    <?php endif; ?>
    <form method="post">
<?= csrf_field(); ?>
        <div class="form-group">
            <label for="name">Plugin Name*</label>
            <input type="text" id="name" name="name"
 required 
                   placeholder="my-plugin-name (lowercase, hyphens)">
?>        </div>
        
        <div class="form-group">
            <label for="version">Version*</label>
            <input type="text" id="version" name="version"
 required 
                   value="1.0.0" placeholder="1.0.0">
?>        </div>
        
        <div class="form-group">
            <label for="author">Author*</label>
            <input type="text" id="author" name="author"
 required 
                   placeholder="Your Name">
?>        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" 
                      placeholder="Brief plugin description"></textarea>
        </div>
        
        <button type="submit">Generate Plugin</button>
    </form>
</body>
</html>
