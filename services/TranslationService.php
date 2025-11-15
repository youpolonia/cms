<?php

class TranslationService {
    private static $translations = [];
    private static $currentLocale = 'en_US';

    /**
     * Load translations for specified locale
     */
    public static function loadTranslations(string $locale): void {
        $file = __DIR__ . "/../../translations/{$locale}.php";
        
        if (file_exists($file)) {
            self::$translations[$locale] = require_once $file;
            self::$currentLocale = $locale;
        }
    }

    /**
     * Get translated string by key
     */
    public static function trans(string $key, array $replace = []): string {
        $translation = self::$translations[self::$currentLocale][$key] ?? $key;

        foreach ($replace as $placeholder => $value) {
            $translation = str_replace(":{$placeholder}", $value, $translation);
        }

        return $translation;
    }

    /**
     * Set current locale
     */
    public static function setLocale(string $locale): void {
        if (isset(self::$translations[$locale])) {
            self::$currentLocale = $locale;
        }
    }

    /**
     * Get current locale
     */
    public static function getLocale(): string {
        return self::$currentLocale;
    }
}
