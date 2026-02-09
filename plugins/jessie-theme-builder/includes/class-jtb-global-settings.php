<?php
/**
 * JTB Global Settings
 *
 * Centralna klasa definiująca DOMYŚLNE wartości stylów dla KAŻDEGO elementu KAŻDEGO modułu.
 * Inspirowana architekturą Divi ET_Global_Settings.
 *
 * Hierarchia wartości:
 * 1. Wartość ustawiona przez użytkownika w module (najwyższy priorytet)
 * 2. Wartość z Theme Settings (jeśli użytkownik zmienił globalnie)
 * 3. Wartość domyślna z tej klasy (fallback)
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Global_Settings
{
    /**
     * Singleton instance
     */
    private static ?JTB_Global_Settings $instance = null;

    /**
     * Cached settings from database (Theme Settings)
     */
    private array $themeSettings = [];

    /**
     * Whether theme settings have been loaded
     */
    private bool $settingsLoaded = false;

    /**
     * Default values for all modules and their elements
     * Format: 'module_element_property' => 'value'
     */
    private static array $defaults = [
        // =============================================
        // GLOBAL DEFAULTS (apply to all modules)
        // =============================================

        // Colors (reference CSS variables from theme)
        'global_primary_color' => 'var(--jtb-primary-color, #6366f1)',
        'global_secondary_color' => 'var(--jtb-secondary-color, #8b5cf6)',
        'global_accent_color' => 'var(--jtb-accent-color, #06b6d4)',
        'global_text_color' => 'var(--jtb-text-color, #1f2937)',
        'global_text_light_color' => 'var(--jtb-text-light-color, #6b7280)',
        'global_heading_color' => 'var(--jtb-heading-color, #111827)',
        'global_link_color' => 'var(--jtb-link-color, #6366f1)',
        'global_link_hover_color' => 'var(--jtb-link-hover-color, #4f46e5)',
        'global_background_color' => 'var(--jtb-background-color, #ffffff)',
        'global_surface_color' => 'var(--jtb-surface-color, #f8fafc)',
        'global_border_color' => 'var(--jtb-border-color, #e5e7eb)',

        // Typography
        'global_body_font' => 'var(--jtb-body-font, system-ui, -apple-system, sans-serif)',
        'global_body_size' => 'var(--jtb-body-size, 16px)',
        'global_body_weight' => 'var(--jtb-body-weight, 400)',
        'global_body_line_height' => 'var(--jtb-body-line-height, 1.6)',
        'global_heading_font' => 'var(--jtb-heading-font, system-ui, -apple-system, sans-serif)',
        'global_heading_weight' => 'var(--jtb-heading-weight, 700)',
        'global_heading_line_height' => 'var(--jtb-heading-line-height, 1.3)',

        // Spacing
        'global_module_margin_bottom' => '30px',
        'global_section_padding_tb' => '60px',
        'global_row_gap' => '30px',
        'global_column_gap' => '30px',

        // Borders & Shadows
        'global_border_radius' => '8px',
        'global_border_radius_small' => '4px',
        'global_border_radius_large' => '12px',
        'global_border_radius_full' => '9999px',
        'global_box_shadow_light' => '0 1px 3px rgba(0,0,0,0.08)',
        'global_box_shadow_medium' => '0 4px 12px rgba(0,0,0,0.1)',
        'global_box_shadow_heavy' => '0 10px 40px rgba(0,0,0,0.15)',

        // Transitions
        'global_transition_fast' => '150ms ease',
        'global_transition_normal' => '300ms ease',
        'global_transition_slow' => '500ms ease',

        // =============================================
        // SECTION MODULE
        // =============================================
        'section_background_color' => 'transparent',
        'section_padding_top' => '60px',
        'section_padding_bottom' => '60px',
        'section_padding_left' => '0',
        'section_padding_right' => '0',
        'section_inner_max_width' => '1200px',

        // =============================================
        // ROW MODULE
        // =============================================
        'row_max_width' => '1200px',
        'row_padding_top' => '0',
        'row_padding_bottom' => '0',
        'row_column_gap' => '30px',
        'row_row_gap' => '30px',

        // =============================================
        // COLUMN MODULE
        // =============================================
        'column_padding' => '0',
        'column_background_color' => 'transparent',

        // =============================================
        // TEXT MODULE
        // =============================================
        'text_font_family' => 'inherit',
        'text_font_size' => '16px',
        'text_font_weight' => '400',
        'text_line_height' => '1.7',
        'text_letter_spacing' => '0',
        'text_color' => 'var(--jtb-text-color, #1f2937)',
        'text_link_color' => 'var(--jtb-link-color, #6366f1)',
        'text_link_hover_color' => 'var(--jtb-link-hover-color, #4f46e5)',

        // =============================================
        // HEADING MODULE
        // =============================================
        'heading_font_family' => 'var(--jtb-heading-font, inherit)',
        'heading_font_weight' => '700',
        'heading_line_height' => '1.3',
        'heading_letter_spacing' => '-0.02em',
        'heading_color' => 'var(--jtb-heading-color, #111827)',
        'heading_margin_bottom' => '20px',
        // H1
        'heading_h1_font_size' => '48px',
        'heading_h1_font_size_tablet' => '40px',
        'heading_h1_font_size_phone' => '32px',
        // H2
        'heading_h2_font_size' => '36px',
        'heading_h2_font_size_tablet' => '32px',
        'heading_h2_font_size_phone' => '28px',
        // H3
        'heading_h3_font_size' => '28px',
        'heading_h3_font_size_tablet' => '24px',
        'heading_h3_font_size_phone' => '22px',
        // H4
        'heading_h4_font_size' => '24px',
        'heading_h4_font_size_tablet' => '22px',
        'heading_h4_font_size_phone' => '20px',
        // H5
        'heading_h5_font_size' => '20px',
        'heading_h5_font_size_tablet' => '18px',
        'heading_h5_font_size_phone' => '18px',
        // H6
        'heading_h6_font_size' => '18px',
        'heading_h6_font_size_tablet' => '16px',
        'heading_h6_font_size_phone' => '16px',

        // =============================================
        // IMAGE MODULE
        // =============================================
        'image_border_radius' => '8px',
        'image_box_shadow' => 'none',
        'image_hover_scale' => '1',
        'image_hover_opacity' => '1',
        'image_caption_font_size' => '14px',
        'image_caption_color' => 'var(--jtb-text-light-color, #6b7280)',
        'image_caption_margin_top' => '12px',

        // =============================================
        // BUTTON MODULE
        // =============================================
        'button_font_family' => 'inherit',
        'button_font_size' => '16px',
        'button_font_weight' => '600',
        'button_letter_spacing' => '0',
        'button_text_transform' => 'none',
        'button_padding_tb' => '14px',
        'button_padding_lr' => '28px',
        'button_border_radius' => '8px',
        'button_border_width' => '0',
        'button_border_style' => 'solid',
        // Primary button
        'button_primary_bg_color' => 'var(--jtb-primary-color, #6366f1)',
        'button_primary_text_color' => '#ffffff',
        'button_primary_border_color' => 'transparent',
        'button_primary_hover_bg_color' => 'var(--jtb-primary-hover, #4f46e5)',
        'button_primary_hover_text_color' => '#ffffff',
        'button_primary_hover_border_color' => 'transparent',
        // Secondary button
        'button_secondary_bg_color' => 'var(--jtb-secondary-color, #8b5cf6)',
        'button_secondary_text_color' => '#ffffff',
        'button_secondary_border_color' => 'transparent',
        'button_secondary_hover_bg_color' => '#7c3aed',
        'button_secondary_hover_text_color' => '#ffffff',
        // Outline button
        'button_outline_bg_color' => 'transparent',
        'button_outline_text_color' => 'var(--jtb-primary-color, #6366f1)',
        'button_outline_border_color' => 'var(--jtb-primary-color, #6366f1)',
        'button_outline_border_width' => '2px',
        'button_outline_hover_bg_color' => 'var(--jtb-primary-color, #6366f1)',
        'button_outline_hover_text_color' => '#ffffff',
        // Ghost button
        'button_ghost_bg_color' => 'transparent',
        'button_ghost_text_color' => 'var(--jtb-primary-color, #6366f1)',
        'button_ghost_hover_bg_color' => 'rgba(99, 102, 241, 0.1)',
        'button_ghost_hover_text_color' => 'var(--jtb-primary-color, #6366f1)',
        // Button icon
        'button_icon_size' => '18px',
        'button_icon_spacing' => '8px',
        // Button transition
        'button_transition' => '300ms ease',
        'button_box_shadow' => 'none',
        'button_hover_box_shadow' => '0 4px 12px rgba(99, 102, 241, 0.3)',

        // =============================================
        // BLURB MODULE
        // =============================================
        // Icon
        'blurb_icon_size' => '64px',
        'blurb_icon_color' => 'var(--jtb-primary-color, #6366f1)',
        'blurb_icon_background_color' => 'transparent',
        'blurb_icon_background_size' => '96px',
        'blurb_icon_border_radius' => '50%',
        'blurb_icon_margin_bottom' => '24px',
        // Image
        'blurb_image_width' => '100%',
        'blurb_image_max_width' => '80px',
        'blurb_image_border_radius' => '8px',
        'blurb_image_margin_bottom' => '24px',
        // Title
        'blurb_title_font_family' => 'var(--jtb-heading-font, inherit)',
        'blurb_title_font_size' => '22px',
        'blurb_title_font_size_tablet' => '20px',
        'blurb_title_font_size_phone' => '18px',
        'blurb_title_font_weight' => '600',
        'blurb_title_line_height' => '1.4',
        'blurb_title_color' => 'var(--jtb-heading-color, #111827)',
        'blurb_title_margin_bottom' => '12px',
        // Content
        'blurb_content_font_size' => '15px',
        'blurb_content_line_height' => '1.7',
        'blurb_content_color' => 'var(--jtb-text-light-color, #6b7280)',
        // Layout
        'blurb_text_align' => 'center',
        'blurb_content_max_width' => '100%',

        // =============================================
        // GALLERY MODULE
        // =============================================
        // Container
        'gallery_layout' => 'grid',
        'gallery_columns' => '3',
        'gallery_columns_tablet' => '2',
        'gallery_columns_phone' => '1',
        'gallery_gutter' => '16px',
        'gallery_gutter_tablet' => '12px',
        'gallery_gutter_phone' => '8px',
        // Item
        'gallery_item_border_radius' => '8px',
        'gallery_item_box_shadow' => '0 1px 3px rgba(0,0,0,0.08)',
        'gallery_item_hover_box_shadow' => '0 8px 25px rgba(0,0,0,0.12)',
        'gallery_item_background_color' => 'var(--jtb-surface-color, #f8fafc)',
        // Image
        'gallery_image_border_radius' => '8px',
        'gallery_image_aspect_ratio' => 'auto',
        'gallery_image_object_fit' => 'cover',
        'gallery_image_hover_scale' => '1.05',
        'gallery_image_transition' => '400ms cubic-bezier(0.4, 0, 0.2, 1)',
        // Overlay
        'gallery_overlay_background' => 'rgba(0, 0, 0, 0.5)',
        'gallery_overlay_icon_color' => '#ffffff',
        'gallery_overlay_icon_size' => '24px',
        'gallery_overlay_icon_bg_size' => '48px',
        'gallery_overlay_icon_bg_color' => 'rgba(255, 255, 255, 0.15)',
        'gallery_overlay_transition' => '300ms ease',
        // Title
        'gallery_title_font_family' => 'var(--jtb-heading-font, inherit)',
        'gallery_title_font_size' => '16px',
        'gallery_title_font_weight' => '600',
        'gallery_title_line_height' => '1.4',
        'gallery_title_color' => 'var(--jtb-heading-color, #111827)',
        'gallery_title_margin_bottom' => '4px',
        // Caption
        'gallery_caption_font_size' => '14px',
        'gallery_caption_line_height' => '1.5',
        'gallery_caption_color' => 'var(--jtb-text-light-color, #6b7280)',
        // Meta container
        'gallery_meta_padding' => '12px 4px',
        'gallery_meta_background' => 'transparent',
        // Caption overlay
        'gallery_caption_overlay_background' => 'linear-gradient(transparent, rgba(0, 0, 0, 0.8))',
        'gallery_caption_overlay_padding' => '16px',
        'gallery_caption_overlay_text_color' => '#ffffff',

        // =============================================
        // BLOG MODULE
        // =============================================
        // Layout
        'blog_layout' => 'grid',
        'blog_columns' => '3',
        'blog_columns_tablet' => '2',
        'blog_columns_phone' => '1',
        'blog_gap' => '30px',
        'blog_gap_tablet' => '24px',
        'blog_gap_phone' => '20px',
        // Card
        'blog_card_background' => 'var(--jtb-surface-color, #ffffff)',
        'blog_card_border_radius' => '12px',
        'blog_card_box_shadow' => '0 1px 3px rgba(0,0,0,0.08)',
        'blog_card_hover_box_shadow' => '0 10px 40px rgba(0,0,0,0.12)',
        'blog_card_hover_transform' => 'translateY(-4px)',
        'blog_card_padding' => '0',
        'blog_card_transition' => '300ms ease',
        // Featured image
        'blog_image_aspect_ratio' => '16/9',
        'blog_image_border_radius' => '12px 12px 0 0',
        'blog_image_hover_scale' => '1.05',
        // Content area
        'blog_content_padding' => '24px',
        // Title
        'blog_title_font_family' => 'var(--jtb-heading-font, inherit)',
        'blog_title_font_size' => '20px',
        'blog_title_font_size_tablet' => '18px',
        'blog_title_font_size_phone' => '18px',
        'blog_title_font_weight' => '600',
        'blog_title_line_height' => '1.4',
        'blog_title_color' => 'var(--jtb-heading-color, #111827)',
        'blog_title_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'blog_title_margin_bottom' => '12px',
        // Meta
        'blog_meta_font_size' => '13px',
        'blog_meta_color' => 'var(--jtb-text-light-color, #6b7280)',
        'blog_meta_margin_bottom' => '12px',
        'blog_meta_separator' => ' · ',
        // Excerpt
        'blog_excerpt_font_size' => '15px',
        'blog_excerpt_line_height' => '1.7',
        'blog_excerpt_color' => 'var(--jtb-text-color, #4b5563)',
        'blog_excerpt_margin_bottom' => '16px',
        // Read more
        'blog_read_more_font_size' => '14px',
        'blog_read_more_font_weight' => '600',
        'blog_read_more_color' => 'var(--jtb-primary-color, #6366f1)',
        'blog_read_more_hover_color' => 'var(--jtb-primary-hover, #4f46e5)',
        // Pagination
        'blog_pagination_font_size' => '14px',
        'blog_pagination_color' => 'var(--jtb-text-color, #4b5563)',
        'blog_pagination_active_color' => 'var(--jtb-primary-color, #6366f1)',
        'blog_pagination_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'blog_pagination_gap' => '8px',
        'blog_pagination_item_size' => '40px',
        'blog_pagination_border_radius' => '8px',
        // Category badges
        'blog_category_font_size' => '11px',
        'blog_category_font_weight' => '600',
        'blog_category_text_transform' => 'uppercase',
        'blog_category_letter_spacing' => '0.05em',
        'blog_category_padding' => '4px 10px',
        'blog_category_border_radius' => '4px',
        'blog_category_background' => 'var(--jtb-primary-color, #6366f1)',
        'blog_category_color' => '#ffffff',

        // =============================================
        // CTA (CALL TO ACTION) MODULE
        // =============================================
        'cta_background_color' => 'var(--jtb-primary-color, #6366f1)',
        'cta_padding' => '60px 40px',
        'cta_padding_tablet' => '48px 32px',
        'cta_padding_phone' => '40px 24px',
        'cta_border_radius' => '16px',
        'cta_text_align' => 'center',
        // Title
        'cta_title_font_size' => '36px',
        'cta_title_font_size_tablet' => '30px',
        'cta_title_font_size_phone' => '26px',
        'cta_title_font_weight' => '700',
        'cta_title_color' => '#ffffff',
        'cta_title_margin_bottom' => '16px',
        // Description
        'cta_description_font_size' => '18px',
        'cta_description_line_height' => '1.7',
        'cta_description_color' => 'rgba(255, 255, 255, 0.9)',
        'cta_description_margin_bottom' => '32px',
        // Button
        'cta_button_bg_color' => '#ffffff',
        'cta_button_text_color' => 'var(--jtb-primary-color, #6366f1)',
        'cta_button_hover_bg_color' => 'rgba(255, 255, 255, 0.9)',

        // =============================================
        // DIVIDER MODULE
        // =============================================
        'divider_style' => 'solid',
        'divider_color' => 'var(--jtb-border-color, #e5e7eb)',
        'divider_weight' => '1px',
        'divider_width' => '100%',
        'divider_max_width' => '100%',
        'divider_alignment' => 'center',
        'divider_margin_top' => '20px',
        'divider_margin_bottom' => '20px',

        // =============================================
        // ICON MODULE
        // =============================================
        'icon_size' => '48px',
        'icon_color' => 'var(--jtb-primary-color, #6366f1)',
        'icon_hover_color' => 'var(--jtb-primary-hover, #4f46e5)',
        'icon_background_color' => 'transparent',
        'icon_background_size' => '80px',
        'icon_background_border_radius' => '50%',
        'icon_border_width' => '0',
        'icon_border_color' => 'var(--jtb-primary-color, #6366f1)',
        'icon_alignment' => 'center',

        // =============================================
        // TESTIMONIAL MODULE
        // =============================================
        'testimonial_background' => 'var(--jtb-surface-color, #f8fafc)',
        'testimonial_padding' => '40px',
        'testimonial_border_radius' => '16px',
        'testimonial_box_shadow' => '0 4px 12px rgba(0,0,0,0.08)',
        // Quote
        'testimonial_quote_font_size' => '18px',
        'testimonial_quote_font_style' => 'normal',
        'testimonial_quote_line_height' => '1.8',
        'testimonial_quote_color' => 'var(--jtb-text-color, #374151)',
        'testimonial_quote_margin_bottom' => '24px',
        // Quote icon
        'testimonial_quote_icon_size' => '48px',
        'testimonial_quote_icon_color' => 'var(--jtb-primary-color, #6366f1)',
        'testimonial_quote_icon_opacity' => '0.15',
        // Author
        'testimonial_author_font_size' => '16px',
        'testimonial_author_font_weight' => '600',
        'testimonial_author_color' => 'var(--jtb-heading-color, #111827)',
        // Position/Company
        'testimonial_position_font_size' => '14px',
        'testimonial_position_color' => 'var(--jtb-text-light-color, #6b7280)',
        // Avatar
        'testimonial_avatar_size' => '56px',
        'testimonial_avatar_border_radius' => '50%',
        'testimonial_avatar_margin_right' => '16px',
        // Rating
        'testimonial_rating_size' => '18px',
        'testimonial_rating_color' => '#fbbf24',
        'testimonial_rating_empty_color' => '#d1d5db',

        // =============================================
        // TEAM MEMBER MODULE
        // =============================================
        'team_background' => 'var(--jtb-surface-color, #ffffff)',
        'team_padding' => '0',
        'team_border_radius' => '16px',
        'team_box_shadow' => '0 1px 3px rgba(0,0,0,0.08)',
        'team_hover_box_shadow' => '0 10px 40px rgba(0,0,0,0.12)',
        'team_text_align' => 'center',
        // Image
        'team_image_aspect_ratio' => '1/1',
        'team_image_border_radius' => '16px 16px 0 0',
        // Content
        'team_content_padding' => '24px',
        // Name
        'team_name_font_size' => '20px',
        'team_name_font_weight' => '600',
        'team_name_color' => 'var(--jtb-heading-color, #111827)',
        'team_name_margin_bottom' => '4px',
        // Position
        'team_position_font_size' => '14px',
        'team_position_color' => 'var(--jtb-primary-color, #6366f1)',
        'team_position_margin_bottom' => '12px',
        // Bio
        'team_bio_font_size' => '14px',
        'team_bio_line_height' => '1.6',
        'team_bio_color' => 'var(--jtb-text-light-color, #6b7280)',
        // Social icons
        'team_social_icon_size' => '18px',
        'team_social_icon_color' => 'var(--jtb-text-light-color, #6b7280)',
        'team_social_icon_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'team_social_icon_gap' => '12px',
        'team_social_margin_top' => '16px',

        // =============================================
        // PRICING TABLE MODULE
        // =============================================
        'pricing_background' => 'var(--jtb-surface-color, #ffffff)',
        'pricing_border_radius' => '16px',
        'pricing_box_shadow' => '0 4px 12px rgba(0,0,0,0.08)',
        'pricing_padding' => '40px 32px',
        'pricing_text_align' => 'center',
        // Featured
        'pricing_featured_background' => 'var(--jtb-primary-color, #6366f1)',
        'pricing_featured_scale' => '1.05',
        'pricing_featured_box_shadow' => '0 20px 60px rgba(99, 102, 241, 0.3)',
        // Title
        'pricing_title_font_size' => '24px',
        'pricing_title_font_weight' => '600',
        'pricing_title_color' => 'var(--jtb-heading-color, #111827)',
        'pricing_title_margin_bottom' => '8px',
        // Subtitle
        'pricing_subtitle_font_size' => '14px',
        'pricing_subtitle_color' => 'var(--jtb-text-light-color, #6b7280)',
        'pricing_subtitle_margin_bottom' => '24px',
        // Price
        'pricing_currency_font_size' => '24px',
        'pricing_amount_font_size' => '56px',
        'pricing_amount_font_weight' => '700',
        'pricing_amount_color' => 'var(--jtb-heading-color, #111827)',
        'pricing_period_font_size' => '16px',
        'pricing_period_color' => 'var(--jtb-text-light-color, #6b7280)',
        'pricing_price_margin_bottom' => '32px',
        // Features
        'pricing_feature_font_size' => '15px',
        'pricing_feature_color' => 'var(--jtb-text-color, #4b5563)',
        'pricing_feature_line_height' => '2.2',
        'pricing_feature_icon_color' => 'var(--jtb-primary-color, #6366f1)',
        'pricing_features_margin_bottom' => '32px',

        // =============================================
        // ACCORDION MODULE
        // =============================================
        'accordion_background' => 'var(--jtb-surface-color, #ffffff)',
        'accordion_border_radius' => '12px',
        'accordion_border_color' => 'var(--jtb-border-color, #e5e7eb)',
        'accordion_border_width' => '1px',
        'accordion_gap' => '12px',
        // Toggle header
        'accordion_toggle_padding' => '20px 24px',
        'accordion_toggle_background' => 'transparent',
        'accordion_toggle_hover_background' => 'var(--jtb-surface-color, #f8fafc)',
        'accordion_toggle_active_background' => 'var(--jtb-surface-color, #f8fafc)',
        // Title
        'accordion_title_font_size' => '16px',
        'accordion_title_font_weight' => '600',
        'accordion_title_color' => 'var(--jtb-heading-color, #111827)',
        'accordion_title_active_color' => 'var(--jtb-primary-color, #6366f1)',
        // Icon
        'accordion_icon_size' => '20px',
        'accordion_icon_color' => 'var(--jtb-text-light-color, #6b7280)',
        'accordion_icon_active_color' => 'var(--jtb-primary-color, #6366f1)',
        // Content
        'accordion_content_padding' => '0 24px 24px',
        'accordion_content_font_size' => '15px',
        'accordion_content_line_height' => '1.7',
        'accordion_content_color' => 'var(--jtb-text-color, #4b5563)',

        // =============================================
        // TABS MODULE
        // =============================================
        'tabs_background' => 'transparent',
        'tabs_border_radius' => '12px',
        // Tab navigation
        'tabs_nav_background' => 'var(--jtb-surface-color, #f1f5f9)',
        'tabs_nav_padding' => '4px',
        'tabs_nav_border_radius' => '10px',
        'tabs_nav_gap' => '4px',
        // Tab button
        'tabs_button_padding' => '12px 24px',
        'tabs_button_border_radius' => '8px',
        'tabs_button_font_size' => '14px',
        'tabs_button_font_weight' => '500',
        'tabs_button_color' => 'var(--jtb-text-color, #4b5563)',
        'tabs_button_hover_color' => 'var(--jtb-heading-color, #111827)',
        'tabs_button_active_background' => '#ffffff',
        'tabs_button_active_color' => 'var(--jtb-primary-color, #6366f1)',
        'tabs_button_active_box_shadow' => '0 1px 3px rgba(0,0,0,0.1)',
        // Tab content
        'tabs_content_padding' => '24px 0',
        'tabs_content_font_size' => '15px',
        'tabs_content_line_height' => '1.7',
        'tabs_content_color' => 'var(--jtb-text-color, #4b5563)',

        // =============================================
        // COUNTER MODULES (Number, Circle, Bar)
        // =============================================
        // Number counter
        'counter_number_font_size' => '56px',
        'counter_number_font_weight' => '700',
        'counter_number_color' => 'var(--jtb-primary-color, #6366f1)',
        'counter_title_font_size' => '18px',
        'counter_title_font_weight' => '500',
        'counter_title_color' => 'var(--jtb-heading-color, #111827)',
        'counter_title_margin_top' => '12px',
        // Circle counter
        'counter_circle_size' => '200px',
        'counter_circle_stroke_width' => '8px',
        'counter_circle_background_color' => 'var(--jtb-surface-color, #f1f5f9)',
        'counter_circle_bar_color' => 'var(--jtb-primary-color, #6366f1)',
        // Bar counter
        'counter_bar_height' => '24px',
        'counter_bar_background' => 'var(--jtb-surface-color, #f1f5f9)',
        'counter_bar_fill_color' => 'var(--jtb-primary-color, #6366f1)',
        'counter_bar_border_radius' => '12px',
        'counter_bar_label_font_size' => '14px',
        'counter_bar_label_color' => 'var(--jtb-text-color, #4b5563)',
        'counter_bar_percent_font_size' => '14px',
        'counter_bar_percent_font_weight' => '600',

        // =============================================
        // SOCIAL FOLLOW / SOCIAL ICONS MODULE
        // =============================================
        'social_icon_size' => '20px',
        'social_icon_color' => 'var(--jtb-text-light-color, #6b7280)',
        'social_icon_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'social_icon_background' => 'transparent',
        'social_icon_hover_background' => 'var(--jtb-surface-color, #f1f5f9)',
        'social_icon_padding' => '12px',
        'social_icon_border_radius' => '50%',
        'social_icon_gap' => '8px',
        // Branded colors
        'social_use_brand_colors' => true,
        'social_facebook_color' => '#1877f2',
        'social_twitter_color' => '#1da1f2',
        'social_instagram_color' => '#e4405f',
        'social_linkedin_color' => '#0a66c2',
        'social_youtube_color' => '#ff0000',
        'social_tiktok_color' => '#000000',
        'social_pinterest_color' => '#bd081c',

        // =============================================
        // SLIDER MODULE
        // =============================================
        'slider_height' => '500px',
        'slider_height_tablet' => '400px',
        'slider_height_phone' => '300px',
        // Navigation arrows
        'slider_arrow_size' => '48px',
        'slider_arrow_icon_size' => '24px',
        'slider_arrow_background' => 'rgba(255, 255, 255, 0.9)',
        'slider_arrow_color' => 'var(--jtb-heading-color, #111827)',
        'slider_arrow_hover_background' => '#ffffff',
        'slider_arrow_border_radius' => '50%',
        'slider_arrow_box_shadow' => '0 4px 12px rgba(0,0,0,0.15)',
        // Pagination dots
        'slider_dot_size' => '10px',
        'slider_dot_color' => 'rgba(255, 255, 255, 0.5)',
        'slider_dot_active_color' => '#ffffff',
        'slider_dot_gap' => '8px',
        // Slide content
        'slider_content_max_width' => '800px',
        'slider_content_padding' => '60px',
        // Slide title
        'slider_title_font_size' => '48px',
        'slider_title_font_size_tablet' => '36px',
        'slider_title_font_size_phone' => '28px',
        'slider_title_font_weight' => '700',
        'slider_title_color' => '#ffffff',
        'slider_title_margin_bottom' => '20px',
        // Slide description
        'slider_description_font_size' => '18px',
        'slider_description_line_height' => '1.7',
        'slider_description_color' => 'rgba(255, 255, 255, 0.9)',
        'slider_description_margin_bottom' => '32px',

        // =============================================
        // VIDEO MODULE
        // =============================================
        'video_aspect_ratio' => '16/9',
        'video_border_radius' => '12px',
        'video_box_shadow' => '0 10px 40px rgba(0,0,0,0.15)',
        // Play button overlay
        'video_play_button_size' => '80px',
        'video_play_button_icon_size' => '32px',
        'video_play_button_background' => 'rgba(255, 255, 255, 0.95)',
        'video_play_button_color' => 'var(--jtb-primary-color, #6366f1)',
        'video_play_button_hover_scale' => '1.1',
        'video_play_button_box_shadow' => '0 8px 30px rgba(0,0,0,0.2)',
        // Overlay
        'video_overlay_background' => 'rgba(0, 0, 0, 0.3)',

        // =============================================
        // MAP MODULE
        // =============================================
        'map_height' => '400px',
        'map_height_tablet' => '350px',
        'map_height_phone' => '280px',
        'map_border_radius' => '12px',
        'map_box_shadow' => '0 4px 12px rgba(0,0,0,0.1)',

        // =============================================
        // CONTACT FORM MODULE
        // =============================================
        // Input fields
        'form_input_background' => 'var(--jtb-surface-color, #f8fafc)',
        'form_input_border_color' => 'var(--jtb-border-color, #e5e7eb)',
        'form_input_border_width' => '1px',
        'form_input_border_radius' => '8px',
        'form_input_padding' => '14px 16px',
        'form_input_font_size' => '15px',
        'form_input_color' => 'var(--jtb-text-color, #1f2937)',
        'form_input_placeholder_color' => 'var(--jtb-text-light-color, #9ca3af)',
        'form_input_focus_border_color' => 'var(--jtb-primary-color, #6366f1)',
        'form_input_focus_box_shadow' => '0 0 0 3px rgba(99, 102, 241, 0.15)',
        // Labels
        'form_label_font_size' => '14px',
        'form_label_font_weight' => '500',
        'form_label_color' => 'var(--jtb-heading-color, #374151)',
        'form_label_margin_bottom' => '8px',
        // Field spacing
        'form_field_margin_bottom' => '20px',
        // Textarea
        'form_textarea_min_height' => '120px',
        // Submit button (inherits from button defaults)
        'form_submit_full_width' => false,
        // Success/Error messages
        'form_success_color' => '#10b981',
        'form_error_color' => '#ef4444',
        'form_message_font_size' => '14px',

        // =============================================
        // SEARCH MODULE
        // =============================================
        'search_input_height' => '48px',
        'search_input_padding' => '0 48px 0 16px',
        'search_button_size' => '48px',
        'search_button_icon_size' => '20px',
        'search_button_position' => 'inside', // inside or outside

        // =============================================
        // SIDEBAR MODULE
        // =============================================
        'sidebar_widget_margin_bottom' => '40px',
        'sidebar_widget_title_font_size' => '18px',
        'sidebar_widget_title_font_weight' => '600',
        'sidebar_widget_title_color' => 'var(--jtb-heading-color, #111827)',
        'sidebar_widget_title_margin_bottom' => '20px',
        'sidebar_widget_title_border_bottom' => '2px solid var(--jtb-primary-color, #6366f1)',
        'sidebar_widget_title_padding_bottom' => '12px',

        // =============================================
        // COUNTDOWN MODULE
        // =============================================
        'countdown_number_font_size' => '56px',
        'countdown_number_font_size_tablet' => '44px',
        'countdown_number_font_size_phone' => '36px',
        'countdown_number_font_weight' => '700',
        'countdown_number_color' => 'var(--jtb-heading-color, #111827)',
        'countdown_label_font_size' => '14px',
        'countdown_label_font_weight' => '500',
        'countdown_label_color' => 'var(--jtb-text-light-color, #6b7280)',
        'countdown_label_text_transform' => 'uppercase',
        'countdown_separator_color' => 'var(--jtb-border-color, #e5e7eb)',
        'countdown_item_padding' => '24px',
        'countdown_item_gap' => '16px',
        'countdown_item_background' => 'var(--jtb-surface-color, #f8fafc)',
        'countdown_item_border_radius' => '12px',

        // =============================================
        // POST NAVIGATION MODULE
        // =============================================
        'post_nav_padding' => '24px 0',
        'post_nav_border_top' => '1px solid var(--jtb-border-color, #e5e7eb)',
        'post_nav_label_font_size' => '13px',
        'post_nav_label_color' => 'var(--jtb-text-light-color, #6b7280)',
        'post_nav_title_font_size' => '16px',
        'post_nav_title_font_weight' => '600',
        'post_nav_title_color' => 'var(--jtb-heading-color, #111827)',
        'post_nav_title_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'post_nav_arrow_size' => '24px',
        'post_nav_arrow_color' => 'var(--jtb-text-light-color, #6b7280)',

        // =============================================
        // COMMENTS MODULE
        // =============================================
        'comments_title_font_size' => '24px',
        'comments_title_margin_bottom' => '32px',
        'comments_avatar_size' => '48px',
        'comments_avatar_border_radius' => '50%',
        'comments_author_font_size' => '16px',
        'comments_author_font_weight' => '600',
        'comments_author_color' => 'var(--jtb-heading-color, #111827)',
        'comments_date_font_size' => '13px',
        'comments_date_color' => 'var(--jtb-text-light-color, #6b7280)',
        'comments_content_font_size' => '15px',
        'comments_content_line_height' => '1.7',
        'comments_content_color' => 'var(--jtb-text-color, #4b5563)',
        'comments_reply_link_color' => 'var(--jtb-primary-color, #6366f1)',
        'comments_item_padding' => '24px 0',
        'comments_item_border_bottom' => '1px solid var(--jtb-border-color, #e5e7eb)',
        'comments_nested_margin_left' => '48px',

        // =============================================
        // FULLWIDTH HEADER MODULE
        // =============================================
        'fullwidth_header_min_height' => '500px',
        'fullwidth_header_padding' => '100px 40px',
        'fullwidth_header_content_max_width' => '800px',
        'fullwidth_header_text_align' => 'center',
        'fullwidth_header_overlay_color' => 'rgba(0, 0, 0, 0.5)',
        // Title
        'fullwidth_header_title_font_size' => '56px',
        'fullwidth_header_title_font_size_tablet' => '44px',
        'fullwidth_header_title_font_size_phone' => '36px',
        'fullwidth_header_title_font_weight' => '700',
        'fullwidth_header_title_color' => '#ffffff',
        'fullwidth_header_title_margin_bottom' => '24px',
        // Subtitle
        'fullwidth_header_subtitle_font_size' => '20px',
        'fullwidth_header_subtitle_line_height' => '1.7',
        'fullwidth_header_subtitle_color' => 'rgba(255, 255, 255, 0.9)',
        'fullwidth_header_subtitle_margin_bottom' => '40px',

        // =============================================
        // MENU MODULE (Theme Builder)
        // =============================================
        'menu_font_family' => 'inherit',
        'menu_font_size' => '15px',
        'menu_font_weight' => '500',
        'menu_text_transform' => 'none',
        'menu_letter_spacing' => '0',
        'menu_link_color' => 'var(--jtb-text-color, #374151)',
        'menu_link_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'menu_link_active_color' => 'var(--jtb-primary-color, #6366f1)',
        'menu_link_padding' => '12px 16px',
        'menu_gap' => '4px',
        // Dropdown
        'menu_dropdown_background' => '#ffffff',
        'menu_dropdown_border_radius' => '12px',
        'menu_dropdown_box_shadow' => '0 10px 40px rgba(0,0,0,0.15)',
        'menu_dropdown_padding' => '8px',
        'menu_dropdown_min_width' => '220px',
        'menu_dropdown_link_padding' => '10px 16px',
        'menu_dropdown_link_border_radius' => '8px',
        'menu_dropdown_link_hover_background' => 'var(--jtb-surface-color, #f8fafc)',
        // Mobile
        'menu_mobile_breakpoint' => '980px',
        'menu_mobile_background' => '#ffffff',
        'menu_mobile_link_padding' => '16px 20px',
        'menu_mobile_link_border_bottom' => '1px solid var(--jtb-border-color, #e5e7eb)',
        'menu_hamburger_color' => 'var(--jtb-heading-color, #111827)',
        'menu_hamburger_size' => '24px',

        // =============================================
        // LOGO MODULE (Theme Builder)
        // =============================================
        'logo_max_height' => '60px',
        'logo_max_height_tablet' => '50px',
        'logo_max_height_phone' => '40px',

        // =============================================
        // BREADCRUMBS MODULE (Theme Builder)
        // =============================================
        'breadcrumbs_font_size' => '14px',
        'breadcrumbs_color' => 'var(--jtb-text-light-color, #6b7280)',
        'breadcrumbs_link_color' => 'var(--jtb-text-color, #4b5563)',
        'breadcrumbs_link_hover_color' => 'var(--jtb-primary-color, #6366f1)',
        'breadcrumbs_separator' => '/',
        'breadcrumbs_separator_color' => 'var(--jtb-text-light-color, #9ca3af)',
        'breadcrumbs_separator_margin' => '0 8px',

        // =============================================
        // RELATED POSTS MODULE (Theme Builder)
        // =============================================
        'related_posts_columns' => '3',
        'related_posts_gap' => '24px',
        'related_posts_title_font_size' => '24px',
        'related_posts_title_margin_bottom' => '24px',
        // Uses blog card styles for individual posts

        // =============================================
        // AUTHOR BOX MODULE (Theme Builder)
        // =============================================
        'author_box_background' => 'var(--jtb-surface-color, #f8fafc)',
        'author_box_padding' => '32px',
        'author_box_border_radius' => '16px',
        'author_box_avatar_size' => '80px',
        'author_box_avatar_border_radius' => '50%',
        'author_box_name_font_size' => '20px',
        'author_box_name_font_weight' => '600',
        'author_box_name_color' => 'var(--jtb-heading-color, #111827)',
        'author_box_bio_font_size' => '15px',
        'author_box_bio_line_height' => '1.7',
        'author_box_bio_color' => 'var(--jtb-text-color, #4b5563)',
    ];

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        // Load theme settings from database on first access
    }

    /**
     * Load theme settings from database
     */
    private function loadThemeSettings(): void
    {
        if ($this->settingsLoaded) {
            return;
        }

        try {
            // Try to load from JTB_Theme_Settings if available
            if (class_exists('\\JessieThemeBuilder\\JTB_Theme_Settings')) {
                // Use static method getAll() instead of instance method
                $this->themeSettings = JTB_Theme_Settings::getAll() ?? [];
            }
        } catch (\Exception $e) {
            // Silently fail - use defaults
            $this->themeSettings = [];
        }

        $this->settingsLoaded = true;
    }

    /**
     * Get a setting value with fallback hierarchy:
     * 1. Module attribute (passed directly)
     * 2. Theme Settings (database)
     * 3. Default value (this class)
     *
     * @param string $key Setting key (e.g., 'gallery_title_font_size')
     * @param mixed $default Optional override for default value
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $instance = self::getInstance();
        $instance->loadThemeSettings();

        // Check theme settings first (user customizations)
        if (isset($instance->themeSettings[$key])) {
            return $instance->themeSettings[$key];
        }

        // Fall back to hardcoded defaults
        if (isset(self::$defaults[$key])) {
            return self::$defaults[$key];
        }

        // Use provided default or null
        return $default;
    }

    /**
     * Get all defaults for a specific module
     *
     * @param string $module Module prefix (e.g., 'gallery', 'blog', 'button')
     * @return array
     */
    public static function getModuleDefaults(string $module): array
    {
        $moduleDefaults = [];
        $prefix = $module . '_';
        $prefixLen = strlen($prefix);

        foreach (self::$defaults as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                // Remove prefix for cleaner key
                $shortKey = substr($key, $prefixLen);
                $moduleDefaults[$shortKey] = $value;
            }
        }

        return $moduleDefaults;
    }

    /**
     * Get all global defaults
     *
     * @return array
     */
    public static function getGlobalDefaults(): array
    {
        $globals = [];
        foreach (self::$defaults as $key => $value) {
            if (strpos($key, 'global_') === 0) {
                $globals[$key] = $value;
            }
        }
        return $globals;
    }

    /**
     * Get all defaults
     *
     * @return array
     */
    public static function getAllDefaults(): array
    {
        return self::$defaults;
    }

    /**
     * Check if a value differs from the default
     *
     * @param string $key Setting key
     * @param mixed $value Value to compare
     * @return bool True if value differs from default
     */
    public static function isDifferentFromDefault(string $key, $value): bool
    {
        $default = self::get($key);
        return $value !== null && $value !== '' && $value !== $default;
    }

    /**
     * Get CSS variable name for a setting
     *
     * @param string $key Setting key
     * @return string CSS variable name
     */
    public static function getCssVariableName(string $key): string
    {
        return '--jtb-' . str_replace('_', '-', $key);
    }

    /**
     * Generate CSS custom properties for all defaults
     * This is used to create the :root variables
     *
     * @return string CSS string
     */
    public static function generateCssVariables(): string
    {
        $css = ":root {\n";

        foreach (self::$defaults as $key => $value) {
            // Skip values that are already CSS variable references
            if (strpos($value, 'var(--') === 0) {
                continue;
            }

            $varName = self::getCssVariableName($key);
            $css .= "    {$varName}: {$value};\n";
        }

        $css .= "}\n";

        return $css;
    }

    /**
     * Merge module attributes with defaults
     * Returns only values that differ from defaults (for minimal CSS output)
     *
     * @param string $module Module prefix
     * @param array $attrs Module attributes
     * @return array Merged values (only non-default)
     */
    public static function mergeWithDefaults(string $module, array $attrs): array
    {
        $defaults = self::getModuleDefaults($module);
        $merged = [];

        foreach ($defaults as $key => $defaultValue) {
            $fullKey = $module . '_' . $key;
            $attrKey = $key; // Attribute key without prefix

            if (isset($attrs[$attrKey]) && $attrs[$attrKey] !== '' && $attrs[$attrKey] !== $defaultValue) {
                $merged[$key] = $attrs[$attrKey];
            }
        }

        return $merged;
    }
}
