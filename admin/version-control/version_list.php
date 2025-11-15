<?php
require_once __DIR__.'/../../includes/auth.php';
require_once __DIR__.'/../../core/database.php';
require_once __DIR__.'/../../services/versioncomparator.php';

$db = new Database(); ?><?php
$versionComparator = new VersionComparator(); ?><?php
$contentId = $_GET['content_id'] ?? 0; ?>
// Get all versions for this content
$versions = $db->query(" ?>
    SELECT * FROM content_versions 
    WHERE content_id = ?  ?>
    ORDER BY version_number DESC
", [
$contentId])->fetchAll();

// Get current version
$currentVersion = $db->query(" ?>
    SELECT * FROM content_versions 
    WHERE content_id = ? AND is_current = 1 ?>
", [
$contentId])->fetch();


?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Version Control - Content #<?= htmlspecialchars($contentId) ?></title>
    <link rel="stylesheet" href="/admin/css/version-control.css">
</head>
<body>
    <div class="container">
        <h1>Version History</h1>
        <div class="current-version">
            <h2>Current Version: v<?= htmlspecialchars($currentVersion['version_number'] ?? 'N/A') ?></h2>
            <p>Last modified: <?= htmlspecialchars($currentVersion['created_at'] ?? 'N/A') ?></p>
        </div>

        <div class="version-list">
            <table>
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Date</th>
                        <th>Author</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($versions as $version): ?>
                    <tr class="<?= $version['is_current'] ? 'current' : '' ?>">
                        <td>v<?= htmlspecialchars($version['version_number']) ?></td>
                        <td><?= htmlspecialchars($version['created_at']) ?></td>
                        <td><?= htmlspecialchars(getUserName($version['author_id'])) ?></td>
                        <td>
                            <a href="version_compare.php?content_id=<?= $contentId ?>&version1=<?= $version['id'] ?>&version2=<?= $currentVersion['id'] ?>" class="btn compare">Compare</a>
                            <a href="version_restore.php?version_id=<?= $version['id'] ?>" class="btn restore">Restore</a>
                        </td>
                    </tr>
                    <?php endforeach;  ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
