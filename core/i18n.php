<?php
/**
 * Internationalization (i18n) Helper
 * 
 * Usage:
 *   __('dashboard.welcome')           → "Welcome to Dashboard"
 *   __('items_count', ['count' => 5]) → "5 items"
 *   _e('dashboard.welcome')           → echo
 *   cms_locale()                      → "en"
 *   cms_set_locale('pl')
 */

if (!function_exists('cms_locale')) {
    /**
     * Get current locale
     */
    function cms_locale(): string
    {
        return $GLOBALS['_cms_locale'] ?? $_SESSION['cms_locale'] ?? _cms_default_locale();
    }
}

if (!function_exists('cms_set_locale')) {
    /**
     * Set current locale
     */
    function cms_set_locale(string $locale): void
    {
        $GLOBALS['_cms_locale'] = $locale;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['cms_locale'] = $locale;
        }
    }
}

if (!function_exists('_cms_default_locale')) {
    function _cms_default_locale(): string
    {
        static $default = null;
        if ($default === null) {
            try {
                $pdo = \Core\Database::connection();
                $stmt = $pdo->query("SELECT code FROM languages WHERE is_default = 1 LIMIT 1");
                $default = $stmt->fetchColumn() ?: 'en';
            } catch (\Throwable $e) {
                $default = 'en';
            }
        }
        return $default;
    }
}

if (!function_exists('__')) {
    /**
     * Translate a key
     * 
     * @param string $key   Format: "group.key" or just "key" (group = "general")
     * @param array  $params Replacements: ['count' => 5] replaces :count in string
     * @param string|null $locale Override locale
     * @return string Translated string or key if not found
     */
    function __(string $key, array $params = [], ?string $locale = null): string
    {
        static $cache = [];
        $locale = $locale ?? cms_locale();

        // Parse group.key
        if (str_contains($key, '.')) {
            [$group, $keyName] = explode('.', $key, 2);
        } else {
            $group = 'general';
            $keyName = $key;
        }

        // Load group if not cached
        $cacheKey = "{$locale}.{$group}";
        if (!isset($cache[$cacheKey])) {
            $cache[$cacheKey] = _cms_load_translations($locale, $group);
        }

        // Lookup
        $value = $cache[$cacheKey][$keyName] ?? $key;

        // Replace params
        foreach ($params as $k => $v) {
            $value = str_replace(":{$k}", (string)$v, $value);
        }

        return $value;
    }
}

if (!function_exists('_e')) {
    /**
     * Echo translated string
     */
    function _e(string $key, array $params = [], ?string $locale = null): void
    {
        echo __(string: $key, params: $params, locale: $locale);
    }
}

if (!function_exists('_cms_load_translations')) {
    /**
     * Load translations for a locale + group from DB
     */
    function _cms_load_translations(string $locale, string $group): array
    {
        // Try file-based first (faster, no DB hit)
        $file = \CMS_ROOT . "/lang/{$locale}/{$group}.php";
        if (file_exists($file)) {
            $data = require $file;
            if (is_array($data)) return $data;
        }

        // Fall back to database
        try {
            $pdo = \Core\Database::connection();
            $stmt = $pdo->prepare(
                "SELECT key_name, value FROM translations WHERE locale = :locale AND group_name = :grp"
            );
            $stmt->execute(['locale' => $locale, 'grp' => $group]);
            $rows = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
            return $rows ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('cms_available_locales')) {
    /**
     * Get list of active languages
     */
    function cms_available_locales(): array
    {
        static $locales = null;
        if ($locales === null) {
            try {
                $pdo = \Core\Database::connection();
                $locales = $pdo->query(
                    "SELECT code, name, native_name, direction, is_default FROM languages WHERE is_active = 1 ORDER BY sort_order, name"
                )->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Throwable $e) {
                $locales = [['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'is_default' => 1]];
            }
        }
        return $locales;
    }
}

if (!function_exists('cms_get_content_translation')) {
    /**
     * Get translated content for a page/article
     */
    function cms_get_content_translation(string $type, int $id, ?string $locale = null): ?array
    {
        $locale = $locale ?? cms_locale();
        $defaultLocale = _cms_default_locale();
        
        // Don't query for default locale — use original content
        if ($locale === $defaultLocale) return null;

        try {
            $pdo = \Core\Database::connection();
            $stmt = $pdo->prepare(
                "SELECT title, slug, content, excerpt, meta_title, meta_description 
                 FROM content_translations 
                 WHERE content_type = :type AND content_id = :id AND locale = :locale
                 LIMIT 1"
            );
            $stmt->execute(['type' => $type, 'id' => $id, 'locale' => $locale]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
