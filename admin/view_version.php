<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/contentversioning.php';
require_once __DIR__ . '/../includes/security/authservicewrapper.php';

$contentId = (int)($_GET['content_id'] ?? 0);
$versionId = $_GET['version_id'] ?? '';

if ($contentId <= 0 || empty($versionId)) {
    header('HTTP/1.1 400 Bad Request');
    exit('Invalid parameters');
}

try {
    $version = ContentVersioning::getVersion($contentId, $versionId);
} catch (InvalidArgumentException $e) {
    header('HTTP/1.1 404 Not Found');
    exit('Version not found');
}
?><!DOCTYPE html>
<html>
<head>
    <title>Version <?= htmlspecialchars($versionId) ?> - Content #<?= htmlspecialchars((string)$contentId) ?></title>
    <link rel="stylesheet" href="/admin/css/styles.css">
</head>
<body>
    <h1>Version <?= htmlspecialchars($versionId) ?></h1>
    <p>Created: <?= date('Y-m-d H:i:s', $version['created_at']) ?></p>
    <div class="content-view">
        <?= htmlspecialchars($version['content']) 
?>    </div>
    <p><a href="/admin/version_history.php?content_id=<?= $contentId ?>">Back to version history</a></p>
</body>
</html>
