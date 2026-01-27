<?php
/**
 * Version List View
 */
require_once __DIR__ . '/../../../core/versioncontrol.php';

$contentType = $_GET['content_type'] ?? '';
$contentId = $_GET['content_id'] ?? '';

if (empty($contentType) || empty($contentId)) {
    die('Invalid content parameters');
}

$versions = VersionControl::listVersions($contentType, $contentId);
$history = VersionControl::getVersionHistory($contentType, $contentId);

?><div class="version-management">
    <h2>Version History for <?= htmlspecialchars($contentType) ?> #<?= htmlspecialchars($contentId) ?></h2>
    <table class="version-table">
        <thead>
            <tr>
                <th>Version ID</th>
                <th>Created At</th>
                <th>Comment</th>
                <th>Size</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versions as $version): ?>
            <tr>
                <td><?= htmlspecialchars($version['id']) ?></td>
                <td><?= htmlspecialchars($version['created_at']) ?></td>
                <td><?= htmlspecialchars($version['comment']) ?></td>
                <td><?= isset($history[$version['id']]) ? formatSize($history[$version['id']]['size']) : 'N/A' ?></td>
                <td>
                    <a href="?page=version_view&content_type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>&version_id=<?= urlencode($version['id']) ?>" class="btn-view">View</a>
                    <a href="?page=version_compare&content_type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>&version_id=<?= urlencode($version['id']) ?>" class="btn-compare">Compare</a>
                    <a href="?page=version_restore&content_type=<?= urlencode($contentType) ?>&content_id=<?= urlencode($contentId) ?>&version_id=<?= urlencode($version['id']) ?>" class="btn-restore">Restore</a>
                </td>
            </tr>
            <?php endforeach;  ?>
        </tbody>
    </table>
</div>

<?php
function formatSize($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return round($bytes / 1024, 2) . ' KB';
    }
    return $bytes . ' bytes';
}
