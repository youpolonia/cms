<?php
// Content List View
require_once __DIR__ . '/../../../includes/security.php';
require_once __DIR__ . '/../../../core/csrf.php';

$title = 'Content Management';
$contents = []; // Will be populated by controller
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | Admin</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="admin-content">
    <?php require_once __DIR__ . '/../partials/header.php'; ?>
    <main class="container">
        <h1><?= htmlspecialchars($title) ?></h1>
        <div class="content-actions">
            <a href="/admin/content/create" class="btn btn-primary">Create New</a>
        </div>

        <?php if (!empty($contents)): ?>
            <table class="content-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contents as $content): ?>
                        <tr>
                            <td><?= htmlspecialchars($content['id']) ?></td>
                            <td><?= htmlspecialchars($content['title']) ?></td>
                            <td><?= htmlspecialchars($content['type']) ?></td>
                            <td><?= htmlspecialchars($content['status']) ?></td>
                            <td class="actions">
                                <a href="/admin/content/edit/<?= $content['id'] ?>" class="btn btn-sm btn-edit">Edit</a>
                                <a href="/admin/content/preview/<?= $content['id'] ?>" class="btn btn-sm btn-preview">Preview</a>
                                <form method="POST" action="/admin/content/delete" class="inline-form">
                                    <input type="hidden" name="id" value="<?= $content['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token('content_delete') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="
return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No content found.</div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
