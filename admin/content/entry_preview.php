<?php
require_once __DIR__ . '/../../admin/includes/auth.php';

// Check permissions
if (!has_permission('content_view')) {
    header('Location: /admin/dashboard.php');
    exit;
}

$entry_id = $_GET['id'] ?? null;
if (!$entry_id) {
    header('Location: /admin/content/entries.php');
    exit;
}

$entry = ContentEntry::getById($entry_id);
$content_type = ContentType::getById($entry->content_type_id);

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Preview: <?= htmlspecialchars($entry->title) ?></title>
    <link rel="stylesheet" href="/admin/assets/css/content.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../admin/includes/header.php'; 
?>    <main class="content-container">
        <h1>Preview: <?= htmlspecialchars($entry->title) ?></h1>
        <p class="content-meta">
            <strong>Type:</strong> <?= htmlspecialchars($content_type->name) ?> |
            <strong>Status:</strong> <?= ucfirst($entry->status) ?> |
            <strong>Last Modified:</strong> <?= date('M j, Y H:i', strtotime($entry->updated_at)) 
?>        </p>
        
        <div class="preview-content">
            <?php foreach ($content_type->fields as $field): ?>
                <div class="preview-field">
                    <h3><?= htmlspecialchars($field['label']) ?></h3>
                    <?php if ($field['type'] === 'textarea'): ?>
                        <div class="preview-textarea"><?= nl2br(htmlspecialchars($entry->field_values[$field['name']] ?? '')) ?></div>
                    <?php else: ?>
                        <p><?= htmlspecialchars($entry->field_values[$field['name']] ?? '') ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="preview-actions">
            <a href="/admin/content/entry_edit.php?id=<?= $entry->id ?>" class="button">Edit</a>
            <a href="/admin/content/entries.php" class="button secondary">Back to List</a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../../admin/includes/footer.php';
</body>
</html>
