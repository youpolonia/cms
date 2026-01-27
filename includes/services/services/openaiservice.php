<?php

namespace Includes\Services;

use Includes\Config\ConfigLoader;
use Includes\Core\Logger\LoggerFactory;

class OpenAIService
{
    protected $apiKey;
    protected $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected $model = 'gpt-4.1-mini';
    protected $temperature = 0.7;
    protected $timeout = 30;
    protected $logger;

    public function __construct()
    {
        $config = ConfigLoader::get('openai');
        $this->apiKey = $config['key'] ?? '';
        $this->timeout = $config['timeout'] ?? 30;
        $this->logger = LoggerFactory::getLogger('ai_service');
    }

    public function generateRecommendations(string $prompt): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a content recommendation assistant. Provide 3-5 relevant content recommendations based on the user behavior patterns and context.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $response = $this->makeApiRequest($messages);

        if (isset($response['choices'][0]['message']['content'])) {
            return $this->parseRecommendations($response['choices'][0]['message']['content']);
        }

        return [];
    }

    protected function makeApiRequest(array $messages): array
    {
        if (empty($this->apiKey)) {
            $this->logger->error('API key not configured', ['service' => 'openai']);
            throw new \RuntimeException('AI service not configured');
        }

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        $data = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => 500
        ];

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                $this->logger->error("API request failed", [
                    'service' => 'openai',
                    'code' => $httpCode,
                    'response' => substr($response, 0, 200)
                ]);
                throw new \RuntimeException('AI service request failed');
            }

            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            $this->logger->error("API connection error", [
                'service' => 'openai',
                'error' => $e->getMessage(),
                'trace' => substr($e->getTraceAsString(), 0, 200)
            ]);
            throw $e;
        }
    }

    protected function parseRecommendations(string $content): array
    {
        $pattern = '/\d+\.\s*(.+)/';
        preg_match_all($pattern, $content, $matches);
        
        return $matches[1] ?? [];
    }

    public function validateApiKey(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        try {
            $response = $this->makeApiRequest([
                [
                    'role' => 'user',
                    'content' => 'Test connection'
                ]
            ]);
            return isset($response['choices']);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function generateContentIdeas(string $topic, int $count = 5): array
    {
        $prompt = "Generate $count content ideas about: $topic";
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a content idea generator. Provide creative, engaging content ideas.'
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ];

        $response = $this->makeApiRequest($messages);
        return $this->parseIdeas($response['choices'][0]['message']['content'] ?? '');
    }

    protected function parseIdeas(string $content): array
    {
        return array_filter(
            array_map('trim', explode("\n", $content)),
            fn($line) => !empty($line)
        );
    }

    public function summarizeText(string $text, int $maxLength = 200): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Summarize the following text in under '.$maxLength.' characters'
            ],
            [
                'role' => 'user',
                'content' => $text
            ]
        ];

        $response = $this->makeApiRequest($messages);
        return $response['choices'][0]['message']['content'] ?? '';
    }
}
