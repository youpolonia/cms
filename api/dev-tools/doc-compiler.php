<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../modules/doccompiler/doccompiler.php';

// Verify API key
if (!isset($_SERVER['HTTP_X_API_KEY']) || $_SERVER['HTTP_X_API_KEY'] !== getenv('DOC_COMPILER_API_KEY')) {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid API key']));
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['content'])) {
        throw new Exception('Missing required content parameter');
    }

    $format = $input['format'] ?? 'html';
    $result = DocCompiler::generateDocs($input['content'], $format);

    echo json_encode([
        'status' => 'success',
        'format' => $format,
        'result' => $result
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
