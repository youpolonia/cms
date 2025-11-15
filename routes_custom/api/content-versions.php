<?php
// Version management endpoints
require_once __DIR__.'/../../includes/routing/helpers.php';
require_once __DIR__ . '/../../includes/model.php';
require_once __DIR__.'/../../controllers/VersionController.php';

$router = new Router();

// Version creation endpoint
$router->post('/api/content-versions', function($request) {
    $controller = new VersionController();
    return $controller->create($request);
});

// Version comparison endpoints
$router->get('/api/content-versions/compare/{content}/versions/{version}/compare', function($request, $content, $version) {
    $controller = new VersionController();
    return $controller->compareWithCurrent($request, $content, $version);
});

$router->get('/api/content-versions/compare', function($request) {
    $controller = new VersionController();
    return $controller->compare($request);
});

$router->post('/api/content-versions/compare/save', function($request) {
    $controller = new VersionController();
    return $controller->saveComparison($request);
});

$router->get('/api/content-versions/compare/{comparison}', function($request, $comparison) {
    $controller = new VersionController();
    return $controller->getComparison($request, $comparison);
});

// Version visualization endpoints
$router->get('/api/content-versions/visualization/visual-diff/{id}', function($request, $id) {
    $controller = new VersionController();
    return $controller->visualDiff($request, $id);
});

$router->get('/api/content-versions/visualization/version-timeline/{id}', function($request, $id) {
    $controller = new VersionController();
    return $controller->versionTimeline($request, $id);
});

$router->get('/api/content-versions/visualization/metadata/{id}', function($request, $id) {
    $controller = new VersionController();
    return $controller->versionMetadata($request, $id);
});

// Version restoration
$router->post('/api/content-versions/{content}/restore', function($request, $content) {
    $controller = new VersionController();
    return $controller->restore($request, $content);
});

// Comparison history
$router->get('/api/content-versions/{content}/history', function($request, $content) {
    $controller = new VersionController();
    return $controller->history($request, $content);
});

// Comparison statistics
$router->get('/api/content-versions/{content}/stats', function($request, $content) {
    $controller = new VersionController();
    return $controller->comparisonStats($request, $content);
});
