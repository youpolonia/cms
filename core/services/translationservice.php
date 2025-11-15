<?php
/**
 * AI Translation Service
 * Handles text translation using configured AI models
 */
class TranslationService {
    private static $models = [];
    private static $initialized = false;

    /**
     * Initialize translation models
     */
    private static function initialize(): void {
        if (!self::$initialized) {
            self::$models = require_once __DIR__.'/../../config/ai_translation_models.php';
            self::$initialized = true;
        }
    }

    /**
     * Translate text to target language
     */
    public static function translate(
        string $text, 
        string $targetLang,
        ?string $sourceLang = null,
        ?string $modelPreference = null,
        array $options = []
    ): array {
        self::initialize();
        
        $model = self::selectModel($targetLang, $modelPreference);
        $result = self::callModelAPI($model, $text, $targetLang, $sourceLang, $options);

        return [
            'original_text' => $text,
            'translated_text' => $result['translation'],
            'source_language' => $sourceLang ?? $result['detected_language'],
            'target_language' => $targetLang,
            'model_used' => $model
        ];
    }

    /**
     * Select appropriate translation model
     */
    private static function selectModel(string $targetLang, ?string $preference): string {
        if ($preference && isset(self::$models[$preference])) {
            return $preference;
        }

        foreach (self::$models as $modelId => $config) {
            if (in_array($targetLang, $config['languages'])) {
                return $modelId;
            }
        }

        throw new Exception("No suitable model found for language: $targetLang");
    }

    /**
     * Make API call to translation model
     */
    private static function callModelAPI(
        string $modelId,
        string $text,
        string $targetLang,
        ?string $sourceLang,
        array $options
    ): array {
        $modelConfig = self::$models[$modelId];
        // Implementation would make actual API call here
        // Placeholder for actual implementation
        return [
            'translation' => "[TRANSLATED: $text]",
            'detected_language' => $sourceLang ?? 'en'
        ];
    }

    /**
     * Get supported languages
     */
    public static function getSupportedLanguages(): array {
        self::initialize();
        $languages = [];
        
        foreach (self::$models as $config) {
            $languages = array_unique(array_merge($languages, $config['languages']));
        }

        return array_values($languages);
    }
}
