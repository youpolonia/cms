<?php
/**
 * Gallery Manager - Images
 * List and manage images in a gallery
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

// RBAC: Require galleries.read permission
if (!function_exists('cms_require_permission')) {
    function cms_require_permission(string $permission): void {
        if (!isset($_SESSION['user_permissions']) || !in_array($permission, $_SESSION['user_permissions'], true)) {
            http_response_code(403);
            exit('Permission denied: ' . htmlspecialchars($permission, ENT_QUOTES, 'UTF-8'));
        }
    }
}
cms_require_permission('galleries.read');

$db = \core\Database::connection();

$galleryId = (int)($_GET['id'] ?? 0);
if ($galleryId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch gallery
$stmt = $db->prepare("SELECT * FROM albums WHERE id = ?");
$stmt->execute([$galleryId]);
$gallery = $stmt->fetch(\PDO::FETCH_ASSOC);

if (!$gallery) {
    header('Location: index.php');
    exit;
}

// Check if album_images table exists
$tableCheck = $db->query("SHOW TABLES LIKE 'album_images'");
$tableExists = $tableCheck && $tableCheck->rowCount() > 0;

$images = [];
$message = '';
$messageType = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_image'])) {
    // Check permission for deletion
    if (!isset($_SESSION['user_permissions']) || !in_array('galleries.edit', $_SESSION['user_permissions'], true)) {
        $message = 'Permission denied for deletion';
        $messageType = 'error';
    } else {
        csrf_validate_or_403();

        $imageId = (int)($_POST['image_id'] ?? 0);
        if ($imageId > 0 && $tableExists) {
            // Fetch image details
            $stmt = $db->prepare("SELECT * FROM album_images WHERE id = ? AND album_id = ?");
            $stmt->execute([$imageId, $galleryId]);
            $image = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($image) {
                // Delete file
                $filePath = __DIR__ . '/../../' . $image['file_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }

                // Delete from database
                $stmt = $db->prepare("DELETE FROM album_images WHERE id = ?");
                $stmt->execute([$imageId]);

                $message = 'Image deleted successfully';
                $messageType = 'success';
            }
        }
    }
}

// Fetch images
if ($tableExists) {
    $stmt = $db->prepare("SELECT * FROM album_images WHERE album_id = ? ORDER BY created_at DESC");
    $stmt->execute([$galleryId]);
    $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Images</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .info-box p { margin-bottom: 5px; }
        .info-box p:last-child { margin-bottom: 0; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .actions-top { display: flex; gap: 10px; margin-bottom: 30px; }
        .image-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .image-card { background: #fff; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .image-card img { width: 100%; height: 200px; object-fit: cover; display: block; }
        .image-card-body { padding: 15px; }
        .image-card-body p { margin-bottom: 8px; font-size: 14px; color: #666; }
        .image-card-body p:last-of-type { margin-bottom: 15px; }
        .image-card-actions { display: flex; gap: 10px; }
        .empty-state { text-align: center; padding: 60px 20px; color: #666; }
        .schema-block { background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 20px; }
        .schema-block pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gallery Images</h1>

        <div class="info-box">
            <p><strong>Gallery:</strong> <?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Slug:</strong> <?= htmlspecialchars($gallery['slug'], ENT_QUOTES, 'UTF-8') ?></p>
            <?php if ($tableExists): ?>
                <p><strong>Total Images:</strong> <?= count($images) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?= htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="actions-top">
            <a href="upload.php?id=<?= htmlspecialchars((string)$galleryId, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success">Upload Images</a>
            <a href="edit.php?id=<?= htmlspecialchars((string)$galleryId, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Edit Gallery</a>
            <a href="index.php" class="btn btn-primary">Back to Galleries</a>
        </div>

        <?php if (!$tableExists): ?>
            <div class="alert alert-warning">
                <strong>Database table missing:</strong> The <code>album_images</code> table does not exist.
            </div>
            <div class="schema-block">
                <h3>Create the album_images table:</h3>
                <pre>CREATE TABLE IF NOT EXISTS album_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
    INDEX idx_album_id (album_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;</pre>
            </div>
        <?php elseif (empty($images)): ?>
            <div class="empty-state">
                <p>No images in this gallery yet. Upload some images to get started.</p>
            </div>
        <?php else: ?>
            <div class="image-grid">
                <?php foreach ($images as $image): ?>
                    <div class="image-card">
                        <img src="/<?= htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($image['file_name'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="image-card-body">
                            <p><strong>File:</strong> <?= htmlspecialchars($image['file_name'], ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Size:</strong> <?= number_format($image['file_size'] / 1024, 2) ?> KB</p>
                            <p><strong>Uploaded:</strong> <?= htmlspecialchars($image['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="image-card-actions">
                                <a href="/<?= htmlspecialchars($image['file_path'], ENT_QUOTES, 'UTF-8') ?>"
                                   target="_blank" class="btn btn-primary btn-sm">View Full</a>
                                <?php if (isset($_SESSION['user_permissions']) && in_array('galleries.edit', $_SESSION['user_permissions'], true)): ?>
                                    <form method="POST" style="display: inline;">
                                        <?php csrf_field(); ?>
                                        <input type="hidden" name="image_id" value="<?= htmlspecialchars((string)$image['id'], ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" name="delete_image" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Delete this image?')">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
