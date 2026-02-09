<?php
/**
 * JTB Unified Style System
 * Konsoliduje CSS_Generator i CSS_Variables w jeden spójny system
 *
 * @package JessieThemeBuilder
 * @since 1.1.0
 * @date 2026-02-03
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Style_System
{
    private static ?self $instance = null;
    private ?JTB_Theme_Settings $themeSettings = null;
    private array $defaults = [];
    private ?string $cachedGlobalCss = null;
    private ?string $cachedModuleCss = null;

    /**
     * Singleton
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->loadDefaults();

        if (class_exists('\\JessieThemeBuilder\\JTB_Theme_Settings')) {
            $this->themeSettings = new JTB_Theme_Settings();
        }
    }

    /**
     * Load default values from all sources
     */
    private function loadDefaults(): void
    {
        $this->defaults = [
            // Colors
            'primary_color' => '#6366f1',
            'secondary_color' => '#8b5cf6',
            'accent_color' => '#f59e0b',
            'text_color' => '#374151',
            'text_light_color' => '#6b7280',
            'heading_color' => '#111827',
            'link_color' => '#6366f1',
            'link_hover_color' => '#4f46e5',
            'background_color' => '#ffffff',
            'surface_color' => '#f9fafb',
            'border_color' => '#e5e7eb',
            'success_color' => '#10b981',
            'warning_color' => '#f59e0b',
            'error_color' => '#ef4444',
            'info_color' => '#3b82f6',

            // Typography
            'body_font' => 'Inter',
            'body_size' => '16',
            'body_weight' => '400',
            'body_line_height' => '1.7',
            'heading_font' => 'Inter',
            'heading_weight' => '700',
            'heading_line_height' => '1.3',
            'heading_letter_spacing' => '-0.02em',
            'h1_size' => '48',
            'h2_size' => '36',
            'h3_size' => '28',
            'h4_size' => '24',
            'h5_size' => '20',
            'h6_size' => '18',

            // Layout
            'content_width' => '1200',
            'gutter_width' => '30',
            'section_padding_top' => '80',
            'section_padding_bottom' => '80',
            'row_gap' => '30',
            'column_gap' => '30',

            // Buttons
            'button_bg_color' => '#6366f1',
            'button_text_color' => '#ffffff',
            'button_border_color' => '#6366f1',
            'button_border_width' => '0',
            'button_border_radius' => '8',
            'button_padding_tb' => '12',
            'button_padding_lr' => '24',
            'button_font_size' => '16',
            'button_font_weight' => '600',
            'button_text_transform' => 'none',
            'button_hover_bg' => '#4f46e5',
            'button_hover_text' => '#ffffff',
            'button_hover_border' => '#4f46e5',
            'button_transition' => '0.3s',

            // Forms
            'input_bg_color' => '#ffffff',
            'input_text_color' => '#374151',
            'input_border_color' => '#d1d5db',
            'input_border_width' => '1',
            'input_border_radius' => '6',
            'input_padding_tb' => '10',
            'input_padding_lr' => '14',
            'input_font_size' => '16',
            'input_focus_border_color' => '#6366f1',
            'placeholder_color' => '#9ca3af',
            'label_color' => '#374151',
            'label_font_size' => '14',

            // Header
            'header_bg_color' => '#ffffff',
            'header_text_color' => '#374151',
            'header_height' => '80',
            'header_padding_lr' => '30',
            'logo_height' => '50',
            'header_sticky' => '1',
            'header_sticky_bg' => '#ffffff',
            'logo_height_sticky' => '40',
            'header_transparent' => '0',
            'header_transparent_text' => '#ffffff',

            // Menu
            'menu_font_family' => 'Inter',
            'menu_font_size' => '15',
            'menu_font_weight' => '500',
            'menu_text_transform' => 'none',
            'menu_link_color' => '#374151',
            'menu_link_hover_color' => '#6366f1',
            'menu_link_active_color' => '#6366f1',
            'menu_link_padding_tb' => '10',
            'menu_link_padding_lr' => '16',
            'dropdown_bg_color' => '#ffffff',
            'dropdown_text_color' => '#374151',
            'dropdown_hover_bg' => '#f9fafb',
            'dropdown_border_radius' => '8',
            'mobile_breakpoint' => '980',
            'mobile_menu_bg' => '#ffffff',
            'mobile_menu_text' => '#374151',
            'hamburger_color' => '#374151',

            // Footer
            'footer_bg_color' => '#1f2937',
            'footer_text_color' => '#9ca3af',
            'footer_heading_color' => '#ffffff',
            'footer_link_color' => '#9ca3af',
            'footer_link_hover_color' => '#ffffff',
            'footer_padding_top' => '60',
            'footer_padding_bottom' => '40',
            'footer_columns' => '4',
            'copyright_bg_color' => '#111827',
            'copyright_text_color' => '#6b7280',
            'copyright_padding_tb' => '20',
            'copyright_text' => '© {year} All rights reserved.',

            // Blog
            'blog_layout' => 'grid',
            'blog_columns' => '3',
            'blog_gap' => '30',
            'post_card_bg' => '#ffffff',
            'post_card_border_radius' => '12',
            'show_featured_image' => '1',
            'show_date' => '1',
            'show_author' => '1',
            'show_categories' => '1',
            'show_excerpt' => '1',
            'excerpt_length' => '150',
            'show_read_more' => '1',
            'read_more_text' => 'Read More',

            // Responsive
            'tablet_breakpoint' => '980',
            'phone_breakpoint' => '767',
            'h1_size_tablet' => '36',
            'h2_size_tablet' => '28',
            'h3_size_tablet' => '24',
            'body_size_tablet' => '15',
            'section_padding_tablet' => '60',
            'h1_size_phone' => '28',
            'h2_size_phone' => '24',
            'h3_size_phone' => '20',
            'body_size_phone' => '14',
            'section_padding_phone' => '40',
        ];
    }

    /**
     * Get setting value with fallback chain
     */
    public function get(string $key, $default = null)
    {
        // 1. Try theme settings from DB
        if ($this->themeSettings) {
            $value = $this->themeSettings->get($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        // 2. Try internal defaults
        if (isset($this->defaults[$key])) {
            return $this->defaults[$key];
        }

        // 3. Return provided default
        return $default;
    }

    /**
     * Get all settings merged with defaults
     */
    public function getAll(): array
    {
        $settings = $this->defaults;

        if ($this->themeSettings) {
            $dbSettings = $this->themeSettings->getAll();
            foreach ($dbSettings as $group => $values) {
                if (is_array($values)) {
                    foreach ($values as $key => $value) {
                        if ($value !== null && $value !== '') {
                            $settings[$key] = $value;
                        }
                    }
                }
            }
        }

        return $settings;
    }

    /**
     * Get defaults only
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Generate CSS Variables (:root)
     */
    public function generateCssVariables(): string
    {
        $s = $this->getAll();

        $css = ":root {\n";

        // Colors
        $css .= "    /* Colors */\n";
        $css .= "    --jtb-primary-color: {$s['primary_color']};\n";
        $css .= "    --jtb-primary-hover: " . $this->adjustBrightness($s['primary_color'], -10) . ";\n";
        $css .= "    --jtb-primary-light: " . $this->adjustBrightness($s['primary_color'], 40) . ";\n";
        $css .= "    --jtb-secondary-color: {$s['secondary_color']};\n";
        $css .= "    --jtb-accent-color: {$s['accent_color']};\n";
        $css .= "    --jtb-text-color: {$s['text_color']};\n";
        $css .= "    --jtb-text-light-color: {$s['text_light_color']};\n";
        $css .= "    --jtb-heading-color: {$s['heading_color']};\n";
        $css .= "    --jtb-link-color: {$s['link_color']};\n";
        $css .= "    --jtb-link-hover-color: {$s['link_hover_color']};\n";
        $css .= "    --jtb-background-color: {$s['background_color']};\n";
        $css .= "    --jtb-surface-color: {$s['surface_color']};\n";
        $css .= "    --jtb-border-color: {$s['border_color']};\n";
        $css .= "    --jtb-success-color: {$s['success_color']};\n";
        $css .= "    --jtb-warning-color: {$s['warning_color']};\n";
        $css .= "    --jtb-error-color: {$s['error_color']};\n";
        $css .= "    --jtb-info-color: {$s['info_color']};\n";

        // Typography
        $css .= "\n    /* Typography */\n";
        $css .= "    --jtb-body-font: \"{$s['body_font']}\", ui-sans-serif, system-ui, -apple-system, sans-serif;\n";
        $css .= "    --jtb-body-size: {$s['body_size']}px;\n";
        $css .= "    --jtb-body-weight: {$s['body_weight']};\n";
        $css .= "    --jtb-body-line-height: {$s['body_line_height']};\n";
        $css .= "    --jtb-heading-font: \"{$s['heading_font']}\", ui-sans-serif, system-ui, -apple-system, sans-serif;\n";
        $css .= "    --jtb-heading-weight: {$s['heading_weight']};\n";
        $css .= "    --jtb-heading-line-height: {$s['heading_line_height']};\n";
        $css .= "    --jtb-heading-letter-spacing: {$s['heading_letter_spacing']};\n";
        $css .= "    --jtb-h1-size: {$s['h1_size']}px;\n";
        $css .= "    --jtb-h2-size: {$s['h2_size']}px;\n";
        $css .= "    --jtb-h3-size: {$s['h3_size']}px;\n";
        $css .= "    --jtb-h4-size: {$s['h4_size']}px;\n";
        $css .= "    --jtb-h5-size: {$s['h5_size']}px;\n";
        $css .= "    --jtb-h6-size: {$s['h6_size']}px;\n";

        // Layout
        $css .= "\n    /* Layout */\n";
        $css .= "    --jtb-content-width: {$s['content_width']}px;\n";
        $css .= "    --jtb-gutter-width: {$s['gutter_width']}px;\n";
        $css .= "    --jtb-section-padding-top: {$s['section_padding_top']}px;\n";
        $css .= "    --jtb-section-padding-bottom: {$s['section_padding_bottom']}px;\n";
        $css .= "    --jtb-row-gap: {$s['row_gap']}px;\n";
        $css .= "    --jtb-column-gap: {$s['column_gap']}px;\n";

        // Buttons
        $css .= "\n    /* Buttons */\n";
        $css .= "    --jtb-button-bg: {$s['button_bg_color']};\n";
        $css .= "    --jtb-button-text: {$s['button_text_color']};\n";
        $css .= "    --jtb-button-border-color: {$s['button_border_color']};\n";
        $css .= "    --jtb-button-border-width: {$s['button_border_width']}px;\n";
        $css .= "    --jtb-button-border-radius: {$s['button_border_radius']}px;\n";
        $css .= "    --jtb-button-padding-tb: {$s['button_padding_tb']}px;\n";
        $css .= "    --jtb-button-padding-lr: {$s['button_padding_lr']}px;\n";
        $css .= "    --jtb-button-font-size: {$s['button_font_size']}px;\n";
        $css .= "    --jtb-button-font-weight: {$s['button_font_weight']};\n";
        $css .= "    --jtb-button-text-transform: {$s['button_text_transform']};\n";
        $css .= "    --jtb-button-hover-bg: {$s['button_hover_bg']};\n";
        $css .= "    --jtb-button-hover-text: {$s['button_hover_text']};\n";
        $css .= "    --jtb-button-hover-border: {$s['button_hover_border']};\n";
        $css .= "    --jtb-button-transition: {$s['button_transition']};\n";

        // Forms
        $css .= "\n    /* Forms */\n";
        $css .= "    --jtb-input-bg: {$s['input_bg_color']};\n";
        $css .= "    --jtb-input-text: {$s['input_text_color']};\n";
        $css .= "    --jtb-input-border: {$s['input_border_color']};\n";
        $css .= "    --jtb-input-border-width: {$s['input_border_width']}px;\n";
        $css .= "    --jtb-input-border-radius: {$s['input_border_radius']}px;\n";
        $css .= "    --jtb-input-padding-tb: {$s['input_padding_tb']}px;\n";
        $css .= "    --jtb-input-padding-lr: {$s['input_padding_lr']}px;\n";
        $css .= "    --jtb-input-font-size: {$s['input_font_size']}px;\n";
        $css .= "    --jtb-input-focus-border: {$s['input_focus_border_color']};\n";
        $css .= "    --jtb-placeholder-color: {$s['placeholder_color']};\n";
        $css .= "    --jtb-label-color: {$s['label_color']};\n";
        $css .= "    --jtb-label-font-size: {$s['label_font_size']}px;\n";

        // Header
        $css .= "\n    /* Header */\n";
        $css .= "    --jtb-header-bg: {$s['header_bg_color']};\n";
        $css .= "    --jtb-header-text: {$s['header_text_color']};\n";
        $css .= "    --jtb-header-height: {$s['header_height']}px;\n";
        $css .= "    --jtb-header-padding-lr: {$s['header_padding_lr']}px;\n";
        $css .= "    --jtb-logo-height: {$s['logo_height']}px;\n";
        $css .= "    --jtb-header-sticky-bg: {$s['header_sticky_bg']};\n";
        $css .= "    --jtb-logo-height-sticky: {$s['logo_height_sticky']}px;\n";
        $css .= "    --jtb-header-transparent-text: {$s['header_transparent_text']};\n";

        // Menu
        $css .= "\n    /* Menu */\n";
        $css .= "    --jtb-menu-font: \"{$s['menu_font_family']}\", ui-sans-serif, system-ui, sans-serif;\n";
        $css .= "    --jtb-menu-font-size: {$s['menu_font_size']}px;\n";
        $css .= "    --jtb-menu-font-weight: {$s['menu_font_weight']};\n";
        $css .= "    --jtb-menu-text-transform: {$s['menu_text_transform']};\n";
        $css .= "    --jtb-menu-link-color: {$s['menu_link_color']};\n";
        $css .= "    --jtb-menu-link-hover: {$s['menu_link_hover_color']};\n";
        $css .= "    --jtb-menu-link-active: {$s['menu_link_active_color']};\n";
        $css .= "    --jtb-menu-link-padding-tb: {$s['menu_link_padding_tb']}px;\n";
        $css .= "    --jtb-menu-link-padding-lr: {$s['menu_link_padding_lr']}px;\n";
        $css .= "    --jtb-dropdown-bg: {$s['dropdown_bg_color']};\n";
        $css .= "    --jtb-dropdown-text: {$s['dropdown_text_color']};\n";
        $css .= "    --jtb-dropdown-hover-bg: {$s['dropdown_hover_bg']};\n";
        $css .= "    --jtb-dropdown-border-radius: {$s['dropdown_border_radius']}px;\n";
        $css .= "    --jtb-mobile-menu-bg: {$s['mobile_menu_bg']};\n";
        $css .= "    --jtb-mobile-menu-text: {$s['mobile_menu_text']};\n";
        $css .= "    --jtb-hamburger-color: {$s['hamburger_color']};\n";

        // Footer
        $css .= "\n    /* Footer */\n";
        $css .= "    --jtb-footer-bg: {$s['footer_bg_color']};\n";
        $css .= "    --jtb-footer-text: {$s['footer_text_color']};\n";
        $css .= "    --jtb-footer-heading: {$s['footer_heading_color']};\n";
        $css .= "    --jtb-footer-link: {$s['footer_link_color']};\n";
        $css .= "    --jtb-footer-link-hover: {$s['footer_link_hover_color']};\n";
        $css .= "    --jtb-footer-padding-top: {$s['footer_padding_top']}px;\n";
        $css .= "    --jtb-footer-padding-bottom: {$s['footer_padding_bottom']}px;\n";
        $css .= "    --jtb-copyright-bg: {$s['copyright_bg_color']};\n";
        $css .= "    --jtb-copyright-text: {$s['copyright_text_color']};\n";
        $css .= "    --jtb-copyright-padding-tb: {$s['copyright_padding_tb']}px;\n";

        // Blog
        $css .= "\n    /* Blog */\n";
        $css .= "    --jtb-blog-columns: {$s['blog_columns']};\n";
        $css .= "    --jtb-blog-gap: {$s['blog_gap']}px;\n";
        $css .= "    --jtb-post-card-bg: {$s['post_card_bg']};\n";
        $css .= "    --jtb-post-card-border-radius: {$s['post_card_border_radius']}px;\n";

        // Breakpoints
        $css .= "\n    /* Breakpoints */\n";
        $css .= "    --jtb-tablet-breakpoint: {$s['tablet_breakpoint']}px;\n";
        $css .= "    --jtb-phone-breakpoint: {$s['phone_breakpoint']}px;\n";
        $css .= "    --jtb-mobile-breakpoint: {$s['mobile_breakpoint']}px;\n";

        // Transitions
        $css .= "\n    /* Transitions */\n";
        $css .= "    --jtb-transition-fast: 0.15s ease;\n";
        $css .= "    --jtb-transition-normal: 0.3s ease;\n";
        $css .= "    --jtb-transition-slow: 0.5s ease;\n";
        $css .= "    --jtb-transition-bounce: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);\n";

        // Shadows
        $css .= "\n    /* Shadows */\n";
        $css .= "    --jtb-shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);\n";
        $css .= "    --jtb-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n";
        $css .= "    --jtb-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);\n";
        $css .= "    --jtb-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);\n";

        // Z-index scale
        $css .= "\n    /* Z-index */\n";
        $css .= "    --jtb-z-dropdown: 1000;\n";
        $css .= "    --jtb-z-sticky: 1020;\n";
        $css .= "    --jtb-z-fixed: 1030;\n";
        $css .= "    --jtb-z-modal-backdrop: 1040;\n";
        $css .= "    --jtb-z-modal: 1050;\n";
        $css .= "    --jtb-z-popover: 1060;\n";
        $css .= "    --jtb-z-tooltip: 1070;\n";

        $css .= "}\n";

        // Responsive overrides
        $css .= "\n/* Tablet */\n";
        $css .= "@media (max-width: {$s['tablet_breakpoint']}px) {\n";
        $css .= "    :root {\n";
        $css .= "        --jtb-h1-size: {$s['h1_size_tablet']}px;\n";
        $css .= "        --jtb-h2-size: {$s['h2_size_tablet']}px;\n";
        $css .= "        --jtb-h3-size: {$s['h3_size_tablet']}px;\n";
        $css .= "        --jtb-body-size: {$s['body_size_tablet']}px;\n";
        $css .= "        --jtb-section-padding-top: {$s['section_padding_tablet']}px;\n";
        $css .= "        --jtb-section-padding-bottom: {$s['section_padding_tablet']}px;\n";
        $css .= "    }\n";
        $css .= "}\n";

        $css .= "\n/* Phone */\n";
        $css .= "@media (max-width: {$s['phone_breakpoint']}px) {\n";
        $css .= "    :root {\n";
        $css .= "        --jtb-h1-size: {$s['h1_size_phone']}px;\n";
        $css .= "        --jtb-h2-size: {$s['h2_size_phone']}px;\n";
        $css .= "        --jtb-h3-size: {$s['h3_size_phone']}px;\n";
        $css .= "        --jtb-body-size: {$s['body_size_phone']}px;\n";
        $css .= "        --jtb-section-padding-top: {$s['section_padding_phone']}px;\n";
        $css .= "        --jtb-section-padding-bottom: {$s['section_padding_phone']}px;\n";
        $css .= "    }\n";
        $css .= "}\n";

        return $css;
    }

    /**
     * Adjust color brightness
     */
    private function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#' . $hex;
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    /**
     * Invalidate cache (call after theme settings change)
     */
    public function invalidateCache(): void
    {
        $this->cachedGlobalCss = null;
        $this->cachedModuleCss = null;

        // Reset singleton to reload settings
        self::$instance = null;
    }

    /**
     * Static helper to invalidate cache
     */
    public static function clearCache(): void
    {
        if (self::$instance !== null) {
            self::$instance->invalidateCache();
        }
        self::$instance = null;
    }

    /**
     * Get full global CSS (variables + base styles)
     */
    public function getGlobalCss(): string
    {
        if ($this->cachedGlobalCss !== null) {
            return $this->cachedGlobalCss;
        }

        $css = $this->generateCssVariables();

        // Add base styles that use variables
        $css .= $this->generateBaseStyles();

        $this->cachedGlobalCss = $css;

        return $css;
    }

    /**
     * Generate base styles using CSS variables
     */
    private function generateBaseStyles(): string
    {
        return <<<CSS

/* Base Typography */
.jtb-content {
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-body-size);
    font-weight: var(--jtb-body-weight);
    line-height: var(--jtb-body-line-height);
    color: var(--jtb-text-color);
}

.jtb-content h1,
.jtb-content h2,
.jtb-content h3,
.jtb-content h4,
.jtb-content h5,
.jtb-content h6 {
    font-family: var(--jtb-heading-font);
    font-weight: var(--jtb-heading-weight);
    line-height: var(--jtb-heading-line-height);
    letter-spacing: var(--jtb-heading-letter-spacing);
    color: var(--jtb-heading-color);
    margin-top: 0;
}

.jtb-content h1 { font-size: var(--jtb-h1-size); }
.jtb-content h2 { font-size: var(--jtb-h2-size); }
.jtb-content h3 { font-size: var(--jtb-h3-size); }
.jtb-content h4 { font-size: var(--jtb-h4-size); }
.jtb-content h5 { font-size: var(--jtb-h5-size); }
.jtb-content h6 { font-size: var(--jtb-h6-size); }

.jtb-content a {
    color: var(--jtb-link-color);
    text-decoration: none;
    transition: color var(--jtb-transition-fast);
}

.jtb-content a:hover {
    color: var(--jtb-link-hover-color);
}

/* Base Button */
.jtb-button,
.jtb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: var(--jtb-button-padding-tb) var(--jtb-button-padding-lr);
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-button-font-size);
    font-weight: var(--jtb-button-font-weight);
    text-transform: var(--jtb-button-text-transform);
    text-decoration: none;
    border: var(--jtb-button-border-width) solid var(--jtb-button-border-color);
    border-radius: var(--jtb-button-border-radius);
    background-color: var(--jtb-button-bg);
    color: var(--jtb-button-text);
    cursor: pointer;
    transition: all var(--jtb-button-transition);
}

