<?php
require_once __DIR__ . '/../core/accesschecker.php';
require_once __DIR__ . '/../core/logger/LoggerFactory.php';
require_once __DIR__ . '/../core/csrf.php';

// Check admin permissions
$accessChecker = new AccessChecker();
if (!$accessChecker->hasAccess('version_control')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Initialize logger
$logger = LoggerFactory::create('file', ['path' => __DIR__ . '/../logs/version-restore.log']);

// Get all version directories
function getVersionDirectories() {
    $contentDirs = glob(__DIR__ . '/../content/*/versions', GLOB_ONLYDIR);
    $versions = [];
    
    foreach ($contentDirs as $dir) {
        $contentId = basename(dirname($dir));
        $versionFiles = glob($dir . '/*.html');
        
        foreach ($versionFiles as $file) {
            $timestamp = basename($file, '.html');
            $versions[$contentId][] = [
                'path' => $file,
                'timestamp' => $timestamp,
                'date' => date('Y-m-d H:i:s', $timestamp)
            ];
        }
    }
    
    return $versions;
}

// Handle restore action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore'])) {
    csrf_validate_or_403();
    $contentId = basename($_POST['content_id']);
    $versionFile = realpath(__DIR__ . "/../content/{$contentId}/versions/{$_POST['version']}.html");
    
    // Validate path
    if (strpos($versionFile, realpath(__DIR__ . '/../content/')) === false || !file_exists($versionFile)) {
        $logger->log("Invalid restore attempt: " . json_encode($_POST));
        die('Invalid version file');
    }
    
    // Restore logic would go here (implementation in restore-version.php)
    $logger->log("Restore initiated for content {$contentId}, version {$_POST['version']}");
    header("Location: restore-version.php?content_id={$contentId}&version={$_POST['version']}");
    exit;
}

$versions = getVersionDirectories();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Version Control</title>
    <link rel="stylesheet" href="/admin/css/admin-ui.css">
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <h1>Version Control</h1>
        </div>
    </div>

    <div class="container">
        <?php foreach ($versions as $contentId => $contentVersions): ?>
        <div class="content-group">
            <h2>Content ID: <?= htmlspecialchars($contentId) ?></h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Version Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contentVersions as $version): ?>
                    <tr>
                        <td><?= htmlspecialchars($version['date']) ?></td>
                        <td>
                            <button class="button"
                                    data-action="preview"
                                    data-content="<?= htmlspecialchars($contentId) ?>" ?>
                                    data-version="<?= htmlspecialchars($version['timestamp']) ?>">
                                Preview
                            </button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="content_id" value="<?= htmlspecialchars($contentId) ?>">
                                <input type="hidden" name="version" value="<?= htmlspecialchars($version['timestamp']) ?>">
                                <button type="submit" name="restore" class="button">Restore</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div id="preview-<?= htmlspecialchars($contentId) ?>" class="preview-container" style="display:none;">
                <h3>Preview</h3>
                <div class="preview-content"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-action="preview"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const contentId = this.dataset.content;
                const version = this.dataset.version;
                const previewContainer = document.querySelector(`#preview-${contentId}`);
                const previewContent = previewContainer.querySelector('.preview-content');
                
                // Toggle display
                if (previewContainer.style.display === 'none') {
                    fetch(`/admin/preview-version.php?content_id=${contentId}&version=${version}`)
                        .then(response => response.text())
                        .then(html => {
                            previewContent.innerHTML = html;
                            previewContainer.style.display = 'block';
                        })
                        .catch(err => {
                            previewContent.innerHTML = 'Error loading preview';
                            previewContainer.style.display = 'block';
                        });
                } else {
                    previewContainer.style.display = 'none';
                }
            });
        });
    });
?>    </script>
</body>
</html>
