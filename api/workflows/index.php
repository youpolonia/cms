<?php
require_once __DIR__.'/../../config/bootstrap.php';
require_once __DIR__ . '/workflowmanager.php';
require_once __DIR__ . '/workflowvalidator.php';

header('Content-Type: application/json');

try {
    $manager = new WorkflowManager();
    $validator = new WorkflowValidator();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // Route requests
    if ($pathParts[2] === 'workflows') {
        $id = $pathParts[3] ?? null;
        
        switch ($method) {
            case 'GET':
                if ($id) {
                    echo json_encode($manager->getWorkflow($id));
                } else {
                    echo json_encode($manager->listWorkflows());
                }
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $validator->validateCreate($data);
                echo json_encode($manager->createWorkflow($data));
                break;
                
            case 'PUT':
                $data = json_decode(file_get_contents('php://input'), true);
                $validator->validateUpdate($data);
                echo json_encode($manager->updateWorkflow($id, $data));
                break;
                
            case 'DELETE':
                echo json_encode($manager->deleteWorkflow($id));
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
