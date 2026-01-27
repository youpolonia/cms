<?php
// Content Preview View
require_once __DIR__ . '/../../../includes/security.php';

$title = 'Preview Content';
$content = []; // Will be populated by controller
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
        <div class="preview-header">
            <h1><?= htmlspecialchars($content['title'] ?? '') ?></h1>
            <div class="content-meta">
                <span class="content-type"><?= htmlspecialchars($content['type'] ?? '') ?></span>
                <span class="content-status"><?= htmlspecialchars($content['status'] ?? '') ?></span>
                <span class="content-date"><?= date('M j, Y', strtotime($content['updated_at'] ?? '')) ?></span>
            </div>
        </div>

        <div class="preview-actions">
            <a href="/admin/content/edit/<?= $content['id'] ?>" class="btn btn-primary">Edit</a>
            <a href="/admin/content" class="btn btn-secondary">Back to List</a>
        </div>

        <article class="content-preview">
            <?= $content['content'] ?? '' 
?>        </article>

        <div class="preview-footer">
            <a href="/admin/content/edit/<?= $content['id'] ?>" class="btn btn-primary">Edit</a>
            <a href="/admin/content" class="btn btn-secondary">Back to List</a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; 
?></body>
</html>
