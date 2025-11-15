<?php

class OpenAIProvider implements AIProviderInterface {
    private string $apiKey;
    private string $organization;
    private array $config;
    private array $tenantOverrides = [];

    public function __construct(array $config) {
        $this->apiKey = $config['key'] ?? '';
        $this->organization = $config['organization'] ?? '';
        $this->config = $config;
    }

    public function generateContent(
        string $template,
        array $variables = [],
        array $options = []
    ): string {
        $tenantId = $options['tenant_id'] ?? null;
        $model = $this->getModelForTenant($options['model'] ?? null, $tenantId);
        $maxTokens = $this->getMaxTokensForModel($model, $tenantId);

        // Interpolate template variables
        $prompt = $template;
        foreach ($variables as $key => $value) {
            $prompt = str_replace("{{{$key}}}", $value, $prompt);
        }

        $response = $this->makeRequest('chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $options['temperature'] ?? 0.7
        ]);

        $this->trackUsage($tenantId, $model, $response['usage']['prompt_tokens'], $response['usage']['completion_tokens']);

        return $response['choices'][0]['message']['content'];
    }

    public function getConfig(): array {
        return $this->config;
    }

    public function getRemainingQuota(): int {
        // Default implementation - would connect to usage tracking system
        return $this->config['monthly_limit'] ?? 1000000;
    }

    public function getUsageStats(): array {
        // Default implementation - would connect to usage tracking system
        return [
            'monthly_used' => 0,
            'monthly_limit' => $this->getRemainingQuota(),
            'total_used' => 0
        ];
    }

    public function isAvailable(): bool {
        return !empty($this->apiKey) && !empty($this->organization);
    }

    public function getUsageCost(
        string $model,
        int $inputTokens,
        int $outputTokens,
        ?int $tenantId = null
    ): float {
        $modelConfig = $this->getModelConfig($model, $tenantId);
        return ($inputTokens * $modelConfig['cost_per_input_token']) 
             + ($outputTokens * $modelConfig['cost_per_output_token']);
    }

    public function getAvailableModels(?int $tenantId = null): array {
        $models = $this->config['models']['available'];
        
        if ($tenantId && isset($this->tenantOverrides[$tenantId]['models'])) {
            $models = array_merge($models, $this->tenantOverrides[$tenantId]['models']);
        }

        return $models;
    }

    public function getRateLimits(?int $tenantId = null): array {
        $limits = $this->config['rate_limits'];
        
        if ($tenantId && isset($this->tenantOverrides[$tenantId]['rate_limits'])) {
            $limits = array_merge($limits, $this->tenantOverrides[$tenantId]['rate_limits']);
        }

        return $limits;
    }

    public function fineTuneModel(
        string $baseModel,
        array $trainingData,
        array $parameters = [],
        ?int $tenantId = null
    ): string {
        if (!in_array($baseModel, $this->config['fine_tuning']['base_models'])) {
            throw new InvalidArgumentException("Model {$baseModel} not available for fine-tuning");
        }

        $response = $this->makeRequest('fine-tunes', [
            'training_file' => $this->prepareTrainingData($trainingData),
            'model' => $baseModel,
            'suffix' => $parameters['suffix'] ?? 'custom-model'
        ]);

        return $response['id'];
    }

    private function getModelForTenant(?string $model, ?int $tenantId): string {
        if ($tenantId && isset($this->tenantOverrides[$tenantId]['default_model'])) {
            return $this->tenantOverrides[$tenantId]['default_model'];
        }
        return $model ?? $this->config['models']['default'];
    }

    private function trackUsage(int $tenantId, string $model, int $inputTokens, int $outputTokens): void {
        // Implementation would connect to database usage tracking
    }

    private function makeRequest(string $endpoint, array $data): array {
        $ch = curl_init("https://api.openai.com/v1/{$endpoint}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'OpenAI-Organization: ' . $this->organization
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('OpenAI API error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            throw new RuntimeException("OpenAI API returned HTTP {$httpCode}");
        }

        return json_decode($response, true);
    }
}
