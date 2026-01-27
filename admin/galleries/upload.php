<?php
/**
 * Gallery Manager - Upload
 * Upload images to gallery
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

$errors = [];
$successMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();

    if (!$tableExists) {
        $errors[] = 'The album_images table does not exist';
    } elseif (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        $errors[] = 'No images selected';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $maxFileSize = 10 * 1024 * 1024; // 10 MB

        // Create upload directory
        $uploadDir = __DIR__ . '/../../uploads/galleries/' . $galleryId;
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $errors[] = 'Failed to create upload directory';
            }
        }

        if (empty($errors)) {
            $uploadedCount = 0;
            $fileCount = count($_FILES['images']['name']);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Upload error for file ' . ($i + 1);
                    continue;
                }

                $fileName = $_FILES['images']['name'][$i];
                $fileSize = $_FILES['images']['size'][$i];
                $fileTmp = $_FILES['images']['tmp_name'][$i];

                // Validate extension
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions, true)) {
                    $errors[] = 'Invalid file type for ' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . '. Allowed: jpg, jpeg, png, webp, gif';
                    continue;
                }

                // Validate file size
                if ($fileSize > $maxFileSize) {
                    $errors[] = 'File too large: ' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8') . ' (max 10 MB)';
                    continue;
                }

                // Generate sanitized filename
                $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);
                $newFileName = $sanitized . '_' . time() . '_' . $i . '.' . $extension;
                $filePath = $uploadDir . '/' . $newFileName;

                // Move file
                if (move_uploaded_file($fileTmp, $filePath)) {
                    // Insert into database
                    $relativePath = 'uploads/galleries/' . $galleryId . '/' . $newFileName;
                    $stmt = $db->prepare("INSERT INTO album_images (album_id, file_path, file_name, file_size, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $galleryId,
                        $relativePath,
                        $fileName,
                        $fileSize
                    ]);
                    $uploadedCount++;
                } else {
                    $errors[] = 'Failed to upload: ' . htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
                }
            }

            if ($uploadedCount > 0) {
                $successMessages[] = 'Successfully uploaded ' . $uploadedCount . ' image(s)';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Images</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-bottom: 30px; color: #333; }
        .alert { padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input[type="file"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        .form-group small { display: block; margin-top: 5px; color: #666; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-primary:hover { background: #0056b3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .actions { display: flex; gap: 10px; margin-top: 30px; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .schema-block { background: #f8f9fa; padding: 20px; border-radius: 4px; margin-top: 20px; }
        .schema-block pre { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Images to Gallery</h1>

        <div class="info-box">
            <p><strong>Gallery:</strong> <?= htmlspecialchars($gallery['title'], ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Slug:</strong> <?= htmlspecialchars($gallery['slug'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>

        <?php if (!empty($successMessages)): ?>
            <div class="alert alert-success">
                <?php foreach ($successMessages as $message): ?>
                    <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
        <?php else: ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <?php csrf_field(); ?>

                <div class="form-group">
                    <label for="images">Select Images *</label>
                    <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
                    <small>Allowed formats: JPG, JPEG, PNG, WEBP, GIF | Max file size: 10 MB</small>
                </div>

                <div class="actions">
                    <button type="submit" class="btn btn-success">Upload Images</button>
                    <a href="images.php?id=<?= htmlspecialchars((string)$galleryId, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">View Images</a>
                    <a href="index.php" class="btn btn-primary">Back to Galleries</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
