<?php
require_once __DIR__.'/../../app/Services/AI/AIServiceFactory.php';

header('Content-Type: application/json');

try {
    // Get configuration from CMS settings
    $config = [
        'openai' => [
            'key' => $_ENV['OPENAI_API_KEY'] ?? '',
            'organization' => $_ENV['OPENAI_ORG'] ?? '',
            'timeout' => 30,
            'models' => [
                'default' => 'gpt-5.2',
                'available' => ['gpt-5.2', 'gpt-4.1']
            ]
        ],
        'huggingface' => [
            'key' => $_ENV['HF_API_KEY'] ?? '',
            'timeout' => 30,
            'models' => [
                'default' => 'gpt2',
                'available' => ['gpt2', 'bloom']
            ]
        ]
    ];

    $provider = $_GET['provider'] ?? 'openai';
    $action = $_GET['action'] ?? 'generate';
    $service = AIServiceFactory::create($config[$provider] ?? [], $provider);

    switch ($action) {
        case 'generate':
            $prompt = $_POST['prompt'] ?? '';
            $params = json_decode($_POST['params'] ?? '[]', true);
            echo json_encode($service->generateContent($prompt, $params));
            break;

        case 'validate':
            $content = $_POST['content'] ?? '';
            echo json_encode($service->validateContent($content));
            break;

        case 'models':
            echo json_encode($service->getModels());
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
