<?php
require_once __DIR__ . '/aiclient.php';
require_once __DIR__ . '/httpclient.php';

class GeminiClient extends AIClient {
    private $httpClient;
    private $apiKey;
    private $model = 'gemini-2.0-flash';
    private $version = 'v1beta';

    public function __construct(array $config, int $tenantId) {
        parent::__construct($config, $tenantId);
        $this->apiKey = $config['api_key'];
        $this->model = $config['model'] ?? $this->model;
        $this->version = $config['version'] ?? $this->version;
        $this->httpClient = new HttpClient('https://generativelanguage.googleapis.com');
    }

    public function init(): void {
        $this->httpClient->setHeaders([
            'Content-Type' => 'application/json'
        ]);
    }

    public function request(string $endpoint, array $data): array {
        $url = $this->buildUrl($endpoint);
        $this->logRequest($data);
        
        $response = $this->httpClient->post($url, json_encode($data));
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response from Gemini API');
        }

        $this->logResponse($decoded);
        return $decoded;
    }

    private function buildUrl(string $endpoint): string {
        return "/{$this->version}/models/{$this->model}:{$endpoint}?key={$this->apiKey}";
    }

    public function getUsage(): array {
        return [
            'model' => $this->model,
            'version' => $this->version,
            'tenant_id' => $this->tenantId,
            'last_request' => $this->lastRequest['timestamp'] ?? null
        ];
    }
}
