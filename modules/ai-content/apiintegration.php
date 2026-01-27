<?php
/**
 * Handles API communication with AI services
 */
class ApiIntegration {
    private $apiKey;
    private $baseUrl;
    private $timeout = 30;
    private $maxRetries = 3;

    public function __construct(string $apiKey, string $baseUrl = '') {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    public function generateContent(string $prompt, array $options = []): string {
        $retryCount = 0;
        $lastError = null;

        while ($retryCount < $this->maxRetries) {
            try {
                $response = $this->makeApiRequest($prompt, $options);
                return $this->parseResponse($response);
            } catch (ApiException $e) {
                $lastError = $e;
                $retryCount++;
                sleep(1); // Simple backoff
            }
        }

        throw new ApiException(
            "API request failed after {$this->maxRetries} attempts: " . 
            ($lastError ? $lastError->getMessage() : 'Unknown error')
        );
    }

    private function makeApiRequest(string $prompt, array $options): array {
        $ch = curl_init($this->getEndpoint());
        
        $payload = [
            'prompt' => $prompt,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['max_tokens'] ?? 1000,
            'content_type' => $options['type'] ?? 'article'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_TIMEOUT => $this->timeout
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status !== 200) {
            throw new ApiException("API request failed with status $status");
        }

        return json_decode($response, true) ?? [];
    }

    private function getEndpoint(): string {
        return $this->baseUrl ?: 'https://api.openai.com/v1/completions';
    }

    private function parseResponse(array $response): string {
        if (empty($response['choices'][0]['text'])) {
            throw new ApiException('Invalid API response format');
        }
        return trim($response['choices'][0]['text']);
    }
}

class ApiException extends Exception {}
