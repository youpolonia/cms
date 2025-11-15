<?php
namespace CMS\Localization;

class ContentTranslator {
    public static function translateContent(array $content, string $targetLanguage): array {
        $translated = [];
        foreach ($content as $field => $value) {
            if (is_array($value)) {
                // Handle nested content fields
                $translated[$field] = self::translateContent($value, $targetLanguage);
            } else {
                // Check if field has translations available
                if (isset($value['translations'][$targetLanguage])) {
                    $translated[$field] = $value['translations'][$targetLanguage];
                } elseif (isset($value['translations'][LanguageManager::getDefaultLanguage()])) {
                    // Fallback to default language
                    $translated[$field] = $value['translations'][LanguageManager::getDefaultLanguage()];
                } else {
                    // No translation available - use original
                    $translated[$field] = is_array($value) ? $value['value'] ?? '' : $value;
                }
            }
        }
        return $translated;
    }

    public static function addTranslation(array &$content, string $field, string $language, string $translation): void {
        if (!isset($content[$field]['translations'])) {
            $content[$field]['translations'] = [];
        }
        $content[$field]['translations'][$language] = $translation;
    }

    public static function getAvailableLanguages(array $content): array {
        $languages = [];
        foreach ($content as $field => $value) {
            if (isset($value['translations'])) {
                $languages = array_unique(array_merge($languages, array_keys($value['translations'])));
            }
        }
        return $languages;
    }
}
