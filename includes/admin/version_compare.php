<?php
/**
 * Version Comparison Interface
 * 
 * Displays side-by-side comparison of content versions
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
if (!$auth->hasPermission('content_versions_view')) {
    die('Access denied');
}

// Get version IDs
$version1Id = $_GET['version_id'] ?? null;
$version2Id = $_GET['compare_to'] ?? null;
if (!$version1Id || !$version2Id) {
    die('Both version IDs required');
}

// Get version metadata
$versionMeta = new VersionMetadata();
$version1 = $versionMeta->getVersion($version1Id);
$version2 = $versionMeta->getVersion($version2Id);
if (!$version1 || !$version2) {
    die('One or both versions not found');
}

// Check rollback permissions
$canRollback = $auth->hasPermission('content_versions_rollback');

// Get content for comparison
$diff = DiffEngine::compare($version1['content'], $version2['content'], $version1['content_type'] === 'html');

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
    <title>Version Comparison</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .comparison-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .version-pane {
            flex: 1;
            border: 1px solid #ddd;
            padding: 15px;
        }
        .diff-line {
            margin: 2px 0;
            padding: 2px;
        }
        .diff-insert {
            background-color: #e6ffed;
        }
        .diff-delete {
            background-color: #ffeef0;
        }
        .diff-change {
            background-color: #fff8c5;
        }
        .metadata-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="content-management">
        <h1>Version Comparison</h1>
        
        <div class="navigation">
            <a href="version_browser.php?version_id=<?= $version1Id ?>" class="button">
                Back to Version Browser
            </a>
        </div>
        
        <h2>Metadata</h2>
        <table class="metadata-table">
            <tr>
                <th></th>
                <th>Version #<?= $version1Id ?></th>
                <th>Version #<?= $version2Id ?></th>
            </tr>
            <tr>
                <td>Created</td>
                <td><?= $formatDate($version1['created_at']) ?></td>
                <td><?= $formatDate($version2['created_at']) ?></td>
            </tr>
            <tr>
                <td>Author</td>
                <td><?= htmlspecialchars($getAuthor($version1['author_id'])) ?></td>
                <td><?= htmlspecialchars($getAuthor($version2['author_id'])) ?></td>
            </tr>
            <tr>
                <td>Content Type</td>
                <td><?= htmlspecialchars($version1['content_type']) ?></td>
                <td><?= htmlspecialchars($version2['content_type']) ?></td>
            </tr>
            <tr>
                <td>Change Summary</td>
                <td colspan="2">
                    <?= nl2br(htmlspecialchars($version1['change_notes'] . "\n\n" . $version2['change_notes'])) 
?>                </td>
            </tr>
        </table>
        
        <h2>Content Comparison</h2>
        <div class="comparison-container">
            <div class="version-pane">
                <h3>Version #<?= $version1Id ?></h3>
                <?php foreach (explode("\n", $version1['content']) as $i => $line): ?>
                    <div class="diff-line <?= 
                        isset($diff[$i]) && in_array($diff[$i]['type'], ['delete', 'change']) ? 'diff-'.$diff[$i]['type'] : '' 
                    ?>">
                        <?= htmlspecialchars($line) 
?>                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="version-pane">
                <h3>Version #<?= $version2Id ?></h3>
                <?php foreach (explode("\n", $version2['content']) as $i => $line): ?>
                    <div class="diff-line <?= 
                        isset($diff[$i]) && in_array($diff[$i]['type'], ['insert', 'change']) ? 'diff-'.$diff[$i]['type'] : '' 
                    ?>">
                        <?= htmlspecialchars($line) 
?>                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if ($canRollback): ?>
            <div class="actions">
                <a href="version_rollback.php?version_id=<?= $version2Id ?>" 
                   class="button"
                   onclick="return confirm('Rollback to this version?')">
                    Rollback to Version #<?= $version2Id 
?>                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
