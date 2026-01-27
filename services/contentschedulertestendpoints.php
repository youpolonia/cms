<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once __DIR__ . '/../core/database.php';
/**
 * ContentScheduler Test Endpoints
 * Web-accessible endpoints for testing ContentScheduler functionality
 */

class ContentSchedulerTestEndpoints {
    /**
     * Verify manual content scheduling
     * Endpoint: /services/ContentSchedulerTestEndpoints.php?action=verify_scheduling
     */
    public static function verifyScheduling() {
        header('Content-Type: application/json');

        try {
            require_once __DIR__ . '/../core/contentscheduler.php';
            $pdo = \core\Database::connection();
            $scheduler = new ContentScheduler($pdo);
            $result = $scheduler->publishScheduledContent();
            
            echo json_encode([
                'status' => 'success',
                'processed_items' => count($result),
                'data' => $result
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Simulate error scenarios 
     * Endpoint: /services/ContentSchedulerTestEndpoints.php?action=simulate_errors
     */
    public static function simulateErrors() {
        header('Content-Type: application/json');
        
        try {
            // Force error conditions
            $_SERVER['TEST_ERROR_MODE'] = true;
            require_once __DIR__ . '/../core/contentscheduler.php';
            $pdo = \core\Database::connection();
            $scheduler = new ContentScheduler($pdo);
            $result = $scheduler->publishScheduledContent();
            
            echo json_encode([
                'status' => 'success',
                'errors_triggered' => count($result['errors']),
                'data' => $result
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Inspect database state
     * Endpoint: /services/ContentSchedulerTestEndpoints.php?action=inspect_db
     */
    public static function inspectDatabase() {
        header('Content-Type: application/json');
        
        try {
            $pdo = \core\Database::connection();
            
            $query = "SELECT * FROM content_items WHERE scheduled_publish_at IS NOT NULL";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            
            echo json_encode([
                'status' => 'success',
                'scheduled_items' => $stmt->rowCount(),
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Simulate cron job execution
     * Endpoint: /services/ContentSchedulerTestEndpoints.php?action=simulate_cron
     */
    public static function simulateCron() {
        header('Content-Type: application/json');
        
        try {
            require_once __DIR__ . '/../core/contentscheduler.php';
            $pdo = \core\Database::connection();
            $scheduler = new ContentScheduler($pdo);

            // Simulate cron running every minute for 5 minutes
            $results = [];
            for ($i = 0; $i < 5; $i++) {
                $results[] = $scheduler->publishScheduledContent();
                sleep(1); // Simulate time passing
            }
            
            echo json_encode([
                'status' => 'success',
                'cron_simulations' => count($results),
                'data' => $results
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}

// Route requests based on action parameter
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'verify_scheduling':
            ContentSchedulerTestEndpoints::verifyScheduling();
            break;
        case 'simulate_errors':
            ContentSchedulerTestEndpoints::simulateErrors();
            break;
        case 'inspect_db':
            ContentSchedulerTestEndpoints::inspectDatabase();
            break;
        case 'simulate_cron':
            ContentSchedulerTestEndpoints::simulateCron();
            break;
        default:
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Action parameter required']);
}
