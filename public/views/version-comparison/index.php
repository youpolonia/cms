<?php
require_once 'versioncomparisonview.php';
require_once 'versionstats.php';
require_once 'versionnav.php';
?><!DOCTYPE html>
<html>
<head>
    <title>Version Comparison</title>
    <link rel="stylesheet" href="version-comparison.css">
</head>
<body>
    <div class="version-comparison">
        <div class="comparison-header">
            <h2>Version Comparison</h2>
        </div>

        <?php VersionNav::render($currentVersion, $availableVersions); 
?>        <div class="comparison-body">
            <div class="side-by-side">
                <div class="version-old">
                    <h3>Version <?= $oldVersion['number'] ?></h3>
                    <?= DiffRenderer::visualDiff($diffData['side_by_side']);  ?>
                </div>
                <div class="version-new">
                    <h3>Version <?= $newVersion['number'] ?></h3>
                    <?= DiffRenderer::visualDiff($diffData['side_by_side']);  ?>
                </div>
            </div>

            <div class="stats-container">
                <?php VersionStats::render($diffData['stats']); 
?>            </div>
        </div>
    </div>
</body>
</html>
