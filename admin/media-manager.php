<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
if (!AccessChecker::hasPermission('media.manage')) {
    die('Access denied');
}

// Ensure required directories exist
$mediaDirs = ['uploads', 'generated'];
foreach ($mediaDirs as $dir) {
    $path = __DIR__ . '/../media/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }
}

// Scan media directories
function scanMediaDir($dir) {
    $path = __DIR__ . '/../media/' . $dir;
    if (!is_dir($path)) return [];
    
    $files = [];
    $items = scandir($path);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $filePath = $path . '/' . $item;
        $files[] = [
            'name' => $item,
            'path' => 'media/' . $dir . '/' . $item,
            'size' => filesize($filePath),
            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
            'type' => mime_content_type($filePath)
        ];
    }
    return $files;
}

$uploads = scanMediaDir('uploads');
$generated = scanMediaDir('generated');
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Media Manager</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>Media Manager</h1>
        </div>
    </div>

    <div class="container">
        <div class="section">
            <h2>Uploads</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Modified</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($uploads as $file): ?>
                <tr>
                    <td>
                        <?php if (strpos($file['type'], 'image/') === 0): ?>
                            <img src="../<?= $file['path'] ?>" class="thumbnail">
                        <?php else: ?>
                            <div>[<?= $file['type'] ?>]</div>
                        <?php endif; ?>
                    </td>
                    <td><?= $file['name'] ?></td>
                    <td><?= round($file['size'] / 1024) ?> KB</td>
                    <td><?= $file['modified'] ?></td>
                    <td>
                        <button class="button danger" data-action="delete" data-path="<?= $file['path'] ?>" data-token="<?= CSRF::generate() ?>">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <div class="section">
            <h2>Upload New File</h2>
            <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <input type="hidden" name="csrf_token" value="<?= CSRF::generate() ?>">
                <button type="submit" class="button primary">Upload</button>
            </form>
        </div>

        <div class="section">
            <h2>Generated Files</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Preview</th>
                    <th>Name</th>
                    <th>Size</th>
                    <th>Modified</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($generated as $file): ?>
                <tr>
                    <td>
                        <?php if (strpos($file['type'], 'image/') === 0): ?>
                            <img src="../<?= $file['path'] ?>" class="thumbnail">
                        <?php else: ?>
                            <div>[<?= $file['type'] ?>]</div>
                        <?php endif; ?>
                    </td>
                    <td><?= $file['name'] ?></td>
                    <td><?= round($file['size'] / 1024) ?> KB</td>
                    <td><?= $file['modified'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-action="delete"]').forEach(button => {
            button.addEventListener('click', function() {
                const path = this.dataset.path;
                if (confirm('Delete this file?')) {
                    fetch('../api/delete-media.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ path: path })
                    }).then(response => {
                        if (response.ok) location.reload();
                        else alert('Error deleting file');
                    });
                }
            });
        });

        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('../api/upload-media.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (response.ok) location.reload();
                else alert('Error uploading file');
            });
        });
    });
    </script>

    <div class="footer-links">
        <a href="dashboard.php" class="button">Back to Dashboard</a>
    </div>
</body>
</html>
