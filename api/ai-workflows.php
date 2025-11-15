<?php
require_once __DIR__ . '/../core/aiclient.php';

header('Content-Type: application/json');

// Simple API key authentication
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey !== 'YOUR_SECURE_API_KEY') {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized']));
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($_SERVER['REQUEST_URI']) {
        case '/ai/content-suggestions':
            $response = handleContentSuggestions($input);
            break;
            
        case '/ai/image-generation':
            $response = handleImageGeneration($input);
            break;
            
        case '/ai/seo-optimization':
            $response = handleSeoOptimization($input);
            break;
            
        default:
            http_response_code(404);
            die(json_encode(['error' => 'Endpoint not found']));
    }
    
    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleContentSuggestions(array $input): array {
    if (empty($input['topic'])) {
        throw new Exception('Topic is required');
    }
    
    AIClient::init($input['api_key'] ?? '');
    $prompt = "Generate content suggestions about: " . $input['topic'];
    
    return [
        'suggestions' => AIClient::generateContent($prompt)
    ];
}

function handleImageGeneration(array $input): array {
    // Placeholder for image generation logic
    // Would integrate with DALL-E or similar API
    return ['status' => 'Image generation endpoint'];
}

function handleSeoOptimization(array $input): array {
    if (empty($input['content'])) {
        throw new Exception('Content is required');
    }
    
    AIClient::init($input['api_key'] ?? '');
    $prompt = "Optimize this content for SEO:\n\n" . $input['content'];
    
    return [
        'optimized' => AIClient::generateContent($prompt)
    ];
}
