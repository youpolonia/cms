<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/../../../core/csrf.php';
csrf_boot();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
}
?>
// Content Creation Form
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../core/csrf.php';

$title = 'Create New Content';
$contentTypes = []; // Will be populated by controller
$errors = []; // Will contain validation errors
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | Admin</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="admin-content">
    <?php require_once __DIR__ . '/../partials/header.php'; 
?>    <main class="container">
        <h1><?= htmlspecialchars($title) ?></h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach;  ?>
                </ul>
            </div>
        <?php endif;  ?>
        <form method="POST" action="/admin/content/store" class="content-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token('content_create') ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control"
 required>
?>            </div>

            <div class="form-group">
                <label for="type">Content Type</label>
                <select id="type" name="type" class="form-control"
 required>
                    <option value="">Select a type</option>
                    <?php foreach ($contentTypes as $type): ?>                        <option value="<?= htmlspecialchars($type['id']) ?>">
                            <?= htmlspecialchars($type['name'])  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" class="form-control" rows="10"
 required></textarea>
?>            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control"
 required>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                </select>
?>            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="/admin/content" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php';
?></body>
</html>
