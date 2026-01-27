<?php
/**
 * JTB CSS Generator Class
 * Generates CSS from global theme settings
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_CSS_Generator
{
    /**
     * Generate complete global CSS from theme settings
     */
    public static function generateGlobalCss(?array $settings = null): string
    {
        if ($settings === null) {
            $settings = JTB_Theme_Settings::getAll();
        }

        $css = self::generateCssVariables($settings);
        $css .= self::generateBaseStyles($settings);
        $css .= self::generateTypographyStyles($settings);
        $css .= self::generateButtonStyles($settings);
        $css .= self::generateFormStyles($settings);
        $css .= self::generateHeaderStyles($settings);
        $css .= self::generateMenuStyles($settings);
        $css .= self::generateFooterStyles($settings);
        $css .= self::generateBlogStyles($settings);
        $css .= self::generateResponsiveStyles($settings);

        return $css;
    }

    /**
     * Generate CSS custom properties (variables)
     */
    private static function generateCssVariables(array $settings): string
    {
        $css = ":root {\n";

        // Colors
        $colors = $settings['colors'] ?? [];
        foreach ($colors as $key => $value) {
            $cssVar = '--jtb-' . str_replace('_', '-', $key);
            $css .= "    {$cssVar}: {$value};\n";
        }

        // Typography
        $typography = $settings['typography'] ?? [];
        $css .= "    --jtb-body-font: " . self::getFontStack($typography['body_font'] ?? 'Inter') . ";\n";
        $css .= "    --jtb-heading-font: " . self::getFontStack($typography['heading_font'] ?? 'Inter') . ";\n";
        $css .= "    --jtb-body-size: " . ($typography['body_size'] ?? '16') . ($typography['body_size_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-body-weight: " . ($typography['body_weight'] ?? '400') . ";\n";
        $css .= "    --jtb-body-line-height: " . ($typography['body_line_height'] ?? '1.6') . ";\n";
        $css .= "    --jtb-heading-weight: " . ($typography['heading_weight'] ?? '700') . ";\n";
        $css .= "    --jtb-heading-line-height: " . ($typography['heading_line_height'] ?? '1.2') . ";\n";
        $css .= "    --jtb-heading-letter-spacing: " . ($typography['heading_letter_spacing'] ?? '-0.02') . "em;\n";

        // Heading sizes
        for ($i = 1; $i <= 6; $i++) {
            $size = $typography["h{$i}_size"] ?? (48 - ($i - 1) * 6);
            $unit = $typography["h{$i}_size_unit"] ?? 'px';
            $css .= "    --jtb-h{$i}-size: {$size}{$unit};\n";
        }

        // Layout
        $layout = $settings['layout'] ?? [];
        $css .= "    --jtb-content-width: " . ($layout['content_width'] ?? '1200') . ($layout['content_width_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-gutter-width: " . ($layout['gutter_width'] ?? '30') . ($layout['gutter_width_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-section-padding-top: " . ($layout['section_padding_top'] ?? '80') . ($layout['section_padding_top_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-section-padding-bottom: " . ($layout['section_padding_bottom'] ?? '80') . ($layout['section_padding_bottom_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-row-gap: " . ($layout['row_gap'] ?? '30') . ($layout['row_gap_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-column-gap: " . ($layout['column_gap'] ?? '30') . ($layout['column_gap_unit'] ?? 'px') . ";\n";

        // Buttons
        $buttons = $settings['buttons'] ?? [];
        $css .= "    --jtb-button-bg: " . ($buttons['button_bg_color'] ?? '#7c3aed') . ";\n";
        $css .= "    --jtb-button-text: " . ($buttons['button_text_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-button-border-width: " . ($buttons['button_border_width'] ?? '0') . "px;\n";
        $css .= "    --jtb-button-border-color: " . ($buttons['button_border_color'] ?? '#7c3aed') . ";\n";
        $css .= "    --jtb-button-radius: " . ($buttons['button_border_radius'] ?? '8') . ($buttons['button_border_radius_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-button-padding: " . ($buttons['button_padding_tb'] ?? '12') . ($buttons['button_padding_unit'] ?? 'px') . " " . ($buttons['button_padding_lr'] ?? '24') . ($buttons['button_padding_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-button-font-size: " . ($buttons['button_font_size'] ?? '16') . ($buttons['button_font_size_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-button-font-weight: " . ($buttons['button_font_weight'] ?? '600') . ";\n";
        $css .= "    --jtb-button-hover-bg: " . ($buttons['button_hover_bg'] ?? '#5b21b6') . ";\n";
        $css .= "    --jtb-button-hover-text: " . ($buttons['button_hover_text'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-button-hover-border: " . ($buttons['button_hover_border'] ?? '#5b21b6') . ";\n";
        $css .= "    --jtb-button-transition: " . ($buttons['button_transition'] ?? '0.2') . "s;\n";

        // Forms
        $forms = $settings['forms'] ?? [];
        $css .= "    --jtb-input-bg: " . ($forms['input_bg_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-input-text: " . ($forms['input_text_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-input-border: " . ($forms['input_border_color'] ?? '#d1d5db') . ";\n";
        $css .= "    --jtb-input-border-width: " . ($forms['input_border_width'] ?? '1') . "px;\n";
        $css .= "    --jtb-input-radius: " . ($forms['input_border_radius'] ?? '6') . "px;\n";
        $css .= "    --jtb-input-padding: " . ($forms['input_padding_tb'] ?? '10') . "px " . ($forms['input_padding_lr'] ?? '14') . "px;\n";
        $css .= "    --jtb-input-font-size: " . ($forms['input_font_size'] ?? '16') . "px;\n";
        $css .= "    --jtb-input-focus-border: " . ($forms['input_focus_border_color'] ?? '#7c3aed') . ";\n";
        $css .= "    --jtb-input-focus-shadow: " . ($forms['input_focus_shadow'] ?? '0 0 0 3px rgba(124, 58, 237, 0.1)') . ";\n";
        $css .= "    --jtb-placeholder: " . ($forms['placeholder_color'] ?? '#9ca3af') . ";\n";
        $css .= "    --jtb-label-color: " . ($forms['label_color'] ?? '#374151') . ";\n";

        // Header
        $header = $settings['header'] ?? [];
        $css .= "    --jtb-header-bg: " . ($header['header_bg_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-header-text: " . ($header['header_text_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-header-height: " . ($header['header_height'] ?? '80') . ($header['header_height_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-header-shadow: " . ($header['header_shadow'] ?? '0 1px 3px rgba(0,0,0,0.1)') . ";\n";
        $css .= "    --jtb-header-sticky-bg: " . ($header['header_sticky_bg'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-header-sticky-shadow: " . ($header['header_sticky_shadow'] ?? '0 2px 10px rgba(0,0,0,0.1)') . ";\n";
        $css .= "    --jtb-logo-height: " . ($header['logo_height'] ?? '50') . ($header['logo_height_unit'] ?? 'px') . ";\n";
        $css .= "    --jtb-logo-height-sticky: " . ($header['logo_height_sticky'] ?? '40') . "px;\n";

        // Menu
        $menu = $settings['menu'] ?? [];
        $css .= "    --jtb-menu-font-size: " . ($menu['menu_font_size'] ?? '16') . "px;\n";
        $css .= "    --jtb-menu-font-weight: " . ($menu['menu_font_weight'] ?? '500') . ";\n";
        $css .= "    --jtb-menu-link-color: " . ($menu['menu_link_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-menu-link-hover: " . ($menu['menu_link_hover_color'] ?? '#7c3aed') . ";\n";
        $css .= "    --jtb-menu-link-active: " . ($menu['menu_link_active_color'] ?? '#7c3aed') . ";\n";
        $css .= "    --jtb-dropdown-bg: " . ($menu['dropdown_bg_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-dropdown-text: " . ($menu['dropdown_text_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-dropdown-hover-bg: " . ($menu['dropdown_hover_bg'] ?? '#f3f4f6') . ";\n";
        $css .= "    --jtb-dropdown-radius: " . ($menu['dropdown_border_radius'] ?? '8') . "px;\n";
        $css .= "    --jtb-dropdown-shadow: " . ($menu['dropdown_shadow'] ?? '0 10px 40px rgba(0,0,0,0.15)') . ";\n";
        $css .= "    --jtb-mobile-breakpoint: " . ($menu['mobile_breakpoint'] ?? '980') . "px;\n";
        $css .= "    --jtb-hamburger-color: " . ($menu['hamburger_color'] ?? '#1f2937') . ";\n";

        // Footer
        $footer = $settings['footer'] ?? [];
        $css .= "    --jtb-footer-bg: " . ($footer['footer_bg_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-footer-text: " . ($footer['footer_text_color'] ?? '#d1d5db') . ";\n";
        $css .= "    --jtb-footer-heading: " . ($footer['footer_heading_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-footer-link: " . ($footer['footer_link_color'] ?? '#d1d5db') . ";\n";
        $css .= "    --jtb-footer-link-hover: " . ($footer['footer_link_hover_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-footer-padding-top: " . ($footer['footer_padding_top'] ?? '60') . "px;\n";
        $css .= "    --jtb-footer-padding-bottom: " . ($footer['footer_padding_bottom'] ?? '60') . "px;\n";
        $css .= "    --jtb-copyright-bg: " . ($footer['copyright_bg_color'] ?? '#111827') . ";\n";
        $css .= "    --jtb-copyright-text: " . ($footer['copyright_text_color'] ?? '#9ca3af') . ";\n";

        // Blog
        $blog = $settings['blog'] ?? [];
        $css .= "    --jtb-blog-columns: " . ($blog['blog_columns'] ?? '3') . ";\n";
        $css .= "    --jtb-blog-gap: " . ($blog['blog_gap'] ?? '30') . "px;\n";
        $css .= "    --jtb-post-card-bg: " . ($blog['post_card_bg'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-post-card-radius: " . ($blog['post_card_border_radius'] ?? '12') . "px;\n";
        $css .= "    --jtb-post-card-shadow: " . ($blog['post_card_shadow'] ?? '0 4px 6px rgba(0,0,0,0.07)') . ";\n";
        $css .= "    --jtb-post-card-hover-shadow: " . ($blog['post_card_hover_shadow'] ?? '0 10px 40px rgba(0,0,0,0.12)') . ";\n";

        // Responsive breakpoints
        $responsive = $settings['responsive'] ?? [];
        $css .= "    --jtb-tablet-breakpoint: " . ($responsive['tablet_breakpoint'] ?? '980') . "px;\n";
        $css .= "    --jtb-phone-breakpoint: " . ($responsive['phone_breakpoint'] ?? '767') . "px;\n";

        $css .= "}\n\n";

        return $css;
    }

    /**
     * Generate base styles
     */
    private static function generateBaseStyles(array $settings): string
    {
        return <<<CSS
/* ========================================
   JTB Global Base Styles
   ======================================== */

body.jtb-page {
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-body-size);
    font-weight: var(--jtb-body-weight);
    line-height: var(--jtb-body-line-height);
    color: var(--jtb-text-color);
    background-color: var(--jtb-background-color);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.jtb-page a {
    color: var(--jtb-link-color);
    text-decoration: none;
    transition: color 0.2s ease;
}

.jtb-page a:hover {
    color: var(--jtb-link-hover-color);
}

/* Layout */
.jtb-section-inner {
    max-width: var(--jtb-content-width);
    margin-left: auto;
    margin-right: auto;
    padding-left: calc(var(--jtb-gutter-width) / 2);
    padding-right: calc(var(--jtb-gutter-width) / 2);
}

.jtb-section {
    padding-top: var(--jtb-section-padding-top);
    padding-bottom: var(--jtb-section-padding-bottom);
}

.jtb-section-fullwidth .jtb-section-inner {
    max-width: 100%;
    padding-left: 0;
    padding-right: 0;
}

.jtb-row {
    display: flex;
    flex-wrap: wrap;
    gap: var(--jtb-row-gap);
}

.jtb-column {
    flex: 1;
    min-width: 0;
}

CSS;
    }

    /**
     * Generate typography styles
     */
    private static function generateTypographyStyles(array $settings): string
    {
        return <<<CSS
/* ========================================
   Typography
   ======================================== */

.jtb-page h1, .jtb-page h2, .jtb-page h3,
.jtb-page h4, .jtb-page h5, .jtb-page h6 {
    font-family: var(--jtb-heading-font);
    font-weight: var(--jtb-heading-weight);
    line-height: var(--jtb-heading-line-height);
    letter-spacing: var(--jtb-heading-letter-spacing);
    color: var(--jtb-heading-color);
    margin-top: 0;
    margin-bottom: 0.5em;
}

.jtb-page h1 { font-size: var(--jtb-h1-size); }
.jtb-page h2 { font-size: var(--jtb-h2-size); }
.jtb-page h3 { font-size: var(--jtb-h3-size); }
.jtb-page h4 { font-size: var(--jtb-h4-size); }
.jtb-page h5 { font-size: var(--jtb-h5-size); }
.jtb-page h6 { font-size: var(--jtb-h6-size); }

.jtb-page p {
    margin-top: 0;
    margin-bottom: 1em;
}

.jtb-page .jtb-text-light {
    color: var(--jtb-text-light-color);
}

CSS;
    }

    /**
     * Generate button styles
     */
    private static function generateButtonStyles(array $settings): string
    {
        $buttons = $settings['buttons'] ?? [];
        $textTransform = $buttons['button_text_transform'] ?? 'none';
        $letterSpacing = $buttons['button_letter_spacing'] ?? '0';

        return <<<CSS
/* ========================================
   Buttons
   ======================================== */

.jtb-button,
.jtb-page .jtb-button,
.jtb-page button.jtb-button,
.jtb-page a.jtb-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: var(--jtb-button-bg);
    color: var(--jtb-button-text);
    border: var(--jtb-button-border-width) solid var(--jtb-button-border-color);
    border-radius: var(--jtb-button-radius);
    padding: var(--jtb-button-padding);
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-button-font-size);
    font-weight: var(--jtb-button-font-weight);
    text-transform: {$textTransform};
    letter-spacing: {$letterSpacing}em;
    text-decoration: none;
    cursor: pointer;
    transition: all var(--jtb-button-transition) ease;
}

.jtb-button:hover,
.jtb-page .jtb-button:hover {
    background-color: var(--jtb-button-hover-bg);
    color: var(--jtb-button-hover-text);
    border-color: var(--jtb-button-hover-border);
}

.jtb-button-secondary {
    background-color: transparent;
    color: var(--jtb-button-bg);
    border-color: var(--jtb-button-bg);
}

.jtb-button-secondary:hover {
    background-color: var(--jtb-button-bg);
    color: var(--jtb-button-text);
}

CSS;
    }

    /**
     * Generate form styles
     */
    private static function generateFormStyles(array $settings): string
    {
        $forms = $settings['forms'] ?? [];
        $labelSize = $forms['label_font_size'] ?? '14';
        $labelWeight = $forms['label_font_weight'] ?? '500';
        $labelMargin = $forms['label_margin_bottom'] ?? '6';

        return <<<CSS
/* ========================================
   Forms
   ======================================== */

.jtb-page input[type="text"],
.jtb-page input[type="email"],
.jtb-page input[type="tel"],
.jtb-page input[type="url"],
.jtb-page input[type="password"],
.jtb-page input[type="number"],
.jtb-page input[type="search"],
.jtb-page input[type="date"],
.jtb-page textarea,
.jtb-page select {
    width: 100%;
    background-color: var(--jtb-input-bg);
    color: var(--jtb-input-text);
    border: var(--jtb-input-border-width) solid var(--jtb-input-border);
    border-radius: var(--jtb-input-radius);
    padding: var(--jtb-input-padding);
    font-family: var(--jtb-body-font);
    font-size: var(--jtb-input-font-size);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.jtb-page input:focus,
.jtb-page textarea:focus,
.jtb-page select:focus {
    outline: none;
    border-color: var(--jtb-input-focus-border);
    box-shadow: var(--jtb-input-focus-shadow);
}

.jtb-page input::placeholder,
.jtb-page textarea::placeholder {
    color: var(--jtb-placeholder);
}

.jtb-page label {
    display: block;
    color: var(--jtb-label-color);
    font-size: {$labelSize}px;
    font-weight: {$labelWeight};
    margin-bottom: {$labelMargin}px;
}

.jtb-page .jtb-form-group {
    margin-bottom: 20px;
}

CSS;
    }

    /**
     * Generate header styles
     */
    private static function generateHeaderStyles(array $settings): string
    {
        $header = $settings['header'] ?? [];
        $paddingLr = $header['header_padding_lr'] ?? '30';
        $transparentText = $header['header_transparent_text'] ?? '#ffffff';

        return <<<CSS
/* ========================================
   Header
   ======================================== */

.jtb-site-header {
    background-color: var(--jtb-header-bg);
    color: var(--jtb-header-text);
    min-height: var(--jtb-header-height);
    box-shadow: var(--jtb-header-shadow);
    padding-left: {$paddingLr}px;
    padding-right: {$paddingLr}px;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 100;
}

.jtb-site-header.jtb-sticky {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    transition: all 0.3s ease;
}

.jtb-site-header.jtb-sticky.scrolled {
    background-color: var(--jtb-header-sticky-bg);
    box-shadow: var(--jtb-header-sticky-shadow);
}

.jtb-site-header.jtb-transparent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background-color: transparent;
    box-shadow: none;
}

.jtb-site-header.jtb-transparent:not(.scrolled) {
    color: {$transparentText};
}

.jtb-site-header.jtb-transparent:not(.scrolled) .jtb-menu-link {
    color: {$transparentText};
}

.jtb-site-header .jtb-logo img {
    height: var(--jtb-logo-height);
    width: auto;
    transition: height 0.3s ease;
}

.jtb-site-header.jtb-sticky.scrolled .jtb-logo img {
    height: var(--jtb-logo-height-sticky);
}

/* Offset body when header is sticky */
body.jtb-has-sticky-header {
    padding-top: var(--jtb-header-height);
}

body.jtb-has-transparent-header .jtb-site-main {
    margin-top: calc(-1 * var(--jtb-header-height));
}

CSS;
    }

    /**
     * Generate menu styles
     */
    private static function generateMenuStyles(array $settings): string
    {
        $menu = $settings['menu'] ?? [];
        $linkPaddingTb = $menu['menu_link_padding_tb'] ?? '10';
        $linkPaddingLr = $menu['menu_link_padding_lr'] ?? '16';
        $textTransform = $menu['menu_text_transform'] ?? 'none';
        $letterSpacing = $menu['menu_letter_spacing'] ?? '0';
        $fontFamily = $menu['menu_font_family'] ?? 'inherit';
        $mobileMenuBg = $menu['mobile_menu_bg'] ?? '#ffffff';
        $mobileMenuText = $menu['mobile_menu_text'] ?? '#1f2937';

        $fontStack = $fontFamily === 'inherit' ? 'inherit' : self::getFontStack($fontFamily);

        return <<<CSS
/* ========================================
   Navigation Menu
   ======================================== */

.jtb-main-nav {
    display: flex;
    align-items: center;
}

.jtb-menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 4px;
}

.jtb-menu-item {
    position: relative;
}

.jtb-menu-link {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: {$linkPaddingTb}px {$linkPaddingLr}px;
    color: var(--jtb-menu-link-color);
    font-family: {$fontStack};
    font-size: var(--jtb-menu-font-size);
    font-weight: var(--jtb-menu-font-weight);
    text-transform: {$textTransform};
    letter-spacing: {$letterSpacing}em;
    text-decoration: none;
    transition: color 0.2s ease;
}

.jtb-menu-link:hover {
    color: var(--jtb-menu-link-hover);
}

.jtb-menu-item.active > .jtb-menu-link,
.jtb-menu-item.current > .jtb-menu-link {
    color: var(--jtb-menu-link-active);
}

/* Dropdown */
.jtb-menu-item .jtb-submenu {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 220px;
    background-color: var(--jtb-dropdown-bg);
    border-radius: var(--jtb-dropdown-radius);
    box-shadow: var(--jtb-dropdown-shadow);
    padding: 8px 0;
    opacity: 0;
    visibility: hidden;
    transform: translateY(10px);
    transition: all 0.2s ease;
    z-index: 100;
}

.jtb-menu-item:hover > .jtb-submenu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.jtb-submenu .jtb-menu-link {
    color: var(--jtb-dropdown-text);
    padding: 10px 20px;
}

.jtb-submenu .jtb-menu-link:hover {
    background-color: var(--jtb-dropdown-hover-bg);
}

/* Mobile menu toggle */
.jtb-menu-toggle {
    display: none;
    background: transparent;
    border: none;
    padding: 10px;
    cursor: pointer;
}

.jtb-hamburger {
    display: block;
    width: 24px;
    height: 2px;
    background-color: var(--jtb-hamburger-color);
    position: relative;
    transition: background-color 0.2s ease;
}

.jtb-hamburger::before,
.jtb-hamburger::after {
    content: '';
    position: absolute;
    left: 0;
    width: 100%;
    height: 2px;
    background-color: var(--jtb-hamburger-color);
    transition: transform 0.2s ease;
}

.jtb-hamburger::before { top: -7px; }
.jtb-hamburger::after { bottom: -7px; }

/* Mobile styles */
@media (max-width: var(--jtb-mobile-breakpoint)) {
    .jtb-menu-toggle {
        display: block;
    }

    .jtb-main-nav {
        position: fixed;
        top: 0;
        left: -100%;
        width: 280px;
        height: 100vh;
        background-color: {$mobileMenuBg};
        padding: 80px 20px 20px;
        transition: left 0.3s ease;
        z-index: 999;
        overflow-y: auto;
    }

    .jtb-main-nav.open {
        left: 0;
    }

    .jtb-menu {
        flex-direction: column;
        gap: 0;
    }

    .jtb-menu-link {
        color: {$mobileMenuText};
        padding: 15px 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .jtb-menu-item .jtb-submenu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        padding-left: 20px;
    }

    .jtb-mobile-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        z-index: 998;
    }

    .jtb-mobile-overlay.open {
        opacity: 1;
        visibility: visible;
    }
}

CSS;
    }

    /**
     * Generate footer styles
     */
    private static function generateFooterStyles(array $settings): string
    {
        $footer = $settings['footer'] ?? [];
        $columns = $footer['footer_columns'] ?? '4';
        $copyrightPadding = $footer['copyright_padding_tb'] ?? '20';

        return <<<CSS
/* ========================================
   Footer
   ======================================== */

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

.jtb-footer-columns {
    display: grid;
    grid-template-columns: repeat({$columns}, 1fr);
    gap: var(--jtb-gutter-width);
}

.jtb-footer-widget {
    margin-bottom: 30px;
}

.jtb-footer-widget h4 {
    margin-bottom: 20px;
}

.jtb-footer-widget ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.jtb-footer-widget li {
    margin-bottom: 10px;
}

.jtb-copyright {
    background-color: var(--jtb-copyright-bg);
    color: var(--jtb-copyright-text);
    padding: {$copyrightPadding}px 0;
    text-align: center;
    font-size: 14px;
}

@media (max-width: 980px) {
    .jtb-footer-columns {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .jtb-footer-columns {
        grid-template-columns: 1fr;
    }
}

CSS;
    }

    /**
     * Generate blog styles
     */
    private static function generateBlogStyles(array $settings): string
    {
        $blog = $settings['blog'] ?? [];
        $layout = $blog['blog_layout'] ?? 'grid';

        return <<<CSS
/* ========================================
   Blog
   ======================================== */

.jtb-blog-grid {
    display: grid;
    grid-template-columns: repeat(var(--jtb-blog-columns), 1fr);
    gap: var(--jtb-blog-gap);
}

.jtb-blog-list {
    display: flex;
    flex-direction: column;
    gap: var(--jtb-blog-gap);
}

.jtb-post-card {
    background-color: var(--jtb-post-card-bg);
    border-radius: var(--jtb-post-card-radius);
    box-shadow: var(--jtb-post-card-shadow);
    overflow: hidden;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.jtb-post-card:hover {
    box-shadow: var(--jtb-post-card-hover-shadow);
    transform: translateY(-4px);
}

.jtb-post-card-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16/9;
}

.jtb-post-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.jtb-post-card:hover .jtb-post-card-image img {
    transform: scale(1.05);
}

.jtb-post-card-content {
    padding: 24px;
}

.jtb-post-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 13px;
    color: var(--jtb-text-light-color);
}

.jtb-post-card-title {
    font-size: 20px;
    margin-bottom: 12px;
}

.jtb-post-card-title a {
    color: var(--jtb-heading-color);
}

.jtb-post-card-title a:hover {
    color: var(--jtb-link-color);
}

.jtb-post-card-excerpt {
    color: var(--jtb-text-light-color);
    margin-bottom: 16px;
}

.jtb-read-more {
    font-weight: 600;
    color: var(--jtb-link-color);
}

/* Pagination */
.jtb-pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 40px;
}

.jtb-pagination a,
.jtb-pagination span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 12px;
    border-radius: 8px;
    background-color: var(--jtb-surface-color);
    color: var(--jtb-text-color);
    font-weight: 500;
    transition: all 0.2s ease;
}

.jtb-pagination a:hover {
    background-color: var(--jtb-primary-color);
    color: #ffffff;
}

.jtb-pagination .current {
    background-color: var(--jtb-primary-color);
    color: #ffffff;
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
     * Generate responsive styles
     */
    private static function generateResponsiveStyles(array $settings): string
    {
        $responsive = $settings['responsive'] ?? [];
        $tabletBreakpoint = $responsive['tablet_breakpoint'] ?? '980';
        $phoneBreakpoint = $responsive['phone_breakpoint'] ?? '767';

        $h1Tablet = $responsive['h1_size_tablet'] ?? '36';
        $h1Phone = $responsive['h1_size_phone'] ?? '28';
        $h2Tablet = $responsive['h2_size_tablet'] ?? '28';
        $h2Phone = $responsive['h2_size_phone'] ?? '24';
        $bodyTablet = $responsive['body_size_tablet'] ?? '15';
        $bodyPhone = $responsive['body_size_phone'] ?? '14';
        $sectionPaddingTablet = $responsive['section_padding_tablet'] ?? '60';
        $sectionPaddingPhone = $responsive['section_padding_phone'] ?? '40';

        return <<<CSS
/* ========================================
   Responsive Styles
   ======================================== */

@media (max-width: {$tabletBreakpoint}px) {
    :root {
        --jtb-h1-size: {$h1Tablet}px;
        --jtb-h2-size: {$h2Tablet}px;
        --jtb-body-size: {$bodyTablet}px;
        --jtb-section-padding-top: {$sectionPaddingTablet}px;
        --jtb-section-padding-bottom: {$sectionPaddingTablet}px;
    }

    .jtb-section-inner {
        padding-left: 20px;
        padding-right: 20px;
    }

    .jtb-row {
        flex-direction: column;
    }

    .jtb-column {
        width: 100%;
    }
}

@media (max-width: {$phoneBreakpoint}px) {
    :root {
        --jtb-h1-size: {$h1Phone}px;
        --jtb-h2-size: {$h2Phone}px;
        --jtb-body-size: {$bodyPhone}px;
        --jtb-section-padding-top: {$sectionPaddingPhone}px;
        --jtb-section-padding-bottom: {$sectionPaddingPhone}px;
    }

    .jtb-section-inner {
        padding-left: 15px;
        padding-right: 15px;
    }
}

/* Visibility utilities */
.jtb-hide-desktop {
    display: block;
}

.jtb-hide-tablet {
    display: block;
}

.jtb-hide-phone {
    display: block;
}

@media (min-width: {$tabletBreakpoint}px) {
    .jtb-hide-desktop {
        display: none !important;
    }
}

@media (max-width: {$tabletBreakpoint}px) and (min-width: {$phoneBreakpoint}px) {
    .jtb-hide-tablet {
        display: none !important;
    }
}

@media (max-width: {$phoneBreakpoint}px) {
    .jtb-hide-phone {
        display: none !important;
    }
}

CSS;
    }

    /**
     * Get font stack for font family
     */
    private static function getFontStack(string $font): string
    {
        $stacks = [
            'Arial' => 'Arial, sans-serif',
            'Helvetica' => 'Helvetica, Arial, sans-serif',
            'Georgia' => 'Georgia, serif',
            'Times New Roman' => '"Times New Roman", Times, serif',
            'Verdana' => 'Verdana, Geneva, sans-serif',
            'Courier New' => '"Courier New", monospace',
            'system-ui' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
        ];

        if (isset($stacks[$font])) {
            return $stacks[$font];
        }

        // Google Font - wrap in quotes if has spaces
        if (strpos($font, ' ') !== false) {
            return '"' . $font . '", sans-serif';
        }

        return $font . ', sans-serif';
    }

    /**
     * Minify CSS
     */
    public static function minify(string $css): string
    {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        // Remove whitespace
        $css = preg_replace('/\s+/', ' ', $css);
        // Remove unnecessary spaces
        $css = preg_replace('/\s*([{};:,>+~])\s*/', '$1', $css);
        // Remove trailing semicolons before }
        $css = preg_replace('/;}/', '}', $css);

        return trim($css);
    }

    /**
     * Generate and cache CSS file
     */
    public static function generateCssFile(?array $settings = null): string
    {
        $css = self::generateGlobalCss($settings);
        $minified = self::minify($css);

        $cacheDir = CMS_ROOT . '/cache/jtb';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $filename = 'theme-' . md5($minified) . '.css';
        $filepath = $cacheDir . '/' . $filename;

        if (!file_exists($filepath)) {
            file_put_contents($filepath, $minified);
        }

        return '/cache/jtb/' . $filename;
    }
}
