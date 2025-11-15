<?php
require_once __DIR__ . '/aiclient.php';
require_once __DIR__ . '/httpclient.php';

class OpenAIClient extends AIClient {
    private $httpClient;
    private $apiKey;
    private $organization;
    private $model = 'gpt-4';

    public function __construct(array $config, int $tenantId) {
        parent::__construct($config, $tenantId);
        $this->apiKey = $config['api_key'];
        $this->organization = $config['organization'] ?? null;
        $this->model = $config['model'] ?? $this->model;
        $this->httpClient = new HttpClient('https://api.openai.com/v1');
    }

    public function init(): void {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ];
        
        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }

        $this->httpClient->setHeaders($headers);
    }

    public function request(string $endpoint, array $data): array {
        $data['model'] = $this->model;
        $this->logRequest($data);
        
        $response = $this->httpClient->post($endpoint, json_encode($data));
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response from OpenAI API');
        }

        $this->logResponse($decoded);
        return $decoded;
    }

    public function getUsage(): array {
        return [
            'model' => $this->model,
            'tenant_id' => $this->tenantId,
            'last_request' => $this->lastRequest['timestamp'] ?? null
        ];
    }
}
