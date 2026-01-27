<?php
/**
 * Content Version History
 * @package CMS
 */
if (!defined('CMS_ADMIN')) {
    die('Invalid access');
}

use CMS\Core\PermissionManager;

// Verify permissions
if (!PermissionManager::has('content_version_view')) {
    die('Access denied');
}

$versions = $this->data['versions'] ?? [];
$contentId = $this->data['content_id'] ?? 0;
?>
<div class="version-history">
    <h2>Version History for Content #<?php echo htmlspecialchars($contentId); ?></h2>

    <table class="version-table">
        <thead>
            <tr>
                <th>Version</th>
                <th>Modified</th>
                <th>Author</th>
                <th>Changes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versions as $version): ?>
            <tr>
                <td><?php echo htmlspecialchars($version['version_number']); ?></td>
                <td><?php echo htmlspecialchars($version['modified_at']); ?></td>
                <td><?php echo htmlspecialchars($version['author_name']); ?></td>
                <td><?php echo htmlspecialchars($version['change_summary']); ?></td>
                <td>
                    <?php if (PermissionManager::has('content_version_rollback')): ?>
                        <a href="/admin/content/rollback.php?content_id=<?php echo $contentId; ?>&version=<?php echo $version['version_number']; ?>"
                           class="btn btn-sm btn-warning"
                           onclick="return confirm('Are you sure you want to rollback to this version?')">
                            Rollback
                        </a>
                    <?php endif; ?>
                    <a href="/admin/content/compare.php?content_id=<?php echo $contentId; ?>&version=<?php echo $version['version_number']; ?>"
                       class="btn btn-sm btn-info">
                        Compare
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
