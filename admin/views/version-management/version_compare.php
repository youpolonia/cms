<?php
/**
 * Version Comparison View
 */
require_once __DIR__ . '/../../../core/versioncontrol.php';

$contentType = $_GET['content_type'] ?? '';
$contentId = $_GET['content_id'] ?? '';
$versionId1 = $_GET['version_id1'] ?? '';
$versionId2 = $_GET['version_id2'] ?? '';

if (empty($contentType) || empty($contentId) || empty($versionId1)) {
    die('Invalid parameters');
}

// If only one version specified, compare with current
if (empty($versionId2)) {
    $currentContent = []; // TODO: Get current content from CMS
    $version1 = VersionControl::getVersion($contentType, $contentId, $versionId1);
    $diff = VersionControl::arrayRecursiveDiff($currentContent, $version1['data']);
} else {
    $diff = VersionControl::diffVersions($contentType, $contentId, $versionId1, $versionId2);
}

?><div class="version-compare">
    <h2>Comparing Versions</h2>
    <div class="version-info">
        <div class="version-from">
            <h3>Version <?= htmlspecialchars($versionId1) ?></h3>
            <?php if (!empty($versionId2)): ?>
                <p>Comparing with Version <?= htmlspecialchars($versionId2) ?></p>
            <?php else: ?>
                <p>Comparing with Current Version</p>
            <?php endif;  ?>
        </div>
    </div>

    <div class="diff-results">
        <?php if (empty($diff)): ?>
            <p class="no-changes">No differences found between versions</p>
        <?php else: ?>
            <table class="diff-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Old Value</th>
                        <th>New Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($diff as $field => $change): ?>
                    <tr>
                        <td><?= htmlspecialchars($field) ?></td>
                        <td class="old-value">
                            <?= is_array($change) ? 'Array' : htmlspecialchars(json_encode($change)) 
?>                        </td>
                        <td class="new-value">
                            <?= is_array($change) ? 'Array' : htmlspecialchars(json_encode($change)) 
?>                        </td>
                    </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>
        <?php endif;  ?>
    </div>
</div>

<style>
.diff-table {
    width: 100%;
    border-collapse: collapse;
}
.diff-table th, .diff-table td {
    border: 1px solid #ddd;
    padding: 8px;
}
.old-value {
    background-color: #ffeeee;
}
.new-value {
    background-color: #eeffee;
}
.no-changes {
    padding: 20px;
    text-align: center;
    color: #666;
}
</style>
