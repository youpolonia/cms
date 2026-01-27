<?php
require_once __DIR__.'/../../includes/middleware/apiauthmiddleware.php';
require_once __DIR__ . '/../../admin/controllers/aicontentenhancercontroller.php';

class SystemRoutes {
    public static function handleAIEnhancement($requestUri) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $requestUri === '/api/ai/enhance') {
            try {
                $authMiddleware = new ApiAuthMiddleware();
                $authMiddleware->handle();
                
                $input = json_decode(file_get_contents('php://input'), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Invalid JSON input');
                }
                
                if (empty($input['provider']) || empty($input['action']) || empty($input['content'])) {
                    throw new Exception('Missing required fields');
                }
                
                $config = [
                    'api_key' => $_ENV['AI_API_KEY'] ?? '',
                    'model' => $_ENV['AI_MODEL'] ?? 'gpt-4.1-mini',
                    'temperature' => 0.7,
                    'max_tokens' => 1000
                ];
                
                $enhancer = new AIContentEnhancerController($input['provider'], $config);
                
                $result = match($input['action']) {
                    'seo' => $enhancer->generateMeta(
                        $input['title'] ?? '',
                        $input['content'],
                        $input['options']['language'] ?? 'en'
                    ),
                    default => throw new Exception('Unsupported action')
                };
                
                header('Content-Type: application/json');
                echo json_encode($result);
                exit;
            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'code' => 'ERR_AI_ENHANCE',
                    'message' => $e->getMessage()
                ]);
                exit;
            }
        }
    }
    
    public static function route() {
        $requestUri = $_SERVER['REQUEST_URI'];
        self::handleAIEnhancement($requestUri);
    }
}
