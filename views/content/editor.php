<?php
require_once __DIR__ . '/../../includes/versioncomparator.php';
require_once __DIR__ . '/../../models/versionmodel.php';
require_once __DIR__ . '/../../models/contentmodel.php';

$contentId = $_GET['id'] ?? null;
$content = []; // Will be populated from DB

$versionModel = new VersionModel();
$versions = $contentId ? $versionModel->getVersions($contentId) : [];

// Load TinyMCE from CDN
$tinymceScript = 'https://cdn.tiny.cloud/1/YOUR-API-KEY/tinymce/6/tinymce.min.js';
?><!DOCTYPE html>
<html>
<head>
    <title>Content Editor</title>
    <script src="<?= $tinymceScript ?>" referrerpolicy="origin"></script>
    <link rel="stylesheet" href="/css/editor.css">
</head>
<body>
    <div class="editor-container">
        <form id="content-form" method="post" action="/api/content/save">
            <input type="hidden" name="content_id" value="<?= htmlspecialchars($contentId) ?>">
            <div class="toolbar">
                <button type="button" id="media-upload">Upload Media</button>
                <button type="button" id="version-history">Version History</button>
                <button type="submit">Save</button>
            </div>
            
            <textarea id="editor" name="content"><?= htmlspecialchars($content['body'] ?? '') ?></textarea>
        </form>
    </div>

    <div id="version-modal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Version History</h2>
            <div id="version-list">
                <?php foreach ($versions as $version): ?>
                <div class="version-item">
                    <span>Version <?= $version['version_number'] ?></span>
                    <span><?= date('Y-m-d H:i', strtotime($version['created_at'])) ?></span>
                    <button class="view-diff" data-version="<?= $version['id'] ?>">View Changes</button>
                    <button class="restore" data-version="<?= $version['id'] ?>">Restore</button>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="diff-viewer" style="display:none;"></div>
        </div>
    </div>

    <script src="/js/editor.js"></script>
</body>
</html>
