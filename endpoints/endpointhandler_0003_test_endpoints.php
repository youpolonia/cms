<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}

class Migration_0003_Test_Endpoints {
    public static function handleRequest() {
        header('Content-Type: application/json');
        
        try {
            $pdo = self::getDatabaseConnection();
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            if (preg_match('#^/migrate/0003$#', $path)) {
                Migration_002_Create_Status_Transitions_Table::apply($pdo);
                $result = Migration_0003_Test_Status_Transitions::testMigration($pdo);
                echo json_encode($result);
            } 
            elseif (preg_match('#^/rollback/0003$#', $path)) {
                $result = Migration_0003_Test_Status_Transitions::testRollback($pdo);
                echo json_encode($result);
            }
            else {
                throw new Exception('Invalid endpoint');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private static function getDatabaseConnection(): PDO {
        require_once __DIR__ . '/../../config.php';
        require_once __DIR__ . '/../../core/database.php';
        return \core\Database::connection();
    }
}

// Handle request if executed directly
if (php_sapi_name() !== 'cli' && isset($_SERVER['REQUEST_METHOD'])) {
    Migration_0003_Test_Endpoints::handleRequest();
}
