<?php
require_once __DIR__ . '/../../controllers/api/recommendationcontroller.php';
require_once __DIR__ . '/../../core/router.php';

$router = new Router();

$router->get('/api/recommendations', function(Request $request) {
    $controller = new RecommendationController();
    return $controller->list($request);
});

$router->get('/api/recommendations/{id}', function(Request $request, $params) {
    $controller = new RecommendationController();
    return $controller->single($request, $params['id']);
});

$router->post('/api/recommendations/feedback', function(Request $request) {
    $controller = new RecommendationController();
    return $controller->feedback($request);
});

return $router;
