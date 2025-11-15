<?php
/**
 * Version Control API Routes
 */
require_once __DIR__.'/../controllers/VersionController.php';
require_once __DIR__.'/../services/VersionService.php';

$router->addRoute('POST', '/api/versions', function($request) {
    return VersionController::createVersion($request);
});

$router->addRoute('GET', '/api/versions/{content_id}', function($request, $params) {
    $request['content_id'] = $params['content_id'];
    return VersionController::getHistory($request);
});

$router->addRoute('GET', '/api/versions/{content_id}/{version}', function($request, $params) {
    $request['content_id'] = $params['content_id'];
    $request['version'] = $params['version'];
    return VersionController::getVersion($request);
});

$router->addRoute('POST', '/api/versions/merge', function($request) {
    return VersionController::mergeVersions($request);
});
