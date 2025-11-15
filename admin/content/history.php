<?php
require_once __DIR__ . '/../../core/contenthistorymanager.php';
require_once __DIR__ . '/../../core/auth.php';

Auth::checkAdminAccess();

$contentId = (int)($_GET['content_id'] ?? 0);
if (!$contentId) {
    header('Location: /admin/content/');
    exit;
}

$historyManager = new ContentHistoryManager();
$versions = $historyManager->getVersions($contentId);

?><!DOCTYPE html>
<html>
<head>
    <title>Content Version History</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <div class="container">
        <h1>Content Version History</h1>
        <a href="/admin/content/edit.php?id=<?= $contentId ?>" class="btn">Back to Content</a>
        <table class="version-table">
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versions as $version): ?>
                <tr>
                    <td><?= $version['version_number'] ?></td>
                    <td><?= htmlspecialchars(getUserName($version['author_id'])) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($version['created_at'])) ?></td>
                    <td>
                        <a href="/admin/content/restore.php?content_id=<?= $contentId ?>&version=<?= $version['version_number'] ?>"
                           class="btn btn-sm"
                           onclick="return confirm('Restore this version?')">Restore</a>
                        <a href="/admin/content/compare.php?content_id=<?= $contentId ?>&version=<?= $version['version_number'] ?>"
                           class="btn btn-sm">Compare</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