.jtb-button:hover,
.jtb-btn:hover {
    background-color: var(--jtb-button-hover-bg);
    border-color: var(--jtb-button-hover-border);
    color: var(--jtb-button-hover-text);
}

/* Base Form Elements */
.jtb-content input[type="text"],
.jtb-content input[type="email"],
.jtb-content input[type="password"],
.jtb-content input[type="tel"],
.jtb-content input[type="url"],
.jtb-content input[type="number"],
.jtb-content textarea,
.jtb-content select {
    width: 100%;
    padding: var(--jtb-input-padding-tb) var(--jtb-input-padding-lr);
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-input-font-size);
    color: var(--jtb-input-text);
    background-color: var(--jtb-input-bg);
    border: var(--jtb-input-border-width) solid var(--jtb-input-border);
    border-radius: var(--jtb-input-border-radius);
    transition: border-color var(--jtb-transition-fast);
}

.jtb-content input:focus,
.jtb-content textarea:focus,
.jtb-content select:focus {
    outline: none;
    border-color: var(--jtb-input-focus-border);
}

.jtb-content input::placeholder,
.jtb-content textarea::placeholder {
    color: var(--jtb-placeholder-color);
}

.jtb-content label {
    display: block;
    margin-bottom: 6px;
    font-size: var(--jtb-label-font-size);
    font-weight: 500;
    color: var(--jtb-label-color);
}

