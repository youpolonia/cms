<?php
// Start output buffering with error handling
if (!ob_start()) {
    error_log('Failed to start output buffering');
    http_response_code(500);
    die(json_encode(['error' => 'Internal server error']));
}

require_once __DIR__ . '/../config.php';

require_once __DIR__ . '/tenantmanager.php';
require_once __DIR__ . '/router.php';
require_once __DIR__ . '/requesthandler.php';

try {
    // Initialize database connection
    $pdo = \core\Database::connection();

    // Add sample routes
    Router::addRoute('GET', '/api/users', function($request, $tenantId) use ($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Validate JSON encoding
        $json = json_encode($data);
        if ($json === false) {
            error_log("JSON encode error: " . json_last_error_msg());
            return [
                'status' => 500,
                'body' => json_encode(['error' => 'Internal server error'])
            ];
        }
        
        return [
            'status' => 200,
            'body' => $json
        ];
    });

    // Handle request
    $response = RequestHandler::handle($_SERVER, ['body' => file_get_contents('php://input')], $pdo);

    // Set required headers
    $response['headers']['Content-Type'] = 'application/json';
    $response['headers']['Content-Length'] = strlen($response['body']);

    // Send response
    http_response_code($response['status']);
    foreach ($response['headers'] as $name => $value) {
        header("$name: $value");
    }
    echo $response['body'];

} catch (Exception $e) {
    // Clean output buffer before error response
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    
    error_log(sprintf(
        "API Gateway Error: %s\nStack: %s",
        $e->getMessage(),
        $e->getTraceAsString()
    ));
    
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal server error']);
    exit;
}

// Clean output buffer
while (ob_get_level() > 0) {
    ob_end_flush();
}
