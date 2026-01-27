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

    public static function addLanguage(array $languageData): bool {
        if (empty($languageData['code']) || empty($languageData['name']) || empty($languageData['locale'])) {
            return false;
        }

        $code = (string) $languageData['code'];

        if (isset(self::$languages[$code])) {
            return false;
        }

        $name = (string) $languageData['name'];
        $locale = (string) $languageData['locale'];
        $isDefault = !empty($languageData['is_default']);

        self::registerLanguage($code, $name, $locale, $isDefault);

        return true;
    }

    public static function deleteLanguage(string $code): bool {
        if (!isset(self::$languages[$code])) {
            return false;
        }

        unset(self::$languages[$code]);

        if (self::$defaultLanguage === $code) {
            if (!empty(self::$languages)) {
                $firstCode = array_keys(self::$languages)[0];
                self::$defaultLanguage = $firstCode;
                self::$languages[$firstCode]['is_default'] = true;
            } else {
                self::$defaultLanguage = 'en';
            }
        }

        if (self::$currentLanguage === $code) {
            self::$currentLanguage = self::$defaultLanguage;
        }

        return true;
    }

    public static function updateLanguage(array $languageData): bool {
        if (empty($languageData['code'])) {
            return false;
        }

        $code = (string) $languageData['code'];

        if (!isset(self::$languages[$code])) {
            return false;
        }

        if (isset($languageData['name']) && $languageData['name'] !== '') {
            self::$languages[$code]['name'] = (string) $languageData['name'];
        }

        if (isset($languageData['locale']) && $languageData['locale'] !== '') {
            self::$languages[$code]['locale'] = (string) $languageData['locale'];
        }

        if (array_key_exists('is_default', $languageData)) {
            $isDefault = !empty($languageData['is_default']);

            if ($isDefault) {
                foreach (self::$languages as $languageCode => &$language) {
                    $language['is_default'] = ($languageCode === $code);
                }

                unset($language);

                self::$defaultLanguage = $code;
            } elseif (isset(self::$languages[$code])) {
                self::$languages[$code]['is_default'] = false;
            }
        }

        return true;
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
