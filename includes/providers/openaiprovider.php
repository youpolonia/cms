<?php
declare(strict_types=1);

class OpenAIProvider implements AIIntegrationProvider {
    private array $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function process(array $input): array {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->config['api_key']
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'model' => $this->config['model'],
                'messages' => $input['messages'],
                'temperature' => $this->config['temperature'],
                'max_tokens' => $this->config['max_tokens']
            ])
        ]);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('OpenAI API error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new RuntimeException('OpenAI API error: HTTP ' . $httpCode);
        }

        return json_decode($response, true);
    }
}
