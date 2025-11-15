<?php
require_once __DIR__ . '/../../includes/tenant/tenantaiconfig.php';
require_once __DIR__ . '/../../includes/ai/AIErrorHandler.php';
require_once __DIR__ . '/../../includes/ai/AIClient.php';

header('Content-Type: application/json');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Method not allowed', 405);
    }

    // Parse and validate input
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON input', 400);
    }

    $content = $input['content'] ?? '';
    $length = min($input['length'] ?? 500, 2000); // Limit to 2000 chars
    $style = $input['style'] ?? 'neutral';
    
    if (empty($content)) {
        throw new RuntimeException('Content parameter is required', 400);
    }

    // Execute with retry and fallback logic
    $result = AIErrorHandler::executeWithRetry(
        function($provider) use ($content, $length, $style) {
            $config = TenantAIConfig::getProviderConfig($provider);
            return AIClient::callAIApi($config, $content, $length, $style);
        },
        TenantAIConfig::getConfig()['default_provider']
    );

    // Return successful response
    echo json_encode([
        'success' => true,
        'result' => $result
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code($e->getCode() ?: 400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'errors' => AIErrorHandler::getErrorLog()
    ]);
}
