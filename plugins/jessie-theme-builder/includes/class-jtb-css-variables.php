<?php
/**
 * JTB CSS Variables Generator
 *
 * Generuje CSS Custom Properties (zmienne CSS) z ustawień Theme Settings i Global Settings.
 * Zmienne są używane w bazowych stylach frontend.css oraz generowanych stylach modułów.
 *
 * Architektura:
 * 1. Globalne zmienne (:root) - kolory, typografia, spacing itd.
 * 2. Zmienne per moduł - domyślne style dla każdego typu modułu
 * 3. Responsywne zmienne - różne wartości dla tablet/phone
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_CSS_Variables
{
    /**
     * Theme Settings instance
     */
    private ?JTB_Theme_Settings $themeSettings = null;

    /**
     * Cached CSS output
     */
    private ?string $cachedCss = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (class_exists('\\JessieThemeBuilder\\JTB_Theme_Settings')) {
            $this->themeSettings = new JTB_Theme_Settings();
        }
    }

    /**
     * Generate all CSS variables
     *
     * @param bool $includeModuleDefaults Whether to include module-specific defaults
     * @return string Complete CSS with :root variables
     */
    public function generate(bool $includeModuleDefaults = true): string
    {
        if ($this->cachedCss !== null) {
            return $this->cachedCss;
        }

        $css = "/* ==========================================\n";
        $css .= "   JTB CSS Custom Properties\n";
        $css .= "   Auto-generated from Theme Settings\n";
        $css .= "   ========================================== */\n\n";

        // Root variables
        $css .= $this->generateRootVariables();

        // Module-specific variables
        if ($includeModuleDefaults) {
            $css .= $this->generateModuleVariables();
        }

        // Responsive overrides
        $css .= $this->generateResponsiveVariables();

        // Dark mode support (optional)
        $css .= $this->generateDarkModeVariables();

        $this->cachedCss = $css;

        return $css;
    }

    /**
     * Generate :root variables from Theme Settings
     */
    private function generateRootVariables(): string
    {
        $settings = $this->themeSettings ? $this->themeSettings->getSettings() : [];
        $defaults = JTB_Global_Settings::getGlobalDefaults();

        $css = ":root {\n";
        $css .= "    /* ====== COLORS ====== */\n";

        // Primary colors
        $css .= "    --jtb-primary-color: " . ($settings['primary_color'] ?? '#6366f1') . ";\n";
        $css .= "    --jtb-primary-hover: " . $this->darkenColor($settings['primary_color'] ?? '#6366f1', 10) . ";\n";
        $css .= "    --jtb-primary-light: " . $this->lightenColor($settings['primary_color'] ?? '#6366f1', 40) . ";\n";
        $css .= "    --jtb-secondary-color: " . ($settings['secondary_color'] ?? '#8b5cf6') . ";\n";
        $css .= "    --jtb-accent-color: " . ($settings['accent_color'] ?? '#06b6d4') . ";\n";

        // Text colors
        $css .= "\n    /* Text Colors */\n";
        $css .= "    --jtb-text-color: " . ($settings['text_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-text-light-color: " . ($settings['text_light_color'] ?? '#6b7280') . ";\n";
        $css .= "    --jtb-heading-color: " . ($settings['heading_color'] ?? '#111827') . ";\n";
        $css .= "    --jtb-link-color: " . ($settings['link_color'] ?? '#6366f1') . ";\n";
        $css .= "    --jtb-link-hover-color: " . ($settings['link_hover_color'] ?? '#4f46e5') . ";\n";

        // Background colors
        $css .= "\n    /* Background Colors */\n";
        $css .= "    --jtb-background-color: " . ($settings['background_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-surface-color: " . ($settings['surface_color'] ?? '#f8fafc') . ";\n";
        $css .= "    --jtb-border-color: " . ($settings['border_color'] ?? '#e5e7eb') . ";\n";

        // Status colors
        $css .= "\n    /* Status Colors */\n";
        $css .= "    --jtb-success-color: " . ($settings['success_color'] ?? '#10b981') . ";\n";
        $css .= "    --jtb-warning-color: " . ($settings['warning_color'] ?? '#f59e0b') . ";\n";
        $css .= "    --jtb-error-color: " . ($settings['error_color'] ?? '#ef4444') . ";\n";
        $css .= "    --jtb-info-color: " . ($settings['info_color'] ?? '#3b82f6') . ";\n";

        // Typography
        $css .= "\n    /* ====== TYPOGRAPHY ====== */\n";
        $css .= "    --jtb-body-font: " . ($settings['body_font'] ?? 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif') . ";\n";
        $css .= "    --jtb-body-size: " . ($settings['body_size'] ?? '16px') . ";\n";
        $css .= "    --jtb-body-weight: " . ($settings['body_weight'] ?? '400') . ";\n";
        $css .= "    --jtb-body-line-height: " . ($settings['body_line_height'] ?? '1.6') . ";\n";
        $css .= "    --jtb-heading-font: " . ($settings['heading_font'] ?? 'inherit') . ";\n";
        $css .= "    --jtb-heading-weight: " . ($settings['heading_weight'] ?? '700') . ";\n";
        $css .= "    --jtb-heading-line-height: " . ($settings['heading_line_height'] ?? '1.3') . ";\n";
        $css .= "    --jtb-heading-letter-spacing: " . ($settings['heading_letter_spacing'] ?? '-0.02em') . ";\n";

        // Heading sizes
        $css .= "\n    /* Heading Sizes */\n";
        $css .= "    --jtb-h1-size: " . ($settings['h1_size'] ?? '48px') . ";\n";
        $css .= "    --jtb-h2-size: " . ($settings['h2_size'] ?? '36px') . ";\n";
        $css .= "    --jtb-h3-size: " . ($settings['h3_size'] ?? '28px') . ";\n";
        $css .= "    --jtb-h4-size: " . ($settings['h4_size'] ?? '24px') . ";\n";
        $css .= "    --jtb-h5-size: " . ($settings['h5_size'] ?? '20px') . ";\n";
        $css .= "    --jtb-h6-size: " . ($settings['h6_size'] ?? '18px') . ";\n";

        // Layout
        $css .= "\n    /* ====== LAYOUT ====== */\n";
        $css .= "    --jtb-content-width: " . ($settings['content_width'] ?? '1200px') . ";\n";
        $css .= "    --jtb-gutter-width: " . ($settings['gutter_width'] ?? '30px') . ";\n";
        $css .= "    --jtb-section-padding-top: " . ($settings['section_padding_top'] ?? '60px') . ";\n";
        $css .= "    --jtb-section-padding-bottom: " . ($settings['section_padding_bottom'] ?? '60px') . ";\n";
        $css .= "    --jtb-row-gap: " . ($settings['row_gap'] ?? '30px') . ";\n";
        $css .= "    --jtb-column-gap: " . ($settings['column_gap'] ?? '30px') . ";\n";

        // Borders & Radius
        $css .= "\n    /* ====== BORDERS & RADIUS ====== */\n";
        $css .= "    --jtb-border-radius: " . ($settings['border_radius'] ?? '8px') . ";\n";
        $css .= "    --jtb-border-radius-sm: " . ($settings['border_radius_sm'] ?? '4px') . ";\n";
        $css .= "    --jtb-border-radius-lg: " . ($settings['border_radius_lg'] ?? '12px') . ";\n";
        $css .= "    --jtb-border-radius-xl: " . ($settings['border_radius_xl'] ?? '16px') . ";\n";
        $css .= "    --jtb-border-radius-full: 9999px;\n";

        // Shadows
        $css .= "\n    /* ====== SHADOWS ====== */\n";
        $css .= "    --jtb-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);\n";
        $css .= "    --jtb-shadow: 0 1px 3px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.06);\n";
        $css .= "    --jtb-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n";
        $css .= "    --jtb-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.05);\n";
        $css .= "    --jtb-shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04);\n";
        $css .= "    --jtb-shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15);\n";
        $css .= "    --jtb-shadow-inner: inset 0 2px 4px rgba(0, 0, 0, 0.06);\n";

        // Transitions
        $css .= "\n    /* ====== TRANSITIONS ====== */\n";
        $css .= "    --jtb-transition-fast: 150ms ease;\n";
        $css .= "    --jtb-transition: 300ms ease;\n";
        $css .= "    --jtb-transition-slow: 500ms ease;\n";
        $css .= "    --jtb-transition-bounce: 500ms cubic-bezier(0.68, -0.55, 0.265, 1.55);\n";
        $css .= "    --jtb-transition-smooth: 400ms cubic-bezier(0.4, 0, 0.2, 1);\n";

        // Z-index scale
        $css .= "\n    /* ====== Z-INDEX SCALE ====== */\n";
        $css .= "    --jtb-z-dropdown: 1000;\n";
        $css .= "    --jtb-z-sticky: 1020;\n";
        $css .= "    --jtb-z-fixed: 1030;\n";
        $css .= "    --jtb-z-modal-backdrop: 1040;\n";
        $css .= "    --jtb-z-modal: 1050;\n";
        $css .= "    --jtb-z-popover: 1060;\n";
        $css .= "    --jtb-z-tooltip: 1070;\n";

        // Button defaults
        $css .= "\n    /* ====== BUTTONS ====== */\n";
        $css .= "    --jtb-btn-font-size: " . ($settings['button_font_size'] ?? '16px') . ";\n";
        $css .= "    --jtb-btn-font-weight: " . ($settings['button_font_weight'] ?? '600') . ";\n";
        $css .= "    --jtb-btn-padding-y: " . ($settings['button_padding_tb'] ?? '14px') . ";\n";
        $css .= "    --jtb-btn-padding-x: " . ($settings['button_padding_lr'] ?? '28px') . ";\n";
        $css .= "    --jtb-btn-border-radius: " . ($settings['button_border_radius'] ?? '8px') . ";\n";
        $css .= "    --jtb-btn-border-width: " . ($settings['button_border_width'] ?? '0') . ";\n";
        $css .= "    --jtb-btn-bg: " . ($settings['button_bg_color'] ?? 'var(--jtb-primary-color)') . ";\n";
        $css .= "    --jtb-btn-color: " . ($settings['button_text_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-btn-hover-bg: " . ($settings['button_hover_bg'] ?? 'var(--jtb-primary-hover)') . ";\n";
        $css .= "    --jtb-btn-hover-color: " . ($settings['button_hover_text'] ?? '#ffffff') . ";\n";

        // Form inputs
        $css .= "\n    /* ====== FORM INPUTS ====== */\n";
        $css .= "    --jtb-input-bg: " . ($settings['input_bg_color'] ?? 'var(--jtb-surface-color)') . ";\n";
        $css .= "    --jtb-input-color: " . ($settings['input_text_color'] ?? 'var(--jtb-text-color)') . ";\n";
        $css .= "    --jtb-input-border-color: " . ($settings['input_border_color'] ?? 'var(--jtb-border-color)') . ";\n";
        $css .= "    --jtb-input-border-width: " . ($settings['input_border_width'] ?? '1px') . ";\n";
        $css .= "    --jtb-input-border-radius: " . ($settings['input_border_radius'] ?? '8px') . ";\n";
        $css .= "    --jtb-input-padding-y: " . ($settings['input_padding_tb'] ?? '14px') . ";\n";
        $css .= "    --jtb-input-padding-x: " . ($settings['input_padding_lr'] ?? '16px') . ";\n";
        $css .= "    --jtb-input-font-size: " . ($settings['input_font_size'] ?? '15px') . ";\n";
        $css .= "    --jtb-input-focus-border-color: " . ($settings['input_focus_border_color'] ?? 'var(--jtb-primary-color)') . ";\n";
        $css .= "    --jtb-input-placeholder-color: " . ($settings['placeholder_color'] ?? 'var(--jtb-text-light-color)') . ";\n";

        // Header
        $css .= "\n    /* ====== HEADER ====== */\n";
        $css .= "    --jtb-header-bg: " . ($settings['header_bg_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-header-color: " . ($settings['header_text_color'] ?? 'var(--jtb-text-color)') . ";\n";
        $css .= "    --jtb-header-height: " . ($settings['header_height'] ?? '80px') . ";\n";
        $css .= "    --jtb-header-padding-x: " . ($settings['header_padding_lr'] ?? '24px') . ";\n";
        $css .= "    --jtb-logo-height: " . ($settings['logo_height'] ?? '50px') . ";\n";

        // Menu
        $css .= "\n    /* ====== MENU ====== */\n";
        $css .= "    --jtb-menu-font-size: " . ($settings['menu_font_size'] ?? '15px') . ";\n";
        $css .= "    --jtb-menu-font-weight: " . ($settings['menu_font_weight'] ?? '500') . ";\n";
        $css .= "    --jtb-menu-link-color: " . ($settings['menu_link_color'] ?? 'var(--jtb-text-color)') . ";\n";
        $css .= "    --jtb-menu-link-hover-color: " . ($settings['menu_link_hover_color'] ?? 'var(--jtb-primary-color)') . ";\n";
        $css .= "    --jtb-menu-link-active-color: " . ($settings['menu_link_active_color'] ?? 'var(--jtb-primary-color)') . ";\n";
        $css .= "    --jtb-menu-link-padding-y: " . ($settings['menu_link_padding_tb'] ?? '12px') . ";\n";
        $css .= "    --jtb-menu-link-padding-x: " . ($settings['menu_link_padding_lr'] ?? '16px') . ";\n";
        $css .= "    --jtb-dropdown-bg: " . ($settings['dropdown_bg_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-dropdown-color: " . ($settings['dropdown_text_color'] ?? 'var(--jtb-text-color)') . ";\n";
        $css .= "    --jtb-dropdown-hover-bg: " . ($settings['dropdown_hover_bg'] ?? 'var(--jtb-surface-color)') . ";\n";
        $css .= "    --jtb-dropdown-border-radius: " . ($settings['dropdown_border_radius'] ?? '12px') . ";\n";

        // Footer
        $css .= "\n    /* ====== FOOTER ====== */\n";
        $css .= "    --jtb-footer-bg: " . ($settings['footer_bg_color'] ?? '#1f2937') . ";\n";
        $css .= "    --jtb-footer-color: " . ($settings['footer_text_color'] ?? 'rgba(255,255,255,0.8)') . ";\n";
        $css .= "    --jtb-footer-heading-color: " . ($settings['footer_heading_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-footer-link-color: " . ($settings['footer_link_color'] ?? 'rgba(255,255,255,0.8)') . ";\n";
        $css .= "    --jtb-footer-link-hover-color: " . ($settings['footer_link_hover_color'] ?? '#ffffff') . ";\n";
        $css .= "    --jtb-footer-padding-top: " . ($settings['footer_padding_top'] ?? '60px') . ";\n";
        $css .= "    --jtb-footer-padding-bottom: " . ($settings['footer_padding_bottom'] ?? '40px') . ";\n";

        $css .= "}\n\n";

        return $css;
    }

    /**
     * Generate module-specific CSS variables
     */
    private function generateModuleVariables(): string
    {
        $css = "/* ====== MODULE-SPECIFIC VARIABLES ====== */\n";
        $css .= ":root {\n";

        // Gallery module
        $css .= "\n    /* Gallery */\n";
        $css .= "    --jtb-gallery-columns: 3;\n";
        $css .= "    --jtb-gallery-gap: 16px;\n";
        $css .= "    --jtb-gallery-item-radius: var(--jtb-border-radius);\n";
        $css .= "    --jtb-gallery-item-shadow: var(--jtb-shadow);\n";
        $css .= "    --jtb-gallery-item-hover-shadow: var(--jtb-shadow-lg);\n";
        $css .= "    --jtb-gallery-overlay-bg: rgba(0, 0, 0, 0.5);\n";
        $css .= "    --jtb-gallery-overlay-color: #ffffff;\n";
        $css .= "    --jtb-gallery-title-size: 16px;\n";
        $css .= "    --jtb-gallery-title-weight: 600;\n";
        $css .= "    --jtb-gallery-title-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-gallery-caption-size: 14px;\n";
        $css .= "    --jtb-gallery-caption-color: var(--jtb-text-light-color);\n";
        $css .= "    --jtb-gallery-meta-padding: 12px 4px;\n";
        $css .= "    --jtb-gallery-image-hover-scale: 1.05;\n";
        $css .= "    --jtb-gallery-transition: var(--jtb-transition-smooth);\n";

        // Blog module
        $css .= "\n    /* Blog */\n";
        $css .= "    --jtb-blog-columns: 3;\n";
        $css .= "    --jtb-blog-gap: 30px;\n";
        $css .= "    --jtb-blog-card-bg: var(--jtb-surface-color);\n";
        $css .= "    --jtb-blog-card-radius: var(--jtb-border-radius-lg);\n";
        $css .= "    --jtb-blog-card-shadow: var(--jtb-shadow);\n";
        $css .= "    --jtb-blog-card-hover-shadow: var(--jtb-shadow-xl);\n";
        $css .= "    --jtb-blog-card-hover-transform: translateY(-4px);\n";
        $css .= "    --jtb-blog-content-padding: 24px;\n";
        $css .= "    --jtb-blog-title-size: 20px;\n";
        $css .= "    --jtb-blog-title-weight: 600;\n";
        $css .= "    --jtb-blog-title-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-blog-title-hover-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-blog-meta-size: 13px;\n";
        $css .= "    --jtb-blog-meta-color: var(--jtb-text-light-color);\n";
        $css .= "    --jtb-blog-excerpt-size: 15px;\n";
        $css .= "    --jtb-blog-excerpt-color: var(--jtb-text-color);\n";
        $css .= "    --jtb-blog-image-ratio: 16/9;\n";
        $css .= "    --jtb-blog-image-hover-scale: 1.05;\n";

        // Blurb module
        $css .= "\n    /* Blurb */\n";
        $css .= "    --jtb-blurb-icon-size: 64px;\n";
        $css .= "    --jtb-blurb-icon-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-blurb-icon-margin: 0 0 24px;\n";
        $css .= "    --jtb-blurb-title-size: 22px;\n";
        $css .= "    --jtb-blurb-title-weight: 600;\n";
        $css .= "    --jtb-blurb-title-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-blurb-title-margin: 0 0 12px;\n";
        $css .= "    --jtb-blurb-content-size: 15px;\n";
        $css .= "    --jtb-blurb-content-color: var(--jtb-text-light-color);\n";
        $css .= "    --jtb-blurb-text-align: center;\n";

        // Testimonial module
        $css .= "\n    /* Testimonial */\n";
        $css .= "    --jtb-testimonial-bg: var(--jtb-surface-color);\n";
        $css .= "    --jtb-testimonial-padding: 40px;\n";
        $css .= "    --jtb-testimonial-radius: var(--jtb-border-radius-xl);\n";
        $css .= "    --jtb-testimonial-shadow: var(--jtb-shadow-md);\n";
        $css .= "    --jtb-testimonial-quote-size: 18px;\n";
        $css .= "    --jtb-testimonial-quote-color: var(--jtb-text-color);\n";
        $css .= "    --jtb-testimonial-author-size: 16px;\n";
        $css .= "    --jtb-testimonial-author-weight: 600;\n";
        $css .= "    --jtb-testimonial-author-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-testimonial-position-size: 14px;\n";
        $css .= "    --jtb-testimonial-position-color: var(--jtb-text-light-color);\n";
        $css .= "    --jtb-testimonial-avatar-size: 56px;\n";

        // Team member module
        $css .= "\n    /* Team Member */\n";
        $css .= "    --jtb-team-bg: var(--jtb-background-color);\n";
        $css .= "    --jtb-team-radius: var(--jtb-border-radius-xl);\n";
        $css .= "    --jtb-team-shadow: var(--jtb-shadow);\n";
        $css .= "    --jtb-team-hover-shadow: var(--jtb-shadow-xl);\n";
        $css .= "    --jtb-team-content-padding: 24px;\n";
        $css .= "    --jtb-team-name-size: 20px;\n";
        $css .= "    --jtb-team-name-weight: 600;\n";
        $css .= "    --jtb-team-name-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-team-position-size: 14px;\n";
        $css .= "    --jtb-team-position-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-team-bio-size: 14px;\n";
        $css .= "    --jtb-team-bio-color: var(--jtb-text-light-color);\n";

        // Pricing table module
        $css .= "\n    /* Pricing Table */\n";
        $css .= "    --jtb-pricing-bg: var(--jtb-background-color);\n";
        $css .= "    --jtb-pricing-radius: var(--jtb-border-radius-xl);\n";
        $css .= "    --jtb-pricing-shadow: var(--jtb-shadow-md);\n";
        $css .= "    --jtb-pricing-padding: 40px 32px;\n";
        $css .= "    --jtb-pricing-featured-bg: var(--jtb-primary-color);\n";
        $css .= "    --jtb-pricing-featured-scale: 1.05;\n";
        $css .= "    --jtb-pricing-title-size: 24px;\n";
        $css .= "    --jtb-pricing-title-weight: 600;\n";
        $css .= "    --jtb-pricing-amount-size: 56px;\n";
        $css .= "    --jtb-pricing-amount-weight: 700;\n";
        $css .= "    --jtb-pricing-feature-size: 15px;\n";

        // Accordion module
        $css .= "\n    /* Accordion */\n";
        $css .= "    --jtb-accordion-bg: var(--jtb-background-color);\n";
        $css .= "    --jtb-accordion-radius: var(--jtb-border-radius-lg);\n";
        $css .= "    --jtb-accordion-border-color: var(--jtb-border-color);\n";
        $css .= "    --jtb-accordion-gap: 12px;\n";
        $css .= "    --jtb-accordion-toggle-padding: 20px 24px;\n";
        $css .= "    --jtb-accordion-title-size: 16px;\n";
        $css .= "    --jtb-accordion-title-weight: 600;\n";
        $css .= "    --jtb-accordion-title-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-accordion-title-active-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-accordion-content-padding: 0 24px 24px;\n";
        $css .= "    --jtb-accordion-content-size: 15px;\n";

        // Tabs module
        $css .= "\n    /* Tabs */\n";
        $css .= "    --jtb-tabs-nav-bg: var(--jtb-surface-color);\n";
        $css .= "    --jtb-tabs-nav-padding: 4px;\n";
        $css .= "    --jtb-tabs-nav-radius: 10px;\n";
        $css .= "    --jtb-tabs-btn-padding: 12px 24px;\n";
        $css .= "    --jtb-tabs-btn-radius: 8px;\n";
        $css .= "    --jtb-tabs-btn-size: 14px;\n";
        $css .= "    --jtb-tabs-btn-weight: 500;\n";
        $css .= "    --jtb-tabs-btn-color: var(--jtb-text-color);\n";
        $css .= "    --jtb-tabs-btn-active-bg: #ffffff;\n";
        $css .= "    --jtb-tabs-btn-active-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-tabs-content-padding: 24px 0;\n";

        // Counter modules
        $css .= "\n    /* Counters */\n";
        $css .= "    --jtb-counter-number-size: 56px;\n";
        $css .= "    --jtb-counter-number-weight: 700;\n";
        $css .= "    --jtb-counter-number-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-counter-title-size: 18px;\n";
        $css .= "    --jtb-counter-title-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-counter-bar-height: 24px;\n";
        $css .= "    --jtb-counter-bar-bg: var(--jtb-surface-color);\n";
        $css .= "    --jtb-counter-bar-fill: var(--jtb-primary-color);\n";
        $css .= "    --jtb-counter-bar-radius: 12px;\n";

        // CTA module
        $css .= "\n    /* CTA */\n";
        $css .= "    --jtb-cta-bg: var(--jtb-primary-color);\n";
        $css .= "    --jtb-cta-padding: 60px 40px;\n";
        $css .= "    --jtb-cta-radius: var(--jtb-border-radius-xl);\n";
        $css .= "    --jtb-cta-title-size: 36px;\n";
        $css .= "    --jtb-cta-title-weight: 700;\n";
        $css .= "    --jtb-cta-title-color: #ffffff;\n";
        $css .= "    --jtb-cta-description-size: 18px;\n";
        $css .= "    --jtb-cta-description-color: rgba(255, 255, 255, 0.9);\n";

        // Slider module
        $css .= "\n    /* Slider */\n";
        $css .= "    --jtb-slider-height: 500px;\n";
        $css .= "    --jtb-slider-arrow-size: 48px;\n";
        $css .= "    --jtb-slider-arrow-bg: rgba(255, 255, 255, 0.9);\n";
        $css .= "    --jtb-slider-arrow-color: var(--jtb-heading-color);\n";
        $css .= "    --jtb-slider-dot-size: 10px;\n";
        $css .= "    --jtb-slider-dot-color: rgba(255, 255, 255, 0.5);\n";
        $css .= "    --jtb-slider-dot-active-color: #ffffff;\n";
        $css .= "    --jtb-slider-title-size: 48px;\n";
        $css .= "    --jtb-slider-title-color: #ffffff;\n";
        $css .= "    --jtb-slider-description-size: 18px;\n";
        $css .= "    --jtb-slider-description-color: rgba(255, 255, 255, 0.9);\n";

        // Social icons
        $css .= "\n    /* Social Icons */\n";
        $css .= "    --jtb-social-icon-size: 20px;\n";
        $css .= "    --jtb-social-icon-color: var(--jtb-text-light-color);\n";
        $css .= "    --jtb-social-icon-hover-color: var(--jtb-primary-color);\n";
        $css .= "    --jtb-social-icon-padding: 12px;\n";
        $css .= "    --jtb-social-icon-radius: 50%;\n";
        $css .= "    --jtb-social-icon-gap: 8px;\n";

        $css .= "}\n\n";

        return $css;
    }

    /**
     * Generate responsive variable overrides
     */
    private function generateResponsiveVariables(): string
    {
        $settings = $this->themeSettings ? $this->themeSettings->getSettings() : [];

        $tabletBreakpoint = $settings['tablet_breakpoint'] ?? '980px';
        $phoneBreakpoint = $settings['phone_breakpoint'] ?? '767px';

        $css = "/* ====== RESPONSIVE OVERRIDES ====== */\n";

        // Tablet
        $css .= "@media (max-width: {$tabletBreakpoint}) {\n";
        $css .= "    :root {\n";
        $css .= "        /* Typography */\n";
        $css .= "        --jtb-h1-size: " . ($settings['h1_size_tablet'] ?? '40px') . ";\n";
        $css .= "        --jtb-h2-size: " . ($settings['h2_size_tablet'] ?? '32px') . ";\n";
        $css .= "        --jtb-h3-size: " . ($settings['h3_size_tablet'] ?? '24px') . ";\n";
        $css .= "        --jtb-body-size: " . ($settings['body_size_tablet'] ?? '15px') . ";\n";
        $css .= "\n        /* Layout */\n";
        $css .= "        --jtb-section-padding-top: " . ($settings['section_padding_tablet'] ?? '48px') . ";\n";
        $css .= "        --jtb-section-padding-bottom: " . ($settings['section_padding_tablet'] ?? '48px') . ";\n";
        $css .= "        --jtb-gutter-width: 24px;\n";
        $css .= "\n        /* Gallery */\n";
        $css .= "        --jtb-gallery-columns: 2;\n";
        $css .= "        --jtb-gallery-gap: 12px;\n";
        $css .= "\n        /* Blog */\n";
        $css .= "        --jtb-blog-columns: 2;\n";
        $css .= "        --jtb-blog-gap: 24px;\n";
        $css .= "        --jtb-blog-title-size: 18px;\n";
        $css .= "\n        /* Blurb */\n";
        $css .= "        --jtb-blurb-icon-size: 56px;\n";
        $css .= "        --jtb-blurb-title-size: 20px;\n";
        $css .= "\n        /* CTA */\n";
        $css .= "        --jtb-cta-padding: 48px 32px;\n";
        $css .= "        --jtb-cta-title-size: 30px;\n";
        $css .= "\n        /* Slider */\n";
        $css .= "        --jtb-slider-height: 400px;\n";
        $css .= "        --jtb-slider-title-size: 36px;\n";
        $css .= "\n        /* Counter */\n";
        $css .= "        --jtb-counter-number-size: 44px;\n";
        $css .= "    }\n";
        $css .= "}\n\n";

        // Phone
        $css .= "@media (max-width: {$phoneBreakpoint}) {\n";
        $css .= "    :root {\n";
        $css .= "        /* Typography */\n";
        $css .= "        --jtb-h1-size: " . ($settings['h1_size_phone'] ?? '32px') . ";\n";
        $css .= "        --jtb-h2-size: " . ($settings['h2_size_phone'] ?? '28px') . ";\n";
        $css .= "        --jtb-h3-size: " . ($settings['h3_size_phone'] ?? '22px') . ";\n";
        $css .= "        --jtb-body-size: " . ($settings['body_size_phone'] ?? '15px') . ";\n";
        $css .= "\n        /* Layout */\n";
        $css .= "        --jtb-section-padding-top: " . ($settings['section_padding_phone'] ?? '40px') . ";\n";
        $css .= "        --jtb-section-padding-bottom: " . ($settings['section_padding_phone'] ?? '40px') . ";\n";
        $css .= "        --jtb-gutter-width: 16px;\n";
        $css .= "        --jtb-row-gap: 20px;\n";
        $css .= "        --jtb-column-gap: 20px;\n";
        $css .= "\n        /* Gallery */\n";
        $css .= "        --jtb-gallery-columns: 1;\n";
        $css .= "        --jtb-gallery-gap: 8px;\n";
        $css .= "\n        /* Blog */\n";
        $css .= "        --jtb-blog-columns: 1;\n";
        $css .= "        --jtb-blog-gap: 20px;\n";
        $css .= "        --jtb-blog-title-size: 18px;\n";
        $css .= "        --jtb-blog-content-padding: 20px;\n";
        $css .= "\n        /* Blurb */\n";
        $css .= "        --jtb-blurb-icon-size: 48px;\n";
        $css .= "        --jtb-blurb-title-size: 18px;\n";
        $css .= "\n        /* Testimonial */\n";
        $css .= "        --jtb-testimonial-padding: 24px;\n";
        $css .= "        --jtb-testimonial-quote-size: 16px;\n";
        $css .= "\n        /* Team */\n";
        $css .= "        --jtb-team-content-padding: 20px;\n";
        $css .= "        --jtb-team-name-size: 18px;\n";
        $css .= "\n        /* Pricing */\n";
        $css .= "        --jtb-pricing-padding: 32px 24px;\n";
        $css .= "        --jtb-pricing-amount-size: 44px;\n";
        $css .= "\n        /* CTA */\n";
        $css .= "        --jtb-cta-padding: 40px 24px;\n";
        $css .= "        --jtb-cta-title-size: 26px;\n";
        $css .= "        --jtb-cta-description-size: 16px;\n";
        $css .= "\n        /* Slider */\n";
        $css .= "        --jtb-slider-height: 300px;\n";
        $css .= "        --jtb-slider-title-size: 28px;\n";
        $css .= "        --jtb-slider-description-size: 16px;\n";
        $css .= "        --jtb-slider-arrow-size: 40px;\n";
        $css .= "\n        /* Counter */\n";
        $css .= "        --jtb-counter-number-size: 36px;\n";
        $css .= "        --jtb-counter-title-size: 16px;\n";
        $css .= "\n        /* Accordion */\n";
        $css .= "        --jtb-accordion-toggle-padding: 16px 20px;\n";
        $css .= "        --jtb-accordion-content-padding: 0 20px 20px;\n";
        $css .= "\n        /* Tabs */\n";
        $css .= "        --jtb-tabs-btn-padding: 10px 16px;\n";
        $css .= "        --jtb-tabs-btn-size: 13px;\n";
        $css .= "    }\n";
        $css .= "}\n\n";

        return $css;
    }

    /**
     * Generate dark mode variables (optional)
     */
    private function generateDarkModeVariables(): string
    {
        $css = "/* ====== DARK MODE (Optional) ====== */\n";
        $css .= "@media (prefers-color-scheme: dark) {\n";
        $css .= "    :root.jtb-auto-dark,\n";
        $css .= "    :root[data-theme=\"dark\"] {\n";
        $css .= "        --jtb-text-color: #e5e7eb;\n";
        $css .= "        --jtb-text-light-color: #9ca3af;\n";
        $css .= "        --jtb-heading-color: #f9fafb;\n";
        $css .= "        --jtb-background-color: #111827;\n";
        $css .= "        --jtb-surface-color: #1f2937;\n";
        $css .= "        --jtb-border-color: #374151;\n";
        $css .= "        --jtb-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);\n";
        $css .= "        --jtb-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.4);\n";
        $css .= "        --jtb-shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.5);\n";
        $css .= "        --jtb-input-bg: #1f2937;\n";
        $css .= "        --jtb-header-bg: #1f2937;\n";
        $css .= "        --jtb-dropdown-bg: #1f2937;\n";
        $css .= "        --jtb-blog-card-bg: #1f2937;\n";
        $css .= "        --jtb-testimonial-bg: #1f2937;\n";
        $css .= "        --jtb-team-bg: #1f2937;\n";
        $css .= "        --jtb-pricing-bg: #1f2937;\n";
        $css .= "        --jtb-accordion-bg: #1f2937;\n";
        $css .= "        --jtb-tabs-nav-bg: #374151;\n";
        $css .= "        --jtb-tabs-btn-active-bg: #1f2937;\n";
        $css .= "    }\n";
        $css .= "}\n\n";

        // Manual dark mode toggle
        $css .= ":root[data-theme=\"dark\"] {\n";
        $css .= "    --jtb-text-color: #e5e7eb;\n";
        $css .= "    --jtb-text-light-color: #9ca3af;\n";
        $css .= "    --jtb-heading-color: #f9fafb;\n";
        $css .= "    --jtb-background-color: #111827;\n";
        $css .= "    --jtb-surface-color: #1f2937;\n";
        $css .= "    --jtb-border-color: #374151;\n";
        $css .= "}\n\n";

        return $css;
    }

    /**
     * Darken a hex color
     */
    private function darkenColor(string $hex, int $percent): string
    {
        return $this->adjustBrightness($hex, -$percent);
    }

    /**
     * Lighten a hex color
     */
    private function lightenColor(string $hex, int $percent): string
    {
        return $this->adjustBrightness($hex, $percent);
    }

    /**
     * Adjust color brightness
     */
    private function adjustBrightness(string $hex, int $percent): string
    {
        // Remove # if present
        $hex = ltrim($hex, '#');

        // Handle short hex
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // If not valid hex, return as-is
        if (strlen($hex) !== 6) {
            return "#{$hex}";
        }

        // Convert to RGB
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Adjust brightness
        $r = max(0, min(255, $r + ($r * $percent / 100)));
        $g = max(0, min(255, $g + ($g * $percent / 100)));
        $b = max(0, min(255, $b + ($b * $percent / 100)));

        // Convert back to hex
        return sprintf('#%02x%02x%02x', (int)$r, (int)$g, (int)$b);
    }

    /**
     * Clear cached CSS
     */
    public function clearCache(): void
    {
        $this->cachedCss = null;
    }
}
