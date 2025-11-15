<?php

class Translator {
    private $languageManager;
    private $localeDetector;
    private $loadedTranslations = [];
    private $translationsPath;

    public function __construct() {
        $this->languageManager = LanguageManager::getInstance();
        $this->localeDetector = new LocaleDetector();
        $this->translationsPath = __DIR__.'/../../translations/';
    }

    public function translate(string $key, array $replacements = []): string {
        $locale = $this->localeDetector->detectFromRequest();
        $translation = $this->getTranslation($key, $locale);

        if ($translation === null) {
            return $key; // Return key if no translation found
        }

        return $this->applyReplacements($translation, $replacements);
    }

    private function getTranslation(string $key, string $locale): ?string {
        $fallbackSequence = $this->languageManager->getFallbackSequence($locale);

        foreach ($fallbackSequence as $lang) {
            $this->loadLanguageFile($lang);
            
            if (isset($this->loadedTranslations[$lang][$key])) {
                return $this->loadedTranslations[$lang][$key];
            }
        }

        return null;
    }

    private function loadLanguageFile(string $language): void {
        if (isset($this->loadedTranslations[$language])) {
            return;
        }

        $filePath = $this->translationsPath . $language . '.php';
        if (!file_exists($filePath)) {
            $this->loadedTranslations[$language] = [];
            return;
        }

        $translations = require_once $filePath;
        $this->loadedTranslations[$language] = is_array($translations) ? $translations : [];
    }

    private function applyReplacements(string $translation, array $replacements): string {
        foreach ($replacements as $placeholder => $value) {
            $translation = str_replace(":$placeholder", $value, $translation);
        }
        return $translation;
    }

    public function addTranslation(string $language, string $key, string $value): bool {
        if (!$this->languageManager->isLanguageAvailable($language)) {
            return false;
        }

        $this->loadLanguageFile($language);
        $this->loadedTranslations[$language][$key] = $value;
        return true;
    }
}
