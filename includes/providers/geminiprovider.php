<?php
declare(strict_types=1);

class GeminiProvider implements AIIntegrationProvider {
    private array $config;

    public function __construct(array $config) {
        $this->config = $config;
    }

    public function process(array $input): array {
        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/' . 
                       $this->config['model'] . ':generateContent');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'contents' => $input['messages'],
                'generationConfig' => [
                    'temperature' => $this->config['temperature'],
                    'maxOutputTokens' => $this->config['max_output_tokens']
                ]
            ])
        ]);

        $url = $ch->url . '?key=' . $this->config['api_key'];
        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new RuntimeException('Gemini API error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode !== 200) {
            throw new RuntimeException('Gemini API error: HTTP ' . $httpCode);
        }

        return json_decode($response, true);
    }
}
