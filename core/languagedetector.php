<?php
/**
 * LanguageDetector - Identifies text language using AI
 */
class LanguageDetector extends AIService {
    private const CACHE_PREFIX = 'langdetect_';
    private const DETECTION_MODEL = 'openrouter/auto';
    
    /**
     * Detect language of given text
     * @param string $text Text to analyze
     * @return string Detected language code (e.g. 'en', 'fr')
     * @throws Exception On detection failure
     */
    public function detectLanguage(string $text): string {
        if (empty($text)) return 'en'; // Default to English
        
        $cacheKey = self::CACHE_PREFIX . md5($text);
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $messages = [
            [
                'role' => 'system',
                'content' => 'Identify the language of this text. ' .
                    'Respond ONLY with the ISO 639-1 language code.'
            ],
            ['role' => 'user', 'content' => $text]
        ];

        $response = $this->client->createChatCompletion([
            'model' => self::DETECTION_MODEL,
            'messages' => $messages,
            'temperature' => 0.1,
            'max_tokens' => 5
        ]);

        $langCode = strtolower(trim($response['choices'][0]['message']['content'] ?? 'en'));
        $this->cache->set($cacheKey, $langCode, 86400); // Cache for 24h
        
        return $langCode;
    }
}