/* Section Inner Container */
.jtb-section-inner {
    max-width: var(--jtb-content-width);
    margin: 0 auto;
    padding-left: var(--jtb-gutter-width);
    padding-right: var(--jtb-gutter-width);
}

/* Header Base */
.jtb-site-header {
    background-color: var(--jtb-header-bg);
    color: var(--jtb-header-text);
    height: var(--jtb-header-height);
    padding-left: var(--jtb-header-padding-lr);
    padding-right: var(--jtb-header-padding-lr);
}

.jtb-site-header.sticky {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: var(--jtb-z-sticky);
    background-color: var(--jtb-header-sticky-bg);
}

.jtb-site-header .jtb-logo img {
    height: var(--jtb-logo-height);
    width: auto;
}

.jtb-site-header.sticky .jtb-logo img {
    height: var(--jtb-logo-height-sticky);
}

/* Navigation Menu Base */
.jtb-nav-menu {
    font-family: var(--jtb-menu-font);
    font-size: var(--jtb-menu-font-size);
    font-weight: var(--jtb-menu-font-weight);
    text-transform: var(--jtb-menu-text-transform);
}

.jtb-nav-menu a {
    color: var(--jtb-menu-link-color);
    padding: var(--jtb-menu-link-padding-tb) var(--jtb-menu-link-padding-lr);
    transition: color var(--jtb-transition-fast);
}

