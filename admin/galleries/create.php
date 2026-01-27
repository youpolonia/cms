<?php
/**
 * Gallery Manager - Create
 * Create new gallery/album
 */

if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';

cms_session_start('admin');
csrf_boot('admin');

// RBAC: Require galleries.edit permission
if (!function_exists('cms_require_permission')) {
    function cms_require_permission(string $permission): void {
        if (!isset($_SESSION['user_permissions']) || !in_array($permission, $_SESSION['user_permissions'], true)) {
            http_response_code(403);
            exit('Permission denied: ' . htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'));
        }
    }
}
cms_require_permission('galleries.edit');

$db = \core\Database::connection();

$errors = [];
$formData = [
    'title' => '',
    'slug' => '',
    'description' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    // Collect form data
    $formData['title'] = trim($_POST['title'] ?? '');
    $formData['slug'] = trim($_POST['slug'] ?? '');
    $formData['description'] = trim($_POST['description'] ?? '');

    // Validation
    if ($formData['title'] === '') {
        $errors[] = 'Title is required';
    }

    if ($formData['slug'] === '') {
        $errors[] = 'Slug is required';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $formData['slug'])) {
        $errors[] = 'Slug must contain only lowercase letters, numbers, and hyphens';
    }

    // Check for duplicate slug
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM albums WHERE slug = ?");
        $stmt->execute([$formData['slug']]);
        if ($stmt->fetch()) {
            $errors[] = 'A gallery with this slug already exists';
        }
    }

    // Insert if no errors
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO albums (title, slug, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $success = $stmt->execute([
            $formData['title'],
            $formData['slug'],
            $formData['description']
        ]);

        if ($success) {
            header('Location: index.php?success=created');
            exit;
        } else {
            $errors[] = 'Failed to create gallery';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Gallery</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: inherit; }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-group small { display: block; margin-top: 5px; color: #666; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .actions { display: flex; gap: 10px; margin-top: 30px; }
    </style>
    <script>
        function generateSlug() {
            const title = document.getElementById('title').value;
            const slug = title
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Create Gallery</h1>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Errors:</strong>
                <ul style="margin-left: 20px; margin-top: 10px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?php csrf_field(); ?>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" required
                       value="<?= htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8') ?>"
                       onblur="generateSlug()">
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required
                       pattern="[a-z0-9-]+"
                       value="<?= htmlspecialchars($formData['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <small>Lowercase letters, numbers, and hyphens only. Auto-generated from title.</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <small>Optional description for this gallery</small>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-success">Create Gallery</button>
                <a href="index.php" class="btn btn-primary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
