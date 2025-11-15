<?php

class LanguageManager {
    private static $instance = null;
    private $availableLanguages = [];
    private $defaultLanguage = 'en';
    private $translationsPath = __DIR__.'/../../translations/';

    private function __construct() {
        $this->loadAvailableLanguages();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadAvailableLanguages() {
        if (!is_dir($this->translationsPath)) {
            return;
        }

        $files = scandir($this->translationsPath);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $langCode = pathinfo($file, PATHINFO_FILENAME);
                $this->availableLanguages[] = $langCode;
            }
        }
    }

    public function getAvailableLanguages(): array {
        return $this->availableLanguages;
    }

    public function setDefaultLanguage(string $language): void {
        $this->defaultLanguage = $language;
    }

    public function getDefaultLanguage(): string {
        return $this->defaultLanguage;
    }

    public function isLanguageAvailable(string $language): bool {
        return in_array($language, $this->availableLanguages);
    }

    public function getFallbackSequence(string $language): array {
        $sequence = [$language];
        
        // Add base language if regional variant (e.g., en_US -> en)
        if (strpos($language, '_') !== false) {
            $baseLang = explode('_', $language)[0];
            $sequence[] = $baseLang;
        }
        
        $sequence[] = $this->defaultLanguage;
        return array_unique($sequence);
    }
}
