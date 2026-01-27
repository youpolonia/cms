<?php
namespace CMS\PageBuilder;

use CMS\AI\WorkerMonitoringService;

class AIContentService {
    const CONTENT_MODEL = 'gpt-4.1';
    const MAX_TOKENS = 1000;
    const RATE_LIMIT = 5; // Requests per minute
    
    public static function generateContent(string $brief, array $params = []): array {
        self::validateInput($brief);
        self::checkRateLimit();

        $payload = [
            'model' => self::CONTENT_MODEL,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a professional content designer. Generate safe, engaging web content based on user briefs.'
                ],
                [
                    'role' => 'user', 
                    'content' => self::filterContent($brief)
                ]
            ],
            'max_tokens' => $params['max_tokens'] ?? self::MAX_TOKENS
        ];

        try {
            $response = self::callAIAPI('/chat/completions', $payload);
            $content = $response['choices'][0]['message']['content'];
            
            WorkerMonitoringService::logAIMetric(
                'content_generation',
                strlen($brief),
                md5($content)
            );

            return [
                'content' => self::postProcessContent($content),
                'usage' => $response['usage']
            ];
        } catch (\Exception $e) {
            WorkerMonitoringService::logError('AI_CONTENT_FAIL', $e->getMessage());
            throw $e;
        }
    }

    private static function callAIAPI(string $endpoint, array $payload): array {
        $apiKey = self::getSecureApiKey();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1' . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception("AI API Error: HTTP $httpCode - " . $response);
        }

        return json_decode($response, true);
    }

    private static function getSecureApiKey(): string {
        $key = WorkerMonitoringService::getSecret('OPENAI_API_KEY');
        if (empty($key)) {
            throw new \Exception("AI API key not configured");
        }
        return $key;
    }

    private static function validateInput(string $input): void {
        if (strlen($input) < 10) {
            throw new \InvalidArgumentException("Input too short - minimum 10 characters required");
        }
    }

    private static function filterContent(string $content): string {
        // Remove sensitive patterns
        $content = preg_replace('/\b(ssn|credit card|password)\b/i', '[REDACTED]', $content);
        return strip_tags($content);
    }

    private static function postProcessContent(string $content): string {
        // Ensure safe HTML output
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);
        return $purifier->purify($content);
    }

    private static function checkRateLimit(): void {
        $lastMinuteCount = WorkerMonitoringService::getCounter('ai_content_reqs', 60);
        if ($lastMinuteCount >= self::RATE_LIMIT) {
            throw new \RuntimeException("Rate limit exceeded - max " . self::RATE_LIMIT . " requests per minute");
        }
        WorkerMonitoringService::incrementCounter('ai_content_reqs');
    }
}
