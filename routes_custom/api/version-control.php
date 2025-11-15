<?php

use api\v1\Controllers\VersionController;

$router->group('/api/v1/versions', function($router) {
    // Get all versions for content
    $router->get('/content/{contentId}', [VersionController::class, 'getVersions']);

    // Get specific version
    $router->get('/{versionId}', [VersionController::class, 'getVersion']);

    // Create new version
    $router->post('/content/{contentId}', [VersionController::class, 'createVersion']);

    // Compare two versions
    $router->get('/compare/{versionId1}/{versionId2}', [VersionController::class, 'compareVersions']);

    // Restore version
    $router->post('/restore/{versionId}', [VersionController::class, 'restoreVersion']);
});
