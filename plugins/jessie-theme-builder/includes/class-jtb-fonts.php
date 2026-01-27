<?php
/**
 * JTB Fonts Class
 * Collects and loads Google Fonts from page content
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Fonts
{
    /**
     * System fonts that don't need Google Fonts loading
     */
    private static array $systemFonts = [
        'Arial',
        'Helvetica',
        'Georgia',
        'Times New Roman',
        'Verdana',
        'Courier New',
        'system-ui',
        'inherit',
        ''
    ];

    /**
     * Font attributes to check in content
     */
    private static array $fontAttributes = [
        'font_family',
        'title_font_family',
        'body_font_family',
        'heading_font_family',
        'quote_font_family',
        'button_font_family',
        'nav_font_family',
        'price_font_family',
        'name_font_family',
        'position_font_family',
        'meta_font_family'
    ];

    /**
     * Extract all Google Fonts from content array
     *
     * @param array $content JTB content array
     * @return array Unique list of Google Font names
     */
    public static function extractFromContent(array $content): array
    {
        $fonts = [];

        self::walkContent($content, function ($attrs) use (&$fonts) {
            foreach (self::$fontAttributes as $attr) {
                // Check main attribute
                if (!empty($attrs[$attr])) {
                    $font = self::extractFontName($attrs[$attr]);
                    if ($font) {
                        $fonts[$font] = true;
                    }
                }

                // Check responsive variants
                foreach (['__tablet', '__phone'] as $suffix) {
                    if (!empty($attrs[$attr . $suffix])) {
                        $font = self::extractFontName($attrs[$attr . $suffix]);
                        if ($font) {
                            $fonts[$font] = true;
                        }
                    }
                }
            }
        });

        return array_keys($fonts);
    }

    /**
     * Walk content tree and call callback for each module's attrs
     *
     * @param array $content Content array
     * @param callable $callback Callback function receiving attrs
     */
    private static function walkContent(array $content, callable $callback): void
    {
        foreach ($content as $item) {
            if (isset($item['attrs']) && is_array($item['attrs'])) {
                $callback($item['attrs']);
            }

            if (isset($item['children']) && is_array($item['children'])) {
                self::walkContent($item['children'], $callback);
            }
        }
    }

    /**
     * Extract font name from font family value
     *
     * @param string $fontFamily CSS font-family value
     * @return string|null Font name or null if system font
     */
    private static function extractFontName(string $fontFamily): ?string
    {
        // Remove CSS fallbacks (take first font)
        $fontName = explode(',', $fontFamily)[0];

        // Remove quotes
        $fontName = trim($fontName, '"\' ');

        // Skip empty, category separators, and system fonts
        if (empty($fontName) || strpos($fontName, '_') === 0 || in_array($fontName, self::$systemFonts)) {
            return null;
        }

        return $fontName;
    }

    /**
     * Generate Google Fonts URL for given fonts
     *
     * @param array $fonts Array of font names
     * @return string Google Fonts CSS URL
     */
    public static function getGoogleFontsUrl(array $fonts): string
    {
        if (empty($fonts)) {
            return '';
        }

        $families = [];
        foreach ($fonts as $font) {
            // URL encode and add weights
            $families[] = urlencode($font) . ':wght@100;200;300;400;500;600;700;800;900';
        }

        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
    }

    /**
     * Generate HTML link tag for Google Fonts
     *
     * @param array $fonts Array of font names
     * @return string HTML link tag or empty string
     */
    public static function getGoogleFontsLink(array $fonts): string
    {
        $url = self::getGoogleFontsUrl($fonts);

        if (empty($url)) {
            return '';
        }

        return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n" .
               '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n" .
               '<link href="' . \esc($url) . '" rel="stylesheet">' . "\n";
    }

    /**
     * Extract fonts from content and return link tag
     *
     * @param array $content JTB content array
     * @return string HTML link tag or empty string
     */
    public static function getGoogleFontsLinkFromContent(array $content): string
    {
        $fonts = self::extractFromContent($content);
        return self::getGoogleFontsLink($fonts);
    }

    /**
     * Get preload hints for Google Fonts
     *
     * @param array $fonts Array of font names
     * @return string HTML preload tags
     */
    public static function getPreloadHints(array $fonts): string
    {
        if (empty($fonts)) {
            return '';
        }

        return '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n" .
               '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    }

    /**
     * Get font options for select fields
     *
     * @return array Font options array (value => label)
     */
    public static function getFontOptions(): array
    {
        return [
            // System Fonts
            '' => '-- System Fonts --',
            'inherit' => 'Inherit',
            'Arial' => 'Arial',
            'Helvetica' => 'Helvetica',
            'Georgia' => 'Georgia',
            'Times New Roman' => 'Times New Roman',
            'Verdana' => 'Verdana',
            'system-ui' => 'System UI',

            // Google Fonts - Popular
            '_google' => '-- Google Fonts --',
            'Inter' => 'Inter',
            'Roboto' => 'Roboto',
            'Open Sans' => 'Open Sans',
            'Lato' => 'Lato',
            'Montserrat' => 'Montserrat',
            'Poppins' => 'Poppins',
            'Raleway' => 'Raleway',
            'Nunito' => 'Nunito',
            'Work Sans' => 'Work Sans',
            'DM Sans' => 'DM Sans',
            'Plus Jakarta Sans' => 'Plus Jakarta Sans',
            'Outfit' => 'Outfit',
            'Manrope' => 'Manrope',
            'Space Grotesk' => 'Space Grotesk',

            // Serif
            '_serif' => '-- Serif --',
            'Playfair Display' => 'Playfair Display',
            'Merriweather' => 'Merriweather',
            'Lora' => 'Lora',
            'PT Serif' => 'PT Serif',
            'Libre Baskerville' => 'Libre Baskerville',
            'Crimson Text' => 'Crimson Text',
            'EB Garamond' => 'EB Garamond',
            'DM Serif Display' => 'DM Serif Display',
            'Fraunces' => 'Fraunces',

            // Display
            '_display' => '-- Display --',
            'Bebas Neue' => 'Bebas Neue',
            'Oswald' => 'Oswald',
            'Anton' => 'Anton',
            'Righteous' => 'Righteous',
            'Archivo Black' => 'Archivo Black',
            'Titan One' => 'Titan One',

            // Monospace
            '_mono' => '-- Monospace --',
            'JetBrains Mono' => 'JetBrains Mono',
            'Fira Code' => 'Fira Code',
            'Source Code Pro' => 'Source Code Pro',
            'IBM Plex Mono' => 'IBM Plex Mono',
            'Roboto Mono' => 'Roboto Mono',
        ];
    }
}
