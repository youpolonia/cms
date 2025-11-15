<?php
require_once __DIR__ . '/../controllers/statustransitionscontroller.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Status transitions API endpoints
    if ($pathParts[1] === 'api' && $pathParts[2] === 'status-transitions') {
        switch ($method) {
            case 'POST':
                // Create new status transition
                $input = json_decode(file_get_contents('php://input'), true);
                if (!isset($input['entity_type'], $input['entity_id'], $input['from_status'], $input['to_status'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing required fields']);
                    break;
                }

                $result = StatusTransitionsController::createTransition(
                    $input['entity_type'],
                    $input['entity_id'],
                    $input['from_status'],
                    $input['to_status'],
                    $input['reason'] ?? null
                );
                echo json_encode(['success' => $result]);
                break;

            case 'GET':
                // Get transitions for entity or specific transition
                if (!isset($pathParts[3])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing entity type or transition ID']);
                    break;
                }

                if (is_numeric($pathParts[3])) {
                    // Get specific transition by ID
                    $transition = StatusTransitionsController::getTransitionById($pathParts[3]);
                    echo json_encode($transition);
                } else {
                    // Get transitions for entity (entityType, entityId)
                    if (!isset($pathParts[4])) {
                        http_response_code(400);
                        echo json_encode(['error' => 'Missing entity ID']);
                        break;
                    }
                    $transitions = StatusTransitionsController::getTransitionsForEntity(
                        $pathParts[3],
                        $pathParts[4]
                    );
                    echo json_encode($transitions);
                }
                break;

            case 'DELETE':
                // Delete transition (admin only)
                if (!isset($pathParts[3])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing transition ID']);
                    break;
                }
                $result = StatusTransitionsController::deleteTransition($pathParts[3]);
                echo json_encode(['success' => $result]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
