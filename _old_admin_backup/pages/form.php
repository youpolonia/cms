<?php
$isEdit = isset($page);
$title = $isEdit ? 'Edit Page' : 'Create Page';
$action = $isEdit ? "?action=update&id={$page['id']}" : "?action=store";

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="admin-container">
        <h1><?= $title ?></h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <form method="POST" action="<?= $action ?>">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required
                       value="<?= $isEdit ? htmlspecialchars($page['title']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug</label>
                <input type="text" id="slug" name="slug" required
                       value="<?= $isEdit ? htmlspecialchars($page['slug']) : '' ?>">
            </div>

            <div class="form-group">
                <label for="content">Content</label>
                <div id="content" class="content-editor" contenteditable="true">
                    <?= $isEdit ? htmlspecialchars($page['content']) : '' ?>
                </div>
                <textarea name="content" style="display:none" id="hidden-content"></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save</button>
                <a href="/admin/pages.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            document.getElementById('hidden-content').value = 
                document.getElementById('content').innerHTML;
        });
    </script>
</body>
</html>
