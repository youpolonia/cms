<?php
/**
 * AI Content Generator Module Initialization
 */

// Register module with CMS
$cms->registerModule('ai-content', [
    'name' => 'AI Content Generator',
    'version' => '1.0.0',
    'dependencies' => ['auth', 'content'],
    'routes' => [
        '/api/ai/generate' => 'handleGenerateRequest'
    ]
]);

// Initialize services
$generator = new AIContentGenerator([
    'api_key' => $config['ai']['api_key'] ?? '',
    'provider' => $config['ai']['provider'] ?? 'openai',
    'fallback_content' => $config['ai']['fallback_content'] ?? '',
    'validation_rules' => $config['ai']['validation_rules'] ?? []
]);

$requestHandler = new RequestHandler($generator);

// Register CMS hooks
$cms->addHook('content.create.before', function(array $contentData) use ($generator) {
    if ($contentData['generate_ai_content'] ?? false) {
        $contentData['body'] = $generator->generate($contentData['prompt'], [
            'type' => $contentData['type'] ?? 'article'
        ]);
    }
    return $contentData;
});

// API endpoint handler
function handleGenerateRequest(): array {
    global $requestHandler;
    return $requestHandler->handleRequest($_POST);
}