.jtb-nav-menu a:hover {
    color: var(--jtb-menu-link-hover);
}

.jtb-nav-menu a.active {
    color: var(--jtb-menu-link-active);
}

.jtb-dropdown-menu {
    background-color: var(--jtb-dropdown-bg);
    color: var(--jtb-dropdown-text);
    border-radius: var(--jtb-dropdown-border-radius);
}

.jtb-dropdown-menu a:hover {
    background-color: var(--jtb-dropdown-hover-bg);
}

/* Footer Base */
.jtb-site-footer {
    background-color: var(--jtb-footer-bg);
    color: var(--jtb-footer-text);
    padding-top: var(--jtb-footer-padding-top);
    padding-bottom: var(--jtb-footer-padding-bottom);
}

.jtb-site-footer h1,
.jtb-site-footer h2,
.jtb-site-footer h3,
.jtb-site-footer h4,
.jtb-site-footer h5,
.jtb-site-footer h6 {
    color: var(--jtb-footer-heading);
}

.jtb-site-footer a {
    color: var(--jtb-footer-link);
}

.jtb-site-footer a:hover {
    color: var(--jtb-footer-link-hover);
}

.jtb-copyright {
    background-color: var(--jtb-copyright-bg);
    color: var(--jtb-copyright-text);
    padding-top: var(--jtb-copyright-padding-tb);
    padding-bottom: var(--jtb-copyright-padding-tb);
}

/* Blog Grid */
.jtb-blog-grid {
    display: grid;
    grid-template-columns: repeat(var(--jtb-blog-columns), 1fr);
    gap: var(--jtb-blog-gap);
}

.jtb-post-card {
    background-color: var(--jtb-post-card-bg);
    border-radius: var(--jtb-post-card-border-radius);
    overflow: hidden;
}

@media (max-width: 980px) {
    .jtb-blog-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .jtb-blog-grid {
        grid-template-columns: 1fr;
    }
}

CSS;
    }

    /**
     * Output CSS to head (for use in templates)
     */
    public function outputToHead(): void
    {
        $css = $this->getGlobalCss();
        if (!empty($css)) {
            JTB_CSS_Output::enqueue($css, 'jtb-style-system-css');
        }
    }

    /**
     * Get CSS for inline output
     */
    public function getCssForInline(): string
    {
        return '<style id="jtb-style-system-css">' . $this->getGlobalCss() . '</style>';
    }
}
