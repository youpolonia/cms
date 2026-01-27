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
// Content Edit Form
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../core/csrf.php';

$title = 'Edit Content';
$content = []; // Will be populated by controller
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
        <form method="POST" action="/admin/content/update" class="content-form">
            <input type="hidden" name="id" value="<?= $content['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token('content_update') ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" 
                       value="<?= htmlspecialchars($content['title'] ?? '') ?>"
 required>
?>            </div>

            <div class="form-group">
                <label for="type">Content Type</label>
                <select id="type" name="type" class="form-control"
 required>
                    <option value="">Select a type</option>
                    <?php foreach ($contentTypes as $type): ?>                        <option value="<?= htmlspecialchars($type['id']) ?>" 
                            <?= ($type['id'] === ($content['type_id'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($type['name'])  ?>
                        </option>
                    <?php endforeach;  ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <textarea id="content" name="content" class="form-control" rows="10"
 required>
                    <?= htmlspecialchars($content['content'] ?? '') 
?>                </textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control"
 required>
                    <option value="draft" <?= ($content['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= ($content['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= ($content['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/admin/content" class="btn btn-secondary">Cancel</a>
                <a href="/admin/content/preview/<?= $content['id'] ?>" class="btn btn-info">Preview</a>
            </div>
        </form>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php';
?></body>
</html>
