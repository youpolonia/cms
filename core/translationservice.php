<?php
/**
 * TranslationService - Handles AI-powered translations via OpenRouter
 */
class TranslationService extends AIService {
    private const CACHE_PREFIX = 'translation_';
    private const DEFAULT_MODEL = 'openrouter/auto';
    
    /**
     * Translate text using OpenRouter-compatible AI
     * @param string $text Text to translate
     * @param string $targetLang Target language code (required)
     * @param string|null $sourceLang Source language code (optional)
     * @param string|null $model OpenRouter model identifier
     * @return string Translated text
     * @throws Exception On translation failure
     */
    public function translate(
        string $text,
        string $targetLang,
        ?string $sourceLang = null,
        ?string $model = null
    ): string {
        if (empty($text)) return '';
        
        $cacheKey = $this->generateCacheKey($text, $targetLang, $sourceLang);
        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $model = $model ?? self::DEFAULT_MODEL;
        $messages = [
            [
                'role' => 'system',
                'content' => "Translate to $targetLang" . 
                    ($sourceLang ? " from $sourceLang" : '') . 
                    ". Preserve formatting and meaning exactly."
            ],
            ['role' => 'user', 'content' => $text]
        ];

        $response = $this->client->createChatCompletion([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.3
        ]);

        $translated = $response['choices'][0]['message']['content'] ?? '';
        $this->cache->set($cacheKey, $translated, 86400); // Cache for 24h
        
        return $translated;
    }

    private function generateCacheKey(
        string $text,
        string $targetLang,
        ?string $sourceLang
    ): string {
        $hash = md5($text);
        return self::CACHE_PREFIX . 
            ($sourceLang ? "{$sourceLang}_" : '') . 
            "{$targetLang}_{$hash}";
    }
}
