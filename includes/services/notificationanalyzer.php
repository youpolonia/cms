<?php

namespace Includes\Services;

use Includes\Services\AIClient;
use Includes\Config\ConfigLoader;

/**
 * Analyzes notification content using AI services
 */
class NotificationAnalyzer {
    private static $cache = [];
    private static $cacheTtl = 3600; // 1 hour

    /**
     * Process notification text through AI analysis
     */
    public static function analyze(string $text): array {
        $cacheKey = md5($text);
        
        // Check cache first
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $analysis = self::processText($text);
        $suggestions = self::generateSuggestions($text);

        $result = [
            'analysis' => $analysis,
            'suggestions' => $suggestions,
            'timestamp' => time()
        ];

        // Cache the result
        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Process text through NLP analysis
     */
    private static function processText(string $text): array {
        $payload = [
            'model' => 'text-davinci-003',
            'prompt' => "Analyze this text:\n$text\n\nSentiment:",
            'temperature' => 0.7,
            'max_tokens' => 64
        ];

        $response = AIClient::sendRequest('analysis', $payload);

        return [
            'sentiment' => self::extractSentiment($response),
            'entities' => self::extractEntities($text),
            'urgency' => self::detectUrgency($text)
        ];
    }

    /**
     * Generate response suggestions
     */
    private static function generateSuggestions(string $text): array {
        $payload = [
            'model' => 'text-davinci-003',
            'prompt' => "Suggest responses to:\n$text",
            'temperature' => 0.5,
            'max_tokens' => 256,
            'n' => 3
        ];

        $response = AIClient::sendRequest('suggestions', $payload);
        return self::parseSuggestions($response);
    }

    /**
     * Extract sentiment from AI response
     */
    private static function extractSentiment(array $response): string {
        // Implementation depends on API response format
        return $response['choices'][0]['text'] ?? 'neutral';
    }

    /**
     * Extract entities from text
     */
    private static function extractEntities(string $text): array {
        // Simple regex-based entity extraction
        preg_match_all('/\b[A-Z][a-z]+\b/', $text, $matches);
        return array_unique($matches[0] ?? []);
    }

    /**
     * Detect urgency level
     */
    private static function detectUrgency(string $text): string {
        $urgentWords = ['urgent', 'immediately', 'asap', 'critical'];
        foreach ($urgentWords as $word) {
            if (stripos($text, $word) !== false) {
                return 'high';
            }
        }
        return 'normal';
    }

    /**
     * Parse suggestions from AI response
     */
    private static function parseSuggestions(array $response): array {
        $suggestions = [];
        foreach ($response['choices'] ?? [] as $choice) {
            $suggestions[] = trim($choice['text']);
        }
        return $suggestions;
    }

    /**
     * Clear cached analyses
     */
    public static function clearCache(): void {
        self::$cache = [];
    }
}
