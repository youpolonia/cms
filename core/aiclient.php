<?php
class AIClient {
    private $apiKey;
    private $baseUrl = 'https://openrouter.ai/api/v1';
    private $timeout = 30;

    public function __construct() {
        $this->apiKey = defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '';
    }

    public function execute($provider, $model, $prompt, $temperature = 0.7) {
        if (empty($this->apiKey)) {
            throw new Exception('OpenRouter API key not configured');
        }

        $payload = [
            'model' => "$provider/$model",
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => $temperature
        ];

        $ch = curl_init("{$this->baseUrl}/chat/completions");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => $this->timeout
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("API request failed: $error");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid API response');
        }

        if (isset($data['error'])) {
            throw new Exception($data['error']['message']);
        }

        return $data['choices'][0]['message']['content'] ?? '';
    }
}
