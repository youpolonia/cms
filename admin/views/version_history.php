<?php
/**
 * Version History View
 */
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/permission_check.php';
check_permission('content_edit');

$contentId = $_GET['content_id'] ?? null;
if (!$contentId) {
    header("Location: /admin/content");
    exit;
}

// Get content details
$content = json_decode(file_get_contents(API_BASE_URL . "/content/$contentId"), true);
// Get versions
$versions = json_decode(file_get_contents(API_BASE_URL . "/versions?content_id=$contentId"), true);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Version History - <?= htmlspecialchars($content['title'] ?? 'Untitled') ?></title>
    <link rel="stylesheet" href="/admin/css/version_history.css">
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; 
?>    <div class="container">
        <h1>Version History: <?= htmlspecialchars($content['title'] ?? 'Untitled') ?></h1>
        <div class="version-container">
            <div class="version-list">
                <h2>Versions</h2>
                <div class="version-search">
                    <input type="text" id="version-search" placeholder="Search versions...">
                </div>
                <div class="version-items">
                    <?php foreach ($versions as $version): ?>
                        <div class="version-item" data-version-id="<?= $version['id'] ?>">
                            <div class="version-header">
                                <span class="version-number">v<?= $version['version_number'] ?></span>
                                <?php if ($version['is_major_version']): ?>
                                    <span class="version-tag major">Major</span>
                                <?php endif;  ?>                                <?php if ($version['is_autosave']): ?>
                                    <span class="version-tag autosave">Autosave</span>
                                <?php endif;  ?>
                            </div>
                            <div class="version-meta">
                                <span class="version-date"><?= date('M j, Y H:i', strtotime($version['created_at'])) ?></span>
                                <span class="version-author">by <?= htmlspecialchars($version['author_name'] ?? 'System') ?></span>
                            </div>
                            <div class="version-notes"><?= htmlspecialchars($version['change_notes'] ?? 'No change notes') ?></div>
                            <div class="version-actions">
                                <button class="btn-view" data-version-id="<?= $version['id'] ?>">View</button>
                                <button class="btn-compare" data-version-id="<?= $version['id'] ?>">Compare</button>
                                <?php if (has_permission('content_edit')): ?>
                                    <button class="btn-rollback" data-version-id="<?= $version['id'] ?>">Restore</button>
                                <?php endif;  ?>
                            </div>
                        </div>
                    <?php endforeach;  ?>
                </div>
            </div>
            
            <div class="version-detail">
                <div class="detail-header">
                    <h2>Version Details</h2>
                    <div class="detail-actions">
                        <button id="btn-compare" disabled>Compare Selected</button>
                    </div>
                </div>
                <div class="detail-content">
                    <div class="diff-viewer" id="diff-viewer">
                        <p>Select a version to view details</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/admin/js/version_history.js"></script>
</body>
</html>
