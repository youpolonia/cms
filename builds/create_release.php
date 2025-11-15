<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/releasebuilder.php';
require_once __DIR__ . '/../includes/versionmanager.php';

$releaseBuilder = new ReleaseBuilder();
$versionManager = new VersionManager();

$release = $releaseBuilder->createRelease($_POST);
if ($release) {
    echo "Release created successfully";
} else {
    echo "Release creation failed";
}
