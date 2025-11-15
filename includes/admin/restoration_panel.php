<?php
/**
 * Conflict Detection Panel
 * 
 * Identifies when multiple users edit the same content
 * Flags conflicts when saved versions diverge
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

require_once __DIR__ . '/../versioning/diffengine.php';
require_once __DIR__ . '/../versioning/versionmetadata.php';
require_once __DIR__ . '/../middleware/adminauthmiddleware.php';

// Check admin permissions
$auth = new AdminAuthMiddleware();
if (!$auth->hasPermission('content_versions_manage')) {
    die('Access denied');
}

// Get content ID
$contentId = $_GET['content_id'] ?? null;
if (!$contentId) {
    die('Content ID required');
}

// Get latest versions
$versionMeta = new VersionMetadata();
$versions = $versionMeta->getLatestVersions($contentId, 2);

// Check if we have two recent versions to compare
if (count($versions) < 2) {
    header("Location: content_editor.php?content_id=$contentId");
    exit;
}

// Check if versions are from different authors
$hasConflict = $versions[0]['author_id'] != $versions[1]['author_id'];

// Compare content if from different authors
if ($hasConflict) {
    $diff = DiffEngine::compare($versions[0]['content'], $versions[1]['content'], $versions[0]['content_type'] === 'html');
    $hasConflict = !empty($diff);
}

if (!$hasConflict) {
    // No conflict, redirect to editor
    header("Location: content_editor.php?content_id=$contentId");
    exit;
}

// Format metadata
$formatDate = function($date) {
    return date('Y-m-d H:i:s', strtotime($date));
};

// Get author names
$getAuthor = function($id) {
    return function_exists('get_user_name') ? get_user_name($id) : 'User #'.$id;
};
?><!DOCTYPE html>
<html>
<head>
    <title>Conflict Detected</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="content-management">
        <h1>Conflict Detected</h1>
        
        <div class="alert warning">
            <p>This content has been modified by multiple users. Please resolve the conflicts below.</p>
        </div>
        
        <table class="metadata-table">
            <tr>
                <th>Version</th>
                <th>Author</th>
                <th>Modified</th>
            </tr>
            <tr>
                <td>Version #<?= $versions[0]['id'] ?></td>
                <td><?= htmlspecialchars($getAuthor($versions[0]['author_id'])) ?></td>
                <td><?= $formatDate($versions[0]['created_at']) ?></td>
            </tr>
            <tr>
                <td>Version #<?= $versions[1]['id'] ?></td>
                <td><?= htmlspecialchars($getAuthor($versions[1]['author_id'])) ?></td>
                <td><?= $formatDate($versions[1]['created_at']) ?></td>
            </tr>
        </table>
        
        <div class="actions">
            <a href="restoration_confirm.php?content_id=<?= $contentId ?>&version1=<?= $versions[0]['id'] ?>&version2=<?= $versions[1]['id'] ?>" 
               class="button primary">
                Resolve Conflicts
?>            </a>
            <a href="content_editor.php?content_id=<?= $contentId ?>" class="button">
                Ignore (Keep Current Version)
?>            </a>
        </div>
    </div>
</body>
</html>
