<?php
require_once __DIR__ . '/../../../../config.php';

/**
 * Content Relations API Endpoint
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
            // Get content relationships
            $contentId = $_GET['content_id'] ?? null;
            if (!$contentId) {
                $handler->error('Content ID required');
            }

            $type = $_GET['type'] ?? null;
            $query = "SELECT * FROM content_relations WHERE content_id = ?";
            $params = [$contentId];

            if ($type) {
                $query .= " AND relation_type = ?";
                $params[] = $type;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $relations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $handler->respond([
                'success' => true,
                'data' => $relations
            ]);
            break;

        case 'POST':
            require_once __DIR__ . '/../../../../core/csrf.php';
            csrf_validate_or_403();

            // Create/update relationship
            $data = $handler->getRequestData();
            
            if (empty($data['content_id'])) {
                $handler->error('Content ID required');
            }
            if (empty($data['related_id'])) {
                $handler->error('Related content ID required');
            }
            if (empty($data['relation_type'])) {
                $handler->error('Relation type required');
            }

            $pdo->beginTransaction();
            
            try {
                // Check if relationship exists
                $stmt = $pdo->prepare("SELECT id FROM content_relations 
                    WHERE content_id = ? AND related_id = ? AND relation_type = ?");
                $stmt->execute([
                    $data['content_id'],
                    $data['related_id'],
                    $data['relation_type']
                ]);
                $exists = $stmt->fetchColumn();

                if ($exists) {
                    // Update existing
                    $stmt = $pdo->prepare("UPDATE content_relations SET 
                        weight = ?, updated_at = NOW() 
                        WHERE id = ?");
                    $stmt->execute([
                        $data['weight'] ?? 1,
                        $exists
                    ]);
                } else {
                    // Create new
                    $stmt = $pdo->prepare("INSERT INTO content_relations 
                        (content_id, related_id, relation_type, weight, created_at) 
                        VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([
                        $data['content_id'],
                        $data['related_id'],
                        $data['relation_type'],
                        $data['weight'] ?? 1
                    ]);
                }
                
                $pdo->commit();
                $handler->respond([
                    'success' => true,
                    'message' => 'Relationship processed'
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
