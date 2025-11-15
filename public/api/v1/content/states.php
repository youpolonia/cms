<?php
require_once __DIR__ . '/../../../../config.php';

/**
 * Content States API Endpoint
 * Version 1
 */
require_once __DIR__ . '/../../../../includes/bootstrap.php';
require_once __DIR__ . '/../../../../core/api/apihandler.php';
require_once __DIR__ . '/../../../../core/database.php';
require_once __DIR__ . '/../../../../auth/workerauthcontroller.php';

header('Content-Type: application/json');

// Authentication check
if (!\Includes\Auth\WorkerAuthController::validateApiRequest()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get database connection
$pdo = \core\Database::connection();
$handler = new ApiHandler();

try {
    switch ($handler->getMethod()) {
        case 'GET':
            // Get current content states
            $stmt = $pdo->query("SELECT DISTINCT state FROM content");
            $states = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $handler->respond([
                'success' => true,
                'data' => $states
            ]);
            break;

        case 'POST':
            require_once __DIR__ . '/../../../../core/csrf.php';
            csrf_validate_or_403();

            // Update content state
            $data = $handler->getRequestData();
            
            if (empty($data['content_id'])) {
                $handler->error('Content ID required');
            }
            if (empty($data['state'])) {
                $handler->error('State required');
            }

            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("UPDATE content SET state = ? WHERE id = ?");
                $stmt->execute([$data['state'], $data['content_id']]);
                
                $pdo->commit();
                $handler->respond([
                    'success' => true,
                    'message' => 'Content state updated'
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                $handler->error('Database operation failed: ' . $e->getMessage(), 500);
            }
            break;

        default:
            $handler->error('Method not allowed', 405);
    }
} catch (Exception $e) {
    $handler->error($e->getMessage(), 500);
}
