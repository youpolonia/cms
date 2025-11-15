<?php
namespace CMS\Localization;

class Translator {
    private static $translations = [];
    private static $fallbackEnabled = true;

    public static function loadTranslations(string $languageCode, array $translations): void {
        self::$translations[$languageCode] = $translations;
    }

    public static function translate(string $key, array $params = [], ?string $language = null): string {
        $language = $language ?? LanguageManager::getCurrentLanguage();
        $translation = self::$translations[$language][$key] ?? null;

        if ($translation === null && self::$fallbackEnabled) {
            $translation = self::$translations[LanguageManager::getDefaultLanguage()][$key] ?? $key;
        }

        if (!empty($params)) {
            foreach ($params as $param => $value) {
                $translation = str_replace("{{$param}}", $value, $translation);
            }
        }

        return $translation;
    }

    public static function setFallbackEnabled(bool $enabled): void {
        self::$fallbackEnabled = $enabled;
    }

    public static function getTranslations(string $languageCode): array {
        return self::$translations[$languageCode] ?? [];
    }
}
