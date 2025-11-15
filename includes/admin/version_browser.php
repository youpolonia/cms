<?php
/**
 * Version Browser Component
 * 
 * Displays version metadata with rollback capabilities
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

require_once __DIR__ . '/../versioning/versionmetadata.php';
require_once __DIR__ . '/../middleware/adminauthmiddleware.php';

// Check admin permissions
$auth = new AdminAuthMiddleware();
if (!$auth->hasPermission('content_versions_view')) {
    die('Access denied');
}

$versionId = $_GET['version_id'] ?? null;
if (!$versionId) {
    die('Version ID required');
}

$versionMeta = new VersionMetadata();
$metadata = $versionMeta->getMetadata($versionId);
if (!$metadata) {
    die('Version not found');
}

// Get author name if available
$authorName = 'Unknown';
if (function_exists('get_user_name')) {
    $authorName = get_user_name($metadata['author_id']);
}

// Format timestamp
$createdAt = date('Y-m-d H:i:s', strtotime($metadata['created_at']));

// Check rollback permissions
$canRollback = $auth->hasPermission('content_versions_rollback');
?><!DOCTYPE html>
<html>
<head>
    <title>Version Browser</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="content-management">
        <h1>Version Metadata</h1>
        
        <table class="version-table">
            <tr>
                <th>Field</th>
                <th>Value</th>
            </tr>
            <tr>
                <td>Version ID</td>
                <td><?= htmlspecialchars($versionId) ?></td>
            </tr>
            <tr>
                <td>Created</td>
                <td><?= htmlspecialchars($createdAt) ?></td>
            </tr>
            <tr>
                <td>Author</td>
                <td><?= htmlspecialchars($authorName) ?></td>
            </tr>
            <tr>
                <td>Content Type</td>
                <td><?= htmlspecialchars($metadata['content_type']) ?></td>
            </tr>
            <tr>
                <td>Change Summary</td>
                <td><?= nl2br(htmlspecialchars($metadata['change_notes'])) ?></td>
            </tr>
            <tr>
                <td>Tags</td>
                <td>
                    <?php foreach (json_decode($metadata['tags'] ?? '[]') as $tag): ?>
                        <span class="tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td>Major Version</td>
                <td><?= $metadata['is_major_version'] ? 'Yes' : 'No' ?></td>
            </tr>
        </table>

        <?php if ($canRollback): ?>
            <div class="actions">
                <a href="version_rollback.php?version_id=<?= $versionId ?>" 
                   class="button"
                   onclick="return confirm('Rollback to this version?')">
                    Rollback
?>                </a>
                <a href="version_compare.php?version_id=<?= $versionId ?>" 
                   class="button">
                    Compare
?>                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
