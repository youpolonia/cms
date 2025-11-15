<?php
require_once __DIR__.'/../../includes/bootstrap.php';

use Includes\Auth\Middleware\WorkerAuthenticate;
use Includes\RoutingV2\Request;
use Includes\RoutingV2\Response as RoutingResponse;

// Initialize database connection
$db = \core\Database::connection();

// Initialize auth service
$auth = new \Includes\Auth\AuthService();

// Create request object from globals
$request = Request::fromGlobals();

// Apply worker authentication middleware
$middleware = new WorkerAuthenticate($auth, $db);
$response = $middleware->process($request, function($request) use ($db) {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required = ['worker_id', 'severity', 'message', 'component'];
        foreach($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Missing required field: $field");
            }
        }

        $stmt = $db->prepare("
            INSERT INTO error_logs 
            (worker_id, severity, message, component, details, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['worker_id'],
            $data['severity'],
            $data['message'],
            $data['component'],
            $data['details'] ?? null
        ]);

        return new RoutingResponse(201, ['status' => 'logged']);
        
    } catch(\Exception $e) {
        error_log("Error logging failed: ".$e->getMessage());
        return new RoutingResponse(400, ['error' => $e->getMessage()]);
    }
});

return $response;
