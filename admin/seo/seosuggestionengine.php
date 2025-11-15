<?php
/**
 * SEO Suggestion Engine - AI-powered SEO recommendations
 */
declare(strict_types=1);

namespace Admin\Seo;

use Includes\Core\AIFeedbackLogger;

class SEOSuggestionEngine
{
    private const DEFAULT_PROVIDER = 'openai';
    private const SEO_ACTION = 'seo_analyze';
    private const LOG_TYPE = 'seo_suggestion';

    /**
     * Generate SEO suggestions for content
     */
    public static function analyzeContent(
        string $content,
        string $provider = self::DEFAULT_PROVIDER,
        ?string $sessionId = null,
        ?int $userId = null
    ): array {
        $params = [
            'meta_title_length' => 60,
            'keyword_density' => 2.5,
            'readability_score' => 8.0
        ];

        $result = self::callAIAssist(
            $provider,
            self::SEO_ACTION,
            $content,
            $params
        );

        self::logSuggestion($result, $sessionId, $userId, $provider);

        return [
            'meta_title' => $result['meta_title'] ?? '',
            'meta_description' => $result['meta_description'] ?? '',
            'keywords' => $result['keywords'] ?? [],
            'score' => $result['score'] ?? 0,
            'suggestions' => $result['suggestions'] ?? []
        ];
    }

    /**
     * Call AI Assist API endpoint
     */
    private static function callAIAssist(
        string $provider,
        string $action,
        string $prompt,
        array $params
    ): array {
        $url = '/api/ai/index.php?provider='.urlencode($provider).'&action='.urlencode($action);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'prompt' => $prompt,
            'params' => json_encode($params)
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    /**
     * Log SEO suggestion via AIFeedbackLogger
     */
    private static function logSuggestion(
        array $result,
        ?string $sessionId,
        ?int $userId,
        string $provider
    ): void {
        AIFeedbackLogger::logFeedback([
            'type' => self::LOG_TYPE,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'model' => $provider,
            'interaction_id' => uniqid('seo_', true),
            'data' => [
                'score' => $result['score'] ?? 0,
                'keywords' => $result['keywords'] ?? [],
                'suggestions' => $result['suggestions'] ?? []
            ]
        ]);
    }
}
