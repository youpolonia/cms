<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/contentversioning.php';
require_once __DIR__ . '/../includes/security/authservicewrapper.php';

$contentId = (int)($_GET['content_id'] ?? 0);
if ($contentId <= 0) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid content ID');
}

$versions = ContentVersioning::listVersions($contentId);
$versionData = [];
foreach ($versions as $versionFile) {
    $versionId = substr($versionFile, strpos($versionFile, '_v_') + 3, 13);
    $versionData[] = ContentVersioning::getVersion($contentId, $versionId);
}

usort($versionData, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
?><!DOCTYPE html>
<html>
<head>
    <title>Version History - Content #<?= htmlspecialchars((string)$contentId) ?></title>
    <link rel="stylesheet" href="/admin/css/styles.css">
</head>
<body>
    <h1>Version History for Content #<?= htmlspecialchars((string)$contentId) ?></h1>
    <table>
        <thead>
            <tr>
                <th>Version ID</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($versionData as $version): ?>
            <tr>
                <td><?= htmlspecialchars($version['version_id']) ?></td>
                <td><?= date('Y-m-d H:i:s', $version['created_at']) ?></td>
                <td>
                    <a href="/admin/view_version.php?content_id=<?= $contentId ?>&version_id=<?= $version['version_id'] ?>">View</a>
                    <a href="/admin/restore_version.php?content_id=<?= $contentId ?>&version_id=<?= $version['version_id'] ?>">Restore</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
