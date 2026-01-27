<?php
/**
 * Gallery Manager - Delete
 * Delete gallery and all associated images
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

// Fetch gallery to verify existence
$stmt = $db->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->execute([$galleryId]);
$gallery = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$gallery) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    try {
        // Begin transaction
        $db->beginTransaction();

        // Delete associated images from database
        $stmt = $db->prepare("SELECT file_path FROM album_images WHERE album_id = ?");
        $stmt->execute([$galleryId]);
        $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Delete image files from filesystem
        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../' . $image['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete image records
        $stmt = $db->prepare("DELETE FROM album_images WHERE album_id = ?");
        $stmt->execute([$galleryId]);

        // Delete gallery directory
        $galleryDir = __DIR__ . '/../../uploads/galleries/' . $galleryId;
        if (is_dir($galleryDir)) {
            $files = array_diff(scandir($galleryDir), ['.', '..']);
            foreach ($files as $file) {
                @unlink($galleryDir . '/' . $file);
            }
            @rmdir($galleryDir);
        }

        // Delete gallery
        $stmt = $db->prepare("DELETE FROM albums WHERE id = ?");
        $stmt->execute([$galleryId]);

        // Commit transaction
        $db->commit();

        header('Location: index.php?success=deleted');
        exit;
    } catch (\Exception $e) {
        // Rollback on error
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $errors[] = 'Failed to delete gallery: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Gallery</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .actions { display: flex; gap: 10px; margin-top: 30px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info-box p { margin-bottom: 10px; }
        .info-box p:last-child { margin-bottom: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Delete Gallery</h1>

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

        <div class="alert alert-warning">
            <strong>Warning:</strong> This action cannot be undone!
        </div>

        <div class="info-box">
            <p><strong>Gallery:</strong> <?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Slug:</strong> <?= htmlspecialchars($gallery['slug'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($gallery['description']): ?>
                <p><strong>Description:</strong> <?= htmlspecialchars($gallery['description'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </div>

        <p style="margin-bottom: 20px;">Are you sure you want to delete this gallery? All associated images will also be permanently deleted.</p>

        <form method="POST" action="">
            <?php csrf_field(); ?>
            <div class="actions">
                <button type="submit" class="btn btn-danger">Delete Gallery</button>
                <a href="index.php" class="btn btn-primary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
