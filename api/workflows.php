<?php
require_once __DIR__ . '/../core/bootstrap.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $segments = explode('/', trim($path, '/'));

    // Route requests
    if ($segments[2] === 'versions') {
        switch ($method) {
            case 'GET':
                $versions = WorkflowVersion::getAll();
                echo json_encode($versions);
                break;
                
            case 'POST':
                $data = json_decode(file_get_contents('php://input'), true);
                $version = WorkflowVersion::create($data);
                echo json_encode($version);
                break;
                
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    } elseif ($segments[2] === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        $result = Workflow::save($data);
        echo json_encode($result);
    } elseif ($segments[2] === 'load') {
        $id = $_GET['id'] ?? null;
        $workflow = Workflow::load($id);
        echo json_encode($workflow);
    } elseif ($segments[2] === 'evaluate') {
        $triggerData = json_decode(file_get_contents('php://input'), true);
        $result = WorkflowTrigger::evaluate($triggerData);
        echo json_encode($result);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

class WorkflowVersion {
    public static function getAll() {
        // TODO: Implement database query
        return [
            ['id' => 1, 'date' => '2025-06-13', 'nodes' => []],
            ['id' => 2, 'date' => '2025-06-14', 'nodes' => []]
        ];
    }
    
    public static function create($data) {
        // TODO: Implement database insert
        return [
            'id' => 3,
            'date' => date('Y-m-d'),
            'nodes' => $data['nodes'] ?? []
        ];
    }
}

class Workflow {
    public static function save($data) {
        // TODO: Implement workflow save
        return [
            'success' => true,
            'id' => $data['id'] ?? uniqid(),
            'nodes' => $data['nodes'] ?? []
        ];
    }
    
    public static function load($id) {
        // TODO: Implement workflow load
        return [
            'id' => $id ?? 'default',
            'nodes' => []
        ];
    }
}

class WorkflowTrigger {
    public static function evaluate($triggerData) {
        // TODO: Implement trigger evaluation
        return [
            'matches' => false,
            'trigger' => $triggerData['type'] ?? 'unknown',
            'params' => $triggerData['params'] ?? []
        ];
    }
}
