<?php
declare(strict_types=1);

namespace CMS\AI;

use AI\SuggestionMetrics;
require_once __DIR__ . '/suggestionmetrics.php';

class SuggestionService
{
    private const API_ENDPOINT = 'https://api.example.com/ai/suggest';
    private const MAX_RETRIES = 3;
    private const TIMEOUT = 30;

    public static function getContentSuggestions(string $content, array $context = []): array
    {
        $payload = [
            'content' => $content,
            'context' => $context,
            'timestamp' => time()
        ];

        $response = self::callAIApi(json_encode($payload));
        $suggestions = json_decode($response, true) ?? [];
        SuggestionMetrics::trackSuggestionGenerated();
        return $suggestions;
    }

    private static function callAIApi(string $payload): string
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_ENDPOINT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . self::getApiKey()
            ]
        ]);

        $retry = 0;
        do {
            $result = curl_exec($ch);
            $responseTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000;
            SuggestionMetrics::trackResponseTime($responseTime);
            
            if ($result !== false) {
                break;
            }
            SuggestionMetrics::trackError(curl_error($ch));
            $retry++;
            sleep(1);
        } while ($retry < self::MAX_RETRIES);

        curl_close($ch);
        return $result !== false ? $result : '';
    }

    private static function getApiKey(): string
    {
        return defined('AI_API_KEY') ? AI_API_KEY : '';
    }
}
