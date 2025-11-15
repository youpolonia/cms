<?php
declare(strict_types=1);

use includes\routing\Router;
use api\v1\Controllers\WorkerController;

Router::post('/api/v1/workers/register', function($request) {
    $db = \core\Database::connection();
    $controller = new WorkerController($db, $GLOBALS['workerSupervisor']);
    return $controller->registerWorker($request);
});

Router::post('/api/v1/workers/heartbeat', function($request) {
    $db = \core\Database::connection();
    $controller = new WorkerController($db, $GLOBALS['workerSupervisor']);
    return $controller->heartbeat($request);
});

Router::get('/api/v1/workers/metrics', function() {
    $db = \core\Database::connection();
    $controller = new WorkerController($db, $GLOBALS['workerSupervisor']);
    return $controller->getMetrics();
});

Router::get('/api/v1/workers/scaling-recommendations', function() {
    $db = \core\Database::connection();
    $controller = new WorkerController($db, $GLOBALS['workerSupervisor']);
    return $controller->getScalingRecommendations();
});
