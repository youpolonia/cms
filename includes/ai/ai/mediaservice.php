<?php
namespace CMS\AI;

class MediaService {
    const AI_PROVIDER = 'OPENAI';
    const DEFAULT_MODEL = 'dall-e-3';
    
    public static function generateImage(string $prompt, array $params = []): array {
        $payload = [
            'model' => $params['model'] ?? self::DEFAULT_MODEL,
            'prompt' => $prompt,
            'n' => $params['count'] ?? 1,
            'size' => $params['size'] ?? '1024x1024',
            'quality' => $params['quality'] ?? 'standard'
        ];

        $response = self::callAIAPI('/images/generations', $payload);
        
        WorkerMonitoringService::logAIMetric(
            'image_generation',
            strlen($prompt),
            $response['data'][0]['revised_prompt'] ?? $prompt
        );

        return $response['data'];
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
            throw new \Exception("AI API Error: HTTP $httpCode - $response");
        }

        return json_decode($response, true);
    }

    private static function getSecureApiKey(): string {
        // Integrated with worker monitoring system
        $key = WorkerMonitoringService::getSecret('OPENAI_API_KEY');
        
        if (empty($key)) {
            throw new \Exception("AI API key not configured");
        }
        
        return $key;
    }
}
