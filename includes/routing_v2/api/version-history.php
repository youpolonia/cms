<?php
require_once __DIR__ . '/../../controllers/api/versionhistorycontroller.php';
require_once __DIR__ . '/../../core/router.php';

$router = new Router();
$controller = new VersionHistoryController();

// GET /api/versions
$router->get('/api/versions', function() use ($controller) {
    $controller->listVersions();
});

// GET /api/versions/{id}
$router->get('/api/versions/(\d+)', function($matches) use ($controller) {
    $controller->getVersion($matches[1]);
});

// GET /api/content/{id}/versions
$router->get('/api/content/(\d+)/versions', function($matches) use ($controller) {
    $controller->getContentVersions($matches[1]);
});

// GET /api/version/compare
$router->get('/api/version/compare', function() use ($controller) {
    $controller->compareVersions();
});

return $router;
