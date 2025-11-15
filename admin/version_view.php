<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/security/authservicewrapper.php';
AuthServiceWrapper::checkAdminAccess();

$versionId = $_GET['id'] ?? 0;
if (!$versionId) {
    header('HTTP/1.0 400 Bad Request');
    exit('Version ID required');
}

require_once __DIR__ . '/../includes/db.php';
$db = getDBConnection();

$stmt = $db->prepare("
    SELECT v.*, c.title AS content_title 
    FROM versions v
    JOIN content c ON v.content_id = c.id
    WHERE v.id = ?
");
$stmt->execute([$versionId]);
$version = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$version) {
    header('HTTP/1.0 404 Not Found');
    exit('Version not found');
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Version <?= htmlspecialchars($version['version_number']) ?> - <?= htmlspecialchars($version['content_title']) ?></title>
    <link rel="stylesheet" href="/admin/css/version-view.css">
</head>
<body>
    <div class="container">
        <h1>Version <?= htmlspecialchars($version['version_number']) ?></h1>
        <h2><?= htmlspecialchars($version['content_title']) ?></h2>
        <div class="version-meta">
            <p><strong>Created By:</strong> <?= htmlspecialchars($version['created_by']) ?></p>
            <p><strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($version['created_at'])) ?></p>
            <?php if ($version['rollback_notes']): ?>
                <p><strong>Rollback Notes:</strong> <?= htmlspecialchars($version['rollback_notes']) ?></p>
            <?php endif;  ?>
        </div>

        <div class="version-content">
            <h3>Content:</h3>
            <pre><?= htmlspecialchars($version['content_data']) ?></pre>
        </div>

        <div class="actions">
            <a href="/admin/version_management.php" class="btn">Back to Version Management</a>
        </div>
    </div>
</body>
</html>
