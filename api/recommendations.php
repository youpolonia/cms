<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/ai/SuggestionService.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Invalid request method');
    }

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON payload');
    }

    if (empty($data['content'])) {
        throw new RuntimeException('Content parameter is required');
    }

    $suggestions = CMS\AI\SuggestionService::getContentSuggestions(
        $data['content'],
        $data['context'] ?? []
    );

    echo json_encode([
        'status' => 'success',
        'suggestions' => $suggestions
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
