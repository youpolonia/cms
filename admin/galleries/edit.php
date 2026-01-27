<?php
/**
 * Gallery Manager - Edit
 * Edit existing gallery/album
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

$galleryId = (int)($_GET['id'] ?? 0);
if ($galleryId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch existing gallery
$stmt = $db->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->execute([$galleryId]);
$gallery = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$gallery) {
    header('Location: index.php');
    exit;
}

$errors = [];
$formData = [
    'title' => $gallery['title'],
    'slug' => $gallery['slug'],
    'description' => $gallery['description'] ?? ''
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

    // Check for duplicate slug (excluding current gallery)
    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM albums WHERE slug = ? AND id != ?");
        $stmt->execute([$formData['slug'], $galleryId]);
        if ($stmt->fetch()) {
            $errors[] = 'Another gallery with this slug already exists';
        }
    }

    // Update if no errors
    if (empty($errors)) {
        $stmt = $db->prepare("UPDATE albums SET title = ?, slug = ?, description = ?, updated_at = NOW() WHERE id = ?");
        $success = $stmt->execute([
            $formData['title'],
            $formData['slug'],
            $formData['description'],
            $galleryId
        ]);

        if ($success) {
            header('Location: index.php?success=updated');
            exit;
        } else {
            $errors[] = 'Failed to update gallery';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gallery</title>
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
</head>
<body>
    <div class="container">
        <h1>Edit Gallery: <?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></h1>

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
                       value="<?= htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="slug">Slug *</label>
                <input type="text" id="slug" name="slug" required
                       pattern="[a-z0-9-]+"
                       value="<?= htmlspecialchars($formData['slug'], ENT_QUOTES, 'UTF-8') ?>">
                <small>Lowercase letters, numbers, and hyphens only</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($formData['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <small>Optional description for this gallery</small>
            </div>

            <div class="actions">
                <button type="submit" class="btn btn-success">Update Gallery</button>
                <a href="index.php" class="btn btn-primary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
