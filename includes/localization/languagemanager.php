<?php
namespace CMS\Localization;

class LanguageManager {
    private static $languages = [];
    private static $defaultLanguage = 'en';
    private static $currentLanguage = 'en';

    public static function registerLanguage(string $code, string $name, string $locale, bool $isDefault = false): void {
        self::$languages[$code] = [
            'name' => $name,
            'locale' => $locale,
            'is_default' => $isDefault
        ];

        if ($isDefault) {
            self::$defaultLanguage = $code;
        }
    }

    public static function getLanguages(): array {
        return self::$languages;
    }

    public static function setCurrentLanguage(string $code): void {
        if (isset(self::$languages[$code])) {
            self::$currentLanguage = $code;
        }
    }

    public static function getCurrentLanguage(): string {
        return self::$currentLanguage;
    }

    public static function getDefaultLanguage(): string {
        return self::$defaultLanguage;
    }

    public static function detectLanguage(): string {
        // Browser language detection
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
            if (isset(self::$languages[$browserLang])) {
                return $browserLang;
            }
        }

        // TODO: Add IP-based detection
        return self::$defaultLanguage;
    }
}
