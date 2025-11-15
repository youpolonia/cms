<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/content/workflowmanager.php';

header("Content-Type: application/json");

try {
    $db = \core\Database::connection();

    $workflowManager = new WorkflowManager($db);
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Simple authentication check
    if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== API_KEY) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Route the request
    if ($pathParts[0] === 'workflows') {
        $workflowId = $pathParts[1] ?? null;
        $action = $pathParts[2] ?? null;

        switch ($requestMethod) {
            case 'GET':
                if ($workflowId === null) {
                    // GET /workflows - List all workflows
                    $workflows = $db->query("SELECT * FROM workflow_definitions")->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($workflows);
                } elseif ($action === 'status') {
                    // GET /workflows/{id}/status - Get workflow status
                    $stmt = $db->prepare("SELECT * FROM content_workflow WHERE content_id = ?");
                    $stmt->execute([$workflowId]);
                    $status = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode($status ?: ['error' => 'Not found']);
                } else {
                    // GET /workflows/{id} - Get workflow details
                    $stmt = $db->prepare("SELECT * FROM workflow_definitions WHERE id = ?");
                    $stmt->execute([$workflowId]);
                    $workflow = $stmt->fetch(PDO::FETCH_ASSOC);
                    echo json_encode($workflow ?: ['error' => 'Not found']);
                }
                break;

            case 'POST':
                if ($action === 'execute') {
                    // POST /workflows/{id}/execute - Execute workflow transition
                    $input = json_decode(file_get_contents('php://input'), true);
                    $success = $workflowManager->transitionContent(
                        (int)$workflowId,
                        (int)$input['toStateId'],
                        $_SERVER['HTTP_X_USER_ID'] ?? null,
                        $input['notes'] ?? null,
                        $input['assignedToUserId'] ?? null
                    );
                    echo json_encode(['success' => $success]);
                } else {
                    // POST /workflows - Create new workflow
                    $input = json_decode(file_get_contents('php://input'), true);
                    $stmt = $db->prepare("
                        INSERT INTO workflow_definitions 
                        (id, name, description, initial_state, states, transitions)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $success = $stmt->execute([
                        $input['id'],
                        $input['name'],
                        $input['description'] ?? null,
                        $input['initial_state'],
                        json_encode($input['states']),
                        json_encode($input['transitions'])
                    ]);
                    echo json_encode(['success' => $success, 'id' => $input['id']]);
                }
                break;

            case 'PUT':
                // PUT /workflows/{id} - Update workflow
                $input = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->prepare("
                    UPDATE workflow_definitions 
                    SET name = ?, description = ?, initial_state = ?, states = ?, transitions = ?
                    WHERE id = ?
                ");
                $success = $stmt->execute([
                    $input['name'],
                    $input['description'] ?? null,
                    $input['initial_state'],
                    json_encode($input['states']),
                    json_encode($input['transitions']),
                    $workflowId
                ]);
                echo json_encode(['success' => $success]);
                break;

            case 'DELETE':
                // DELETE /workflows/{id} - Delete workflow
                $stmt = $db->prepare("DELETE FROM workflow_definitions WHERE id = ?");
                $success = $stmt->execute([$workflowId]);
                echo json_encode(['success' => $success]);
                break;

            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log($e->getMessage());
    exit;
} catch (RuntimeException $e) {
    http_response_code(400);
    error_log($e->getMessage());
    exit;
}
