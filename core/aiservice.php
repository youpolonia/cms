<?php
require_once __DIR__ . '/aiclient.php';
require_once __DIR__ . '/openaiclient.php';
require_once __DIR__ . '/geminiclient.php';

class AIService {
    private $clients = [];
    private $activeClient;
    private $tenantId;

    public function __construct(int $tenantId) {
        $this->tenantId = $tenantId;
    }

    public function addClient(string $provider, array $config): void {
        switch ($provider) {
            case 'openai':
                $this->clients[$provider] = new OpenAIClient($config, $this->tenantId);
                break;
            case 'gemini':
                $this->clients[$provider] = new GeminiClient($config, $this->tenantId);
                break;
            default:
                throw new InvalidArgumentException("Unsupported AI provider: $provider");
        }

        if (!isset($this->activeClient)) {
            $this->activeClient = $provider;
        }
    }

    public function setActiveClient(string $provider): void {
        if (!isset($this->clients[$provider])) {
            throw new RuntimeException("Client $provider not initialized");
        }
        $this->activeClient = $provider;
    }

    public function getClient(string $provider = null): AIClient {
        $provider = $provider ?? $this->activeClient;
        if (!isset($this->clients[$provider])) {
            throw new RuntimeException("Client $provider not initialized");
        }
        return $this->clients[$provider];
    }

    public function request(string $endpoint, array $data, string $provider = null): array {
        return $this->getClient($provider)->request($endpoint, $data);
    }

    public function getUsage(string $provider = null): array {
        return $this->getClient($provider)->getUsage();
    }
}
