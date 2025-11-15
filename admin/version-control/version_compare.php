<?php
declare(strict_types=1); ?><?php
require_once __DIR__.'/../../includes/security/authservicewrapper.php';
require_once __DIR__.'/../../core/database.php';
require_once __DIR__.'/../../services/versioncomparator.php';
require_once __DIR__.'/../../includes/database/middleware/tenantisolation.php';

$db = new Database(); ?><?php
$versionComparator = new VersionComparator(); ?>
$version1Id = $_GET['version1'] ?? 0; ?><?php
$version2Id = $_GET['version2'] ?? 0; ?><?php
$tenantId = $_SESSION['current_tenant'] ?? null; ?>
if (!$tenantId) {
    header('HTTP/1.1 403 Forbidden');
    die('Invalid tenant context');
}

// Get version details with tenant isolation
$version1 = $db->query( ?>    "SELECT * FROM content_versions WHERE id = ? AND tenant_id = ?", ?>
    [
$version1Id, $tenantId]
)->fetch();

$version2 = $db->query( ?>    "SELECT * FROM content_versions WHERE id = ? AND tenant_id = ?", ?>
    [
$version2Id, $tenantId]
)->fetch();

if (!$version1 || !$version2) {
    header('HTTP/1.1 404 Not Found');
    die('Version not found or not accessible');
}

// Compare versions
$diff = $versionComparator->compareVersions($version1Id, $version2Id); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Version Comparison</title>
    <link rel="stylesheet" href="/admin/css/version-control.css">
    <style>
        .diff-container {
            display: flex;
            gap: 20px;
        }
        .diff-side {
            flex: 1;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .diff-line {
            padding: 2px;
            margin: 1px 0;
        }
        .diff-added {
            background-color: #e6ffed;
        }
        .diff-removed {
            background-color: #ffeef0;
            text-decoration: line-through;
        }
        .diff-changed {
            background-color: #fff8c5;
        }
        .merge-actions {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Version Comparison</h1>
        <div class="version-info">
            <h2>Comparing v<?= htmlspecialchars($version1['version_number']) ?> and v<?= htmlspecialchars($version2['version_number']) ?></h2>
        </div>

        <div class="diff-container">
            <div class="diff-side">
                <h3>Version <?= htmlspecialchars($version1['version_number']) ?></h3>
                <?php foreach ($diff['changes'] as $change): ?>
                    <div class="diff-line <?= $change['type'] === 'delete' ? 'diff-removed' : '' ?>">
                        <?= htmlspecialchars($change['content']['old'] ?? '')  ?>
                    </div>
                <?php endforeach;  ?>
            </div>

            <div class="diff-side">
                <h3>Version <?= htmlspecialchars($version2['version_number']) ?></h3>
                <?php foreach ($diff['changes'] as $change): ?>
                    <div class="diff-line <?= $change['type'] === 'insert' ? 'diff-added' : '' ?>">
                        <?= htmlspecialchars($change['content']['new'] ?? '')  ?>
                    </div>
                <?php endforeach;  ?>
            </div>
        </div>

        <div class="merge-actions">
            <a href="version_merge.php?version1=<?= $version1Id ?>&version2=<?= $version2Id ?>" class="btn merge">Merge These Versions</a>
        </div>
    </div>
</body>
</html>
