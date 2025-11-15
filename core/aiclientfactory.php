<?php
class AIClientFactory {
    public static function create(string $type, array $config, int $tenantId): AIClient {
        switch ($type) {
            case 'openai':
                require_once __DIR__ . '/openaiclient.php';
                return new OpenAIClient($config, $tenantId);
            case 'gemini':
                require_once __DIR__ . '/geminiclient.php';
                return new GeminiClient($config, $tenantId);
            default:
                throw new InvalidArgumentException("Unsupported AI client type: $type");
        }
    }

    public static function getAvailableClients(): array {
        return [
            'openai' => 'OpenAI API',
            'gemini' => 'Google Gemini'
        ];
    }
}
