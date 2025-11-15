<?php

require_once __DIR__ . '/../../includes/services/SchedulingService.php';

use Includes\services\SchedulingService;

$service = new SchedulingService();
$requestMethod = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts = explode('/', trim($path, '/'));

// Route the request
switch ($requestMethod) {
    case 'GET':
        if (count($parts) >= 5 && $parts[4] === 'events') {
            // GET /api/v1/scheduling/events
            $filters = [
                'status' => $_GET['status'] ?? null,
                'start_date' => $_GET['start_date'] ?? null,
                'end_date' => $_GET['end_date'] ?? null
            ];
            
            header('Content-Type: application/json');
            echo json_encode($service->getScheduledEvents($filters));
            exit;
        }
        break;
        
    case 'POST':
        if (count($parts) >= 5 && $parts[4] === 'events') {
            // POST /api/v1/scheduling/events
            $data = json_decode(file_get_contents('php://input'), true);
            
            try {
                $event = $service->createScheduledEvent(
                    $data['content_id'],
                    $data['version_id'],
                    $data['scheduled_at']
                );
                
                http_response_code(201);
                header('Content-Type: application/json');
                echo json_encode($event);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        break;
        
    case 'PUT':
        if (count($parts) >= 6 && is_numeric($parts[5])) {
            // PUT /api/v1/scheduling/events/{id}
            $data = json_decode(file_get_contents('php://input'), true);
            
            try {
                $event = $service->updateScheduledEvent(
                    $parts[5],
                    $data['scheduled_at'] ?? null,
                    $data['status'] ?? null
                );
                
                header('Content-Type: application/json');
                echo json_encode($event);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        break;
        
    case 'DELETE':
        if (count($parts) >= 6 && is_numeric($parts[5])) {
            // DELETE /api/v1/scheduling/events/{id}
            try {
                $service->cancelScheduledEvent($parts[5]);
                http_response_code(204);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
        break;
}

// No matching route found
http_response_code(404);
echo json_encode(['error' => 'Not Found']);
