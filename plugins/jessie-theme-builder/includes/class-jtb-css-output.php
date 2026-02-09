<?php
/**
 * JTB CSS Output Manager
 * Zarządza outputem CSS w <head> zamiast inline w body
 *
 * @package JessieThemeBuilder
 * @since 1.0.0
 * @date 2026-02-03
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_CSS_Output
{
    private static array $cssQueue = [];
    private static array $cssFiles = [];
    private static bool $isRendered = false;

    /**
     * Dodaj CSS do kolejki
     *
     * @param string $css CSS content
     * @param string $id Unique identifier for deduplication
     */
    public static function enqueue(string $css, string $id = ''): void
    {
        if (empty($css)) {
            return;
        }

        $key = $id ?: 'css_' . md5($css);

        // Deduplikacja
        if (!isset(self::$cssQueue[$key])) {
            self::$cssQueue[$key] = $css;
        }
    }

    /**
     * Dodaj plik CSS do załadowania
     *
     * @param string $url URL to CSS file
     * @param string $id Unique identifier
     */
    public static function enqueueFile(string $url, string $id = ''): void
    {
        $key = $id ?: 'file_' . md5($url);
        self::$cssFiles[$key] = $url;
    }

    /**
     * Pobierz cały CSS (do wstawienia w <head>)
     *
     * @return string Combined CSS
     */
    public static function getCss(): string
    {
        if (empty(self::$cssQueue)) {
            return '';
        }

        $css = implode("\n", self::$cssQueue);

        // Minifikacja w production
        if (!defined('JTB_DEV_MODE') || !JTB_DEV_MODE) {
            $css = self::minify($css);
        }

        return $css;
    }

    /**
     * Pobierz tagi <link> dla plików CSS
     *
     * @return string HTML link tags
     */
    public static function getCssLinks(): string
    {
        if (empty(self::$cssFiles)) {
            return '';
        }

        $html = '';
        foreach (self::$cssFiles as $id => $url) {
            $html .= '<link rel="stylesheet" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
        }

        return $html;
    }

    /**
     * Renderuj kompletny blok CSS dla <head>
     *
     * @return string Complete CSS block with style tags
     */
    public static function render(): string
    {
        if (self::$isRendered) {
            return '';
        }

        self::$isRendered = true;

        $output = '';

        // Pliki CSS
        $output .= self::getCssLinks();

        // Inline CSS
        $css = self::getCss();
        if (!empty($css)) {
            $output .= '<style id="jtb-inline-css">' . "\n" . $css . "\n" . '</style>' . "\n";
        }

        return $output;
    }

    /**
     * Check if CSS has been rendered
     *
     * @return bool
     */
    public static function isRendered(): bool
    {
        return self::$isRendered;
    }

    /**
     * Check if there is any CSS queued
     *
     * @return bool
     */
    public static function hasContent(): bool
    {
        return !empty(self::$cssQueue) || !empty(self::$cssFiles);
    }

    /**
     * Reset (dla testów i nowych renderów)
     */
    public static function reset(): void
    {
        self::$cssQueue = [];
        self::$cssFiles = [];
        self::$isRendered = false;
    }

    /**
     * Minifikacja CSS
     *
     * @param string $css Raw CSS
     * @return string Minified CSS
     */
    private static function minify(string $css): string
    {
        // Usuń komentarze
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Usuń whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Usuń spacje wokół selektorów
        $css = preg_replace('/\s*([{}:;,])\s*/', '$1', $css);
        // Usuń ostatni średnik przed }
        $css = preg_replace('/;}/', '}', $css);

        return trim($css);
    }
}
