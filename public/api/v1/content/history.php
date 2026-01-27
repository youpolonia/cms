<?php
require_once __DIR__ . '/../../../../core/bootstrap.php';

/**
 * Content History API Endpoint
 * Version 1
 */
require_once __DIR__ . '/../../../core/bootstrap.php';
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
            // Get content history with filters
            $contentId = $_GET['content_id'] ?? null;
            $action = $_GET['action'] ?? null;
            $userId = $_GET['user_id'] ?? null;
            $limit = min(100, (int)($_GET['limit'] ?? 50));

            $query = "SELECT * FROM content_history WHERE 1=1";
            $params = [];

            if ($contentId) {
                $query .= " AND content_id = ?";
                $params[] = $contentId;
            }
            if ($action) {
                $query .= " AND action = ?";
                $params[] = $action;
            }
            if ($userId) {
                $query .= " AND user_id = ?";
                $params[] = $userId;
            }

            $query .= " ORDER BY created_at DESC LIMIT ?";
            $params[] = $limit;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $handler->respond([
                'success' => true,
                'data' => $history
            ]);
            break;

        case 'POST':
            require_once __DIR__ . '/../../../../core/csrf.php';
            csrf_validate_or_403();

            // Add history entry (typically called by other endpoints)
            $data = $handler->getRequestData();
            
            if (empty($data['content_id'])) {
                $handler->error('Content ID required');
            }
            if (empty($data['action'])) {
                $handler->error('Action required');
            }

            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("INSERT INTO content_history 
                    (content_id, action, old_value, new_value, user_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $data['content_id'],
                    $data['action'],
                    $data['old_value'] ?? null,
                    $data['new_value'] ?? null,
                    $_SESSION['user_id'] ?? null
                ]);
                
                $pdo->commit();
                $handler->respond([
                    'success' => true,
                    'message' => 'History recorded'
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
