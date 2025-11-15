<?php
/**
 * AI Translator Assistant - Main Plugin File
 */
require_once __DIR__ . '/../../core/translationservice.php';

class AssistantMain {
    private TranslationService $translationService;
    
    public function __construct() {
        $this->translationService = new TranslationService();
    }

    /**
     * Translate content to target language
     */
    public function translate(string $content, string $targetLanguage): string {
        return $this->translationService->translate($content, $targetLanguage);
    }

    /**
     * Detect language of content
     */
    public function detectLanguage(string $content): string {
        return $this->translationService->detectLanguage($content);
    }

    /**
     * Get supported languages
     */
    public function getSupportedLanguages(): array {
        return $this->translationService->getSupportedLanguages();
    }
}
