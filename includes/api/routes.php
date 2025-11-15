<?php
declare(strict_types=1);

require_once __DIR__ . '/analyticscontroller.php';

$router->post('/api/analytics/events', function() {
    $controller = new AnalyticsController();
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $controller->handleEvent($input);
    header('Content-Type: application/json');
    echo json_encode($response);
});

$router->get('/api/analytics/summary', function() {
    $controller = new AnalyticsController();
    $response = $controller->getSummary($_GET);
    header('Content-Type: application/json');
    echo json_encode($response);
});

$router->get('/api/analytics/query', function() {
    $controller = new AnalyticsController();
    $response = $controller->queryEvents($_GET);
    header('Content-Type: application/json');
    echo json_encode($response);
});

$router->get('/api/analytics/export', function() {
    $controller = new AnalyticsController();
    $controller->exportData($_GET);
});
