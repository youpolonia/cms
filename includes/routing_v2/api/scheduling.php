<?php

require_once __DIR__ . '/../../controllers/api/schedulingcontroller.php';

$schedulingController = new SchedulingController();

// Rate limiting middleware (assuming this is included from a common file)
require_once __DIR__ . '/../middleware/rate_limiting.php';

// POST /api/schedules - Create new schedule
$router->post('/api/schedules', function() use ($schedulingController) {
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $schedulingController->create($input);
    header('Content-Type: application/json');
    echo json_encode($response);
});

// GET /api/schedules - List all schedules
$router->get('/api/schedules', function() use ($schedulingController) {
    $response = $schedulingController->list();
    header('Content-Type: application/json');
    echo json_encode($response);
});

// GET /api/schedules/{id} - Get specific schedule
$router->get('/api/schedules/(\d+)', function($id) use ($schedulingController) {
    $response = $schedulingController->get((int)$id);
    header('Content-Type: application/json');
    echo json_encode($response);
});

// PUT /api/schedules/{id} - Update schedule
$router->put('/api/schedules/(\d+)', function($id) use ($schedulingController) {
    $input = json_decode(file_get_contents('php://input'), true);
    $response = $schedulingController->update((int)$id, $input);
    header('Content-Type: application/json');
    echo json_encode($response);
});

// DELETE /api/schedules/{id} - Cancel schedule
$router->delete('/api/schedules/(\d+)', function($id) use ($schedulingController) {
    $response = $schedulingController->cancel((int)$id);
    header('Content-Type: application/json');
    echo json_encode($response);
});
