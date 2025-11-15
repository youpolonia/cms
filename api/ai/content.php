<?php
require_once __DIR__ . '/../../core/aicontentgenerator.php';

header('Content-Type: application/json');

try {
    // Only allow POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    // Get and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input', 400);
    }

    // Required parameters
    $required = ['model_id', 'content_type', 'parameters'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            throw new Exception("Missing required field: $field", 400);
        }
    }

    // Process request
    $result = AIContentGenerator::generate(
        $input['model_id'],
        $input['content_type'],
        $input['parameters'],
        $input['prompt'] ?? null
    );

    // Return success response
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'data' => $result['data']
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
