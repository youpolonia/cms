<?php
class CloudAIService {
    private $apiKeys;
    private $rateLimiter;
    private $httpClient;

    public function __construct(array $apiKeys) {
        $this->apiKeys = $apiKeys;
        $this->rateLimiter = new RateLimiter();
        $this->httpClient = new HttpClient();
    }

    public function callService(string $service, string $prompt, int $timeout): string {
        $this->rateLimiter->check($service);
        
        $config = $this->getServiceConfig($service);
        $payload = [
            'prompt' => $prompt,
            'max_tokens' => $config['max_tokens'] ?? 1000,
            'temperature' => $config['temperature'] ?? 0.7
        ];

        $response = $this->httpClient->post(
            $config['endpoint'],
            $payload,
            [
                'Authorization: Bearer ' . $this->apiKeys[$service],
                'Content-Type: application/json'
            ],
            $timeout
        );

        return $this->parseResponse($response, $config['response_format']);
    }

    private function getServiceConfig(string $service): array {
        $services = [
            'deepseek-openrouter' => [
                'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
                'response_format' => 'openai',
                'max_tokens' => 1200
            ],
            'openai-gpt-4' => [
                'endpoint' => 'https://api.openai.com/v1/chat/completions',
                'response_format' => 'openai',
                'temperature' => 0.5
            ]
        ];

        return $services[$service] ?? [];
    }

    private function parseResponse(string $response, string $format): string {
        $data = json_decode($response, true);
        
        switch ($format) {
            case 'openai':
                return $data['choices'][0]['message']['content'] ?? '';
            default:
                return $data['output'] ?? '';
        }
    }
}
