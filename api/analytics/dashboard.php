<?php
require_once __DIR__ . '/../../services/analyticsprocessor.php';
require_once __DIR__ . '/../../core/database.php';

header('Content-Type: application/json');

try {
    $pdo = \core\Database::connection();
    $processor = new AnalyticsProcessor($pdo);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // Get dashboard views
            $stmt = $pdo->query(
                "SELECT * FROM analytics_dashboard_views 
                 ORDER BY created_at DESC LIMIT 10"
            );
            $views = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            echo json_encode(['data' => $views]);
            break;

        case 'POST':
            // Create new dashboard view
            $input = json_decode(file_get_contents('php://input'), true);
            $required = ['name', 'config'];
            
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    throw new \InvalidArgumentException("Missing required field: $field");
                }
            }

            $stmt = $pdo->prepare(
                "INSERT INTO analytics_dashboard_views 
                 (name, config, created_at) 
                 VALUES (:name, :config, NOW())"
            );
            $stmt->execute([
                ':name' => $input['name'],
                ':config' => json_encode($input['config'])
            ]);

            echo json_encode([
                'status' => 'success',
                'id' => $pdo->lastInsertId()
            ]);
            break;

        case 'PUT':
            // Update existing view
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input['id'])) {
                throw new \InvalidArgumentException("Missing view ID");
            }

            $stmt = $pdo->prepare(
                "UPDATE analytics_dashboard_views 
                 SET config = :config, updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                ':id' => $input['id'],
                ':config' => json_encode($input['config'])
            ]);

            echo json_encode(['status' => 'success']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
