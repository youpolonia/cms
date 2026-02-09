<?php
/**
 * Default Styles for all JTB Modules
 *
 * Centralized default values matching the Layout Library design system.
 * All modules should use these defaults for consistent styling.
 *
 * Design System:
 * - Primary: #6366f1 (Indigo)
 * - Dark: #0f172a (Slate 900)
 * - Text Dark: #1a1a2e (Headings)
 * - Text: #64748b (Body)
 * - Text Light: #94a3b8 (Muted)
 * - Font: Inter
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_Default_Styles
{
    // =========================================================
    // DESIGN SYSTEM COLORS
    // =========================================================

    const PRIMARY = '#6366f1';
    const PRIMARY_DARK = '#4f46e5';
    const SECONDARY = '#10b981';
    const DARK = '#0f172a';
    const DARK_LIGHT = '#1e293b';
    const TEXT_DARK = '#1a1a2e';
    const TEXT = '#64748b';
    const TEXT_LIGHT = '#94a3b8';
    const WHITE = '#ffffff';
    const LIGHT = '#f8fafc';
    const BORDER = '#e2e8f0';

    // =========================================================
    // TYPOGRAPHY
    // =========================================================

    const FONT_FAMILY = 'Inter';
    const FONT_SIZE_XS = 12;
    const FONT_SIZE_SM = 14;
    const FONT_SIZE_BASE = 16;
    const FONT_SIZE_LG = 18;
    const FONT_SIZE_XL = 20;
    const FONT_SIZE_2XL = 24;
    const FONT_SIZE_3XL = 30;
    const FONT_SIZE_4XL = 36;
    const FONT_SIZE_5XL = 48;
    const FONT_SIZE_6XL = 56;

    const LINE_HEIGHT = '1.6';
    const LINE_HEIGHT_TIGHT = '1.2';
    const LINE_HEIGHT_LOOSE = '1.8';

    // =========================================================
    // SPACING
    // =========================================================

    const SPACING_XS = 8;
    const SPACING_SM = 12;
    const SPACING_MD = 16;
    const SPACING_LG = 24;
    const SPACING_XL = 32;
    const SPACING_2XL = 48;
    const SPACING_3XL = 64;

    // =========================================================
    // BORDER RADIUS
    // =========================================================

    const RADIUS_SM = 4;
    const RADIUS_MD = 8;
    const RADIUS_LG = 12;
    const RADIUS_XL = 16;
    const RADIUS_FULL = 9999;

    // =========================================================
    // MODULE DEFAULTS
    // =========================================================

    /**
     * Get default attributes for a module type
     */
    public static function getDefaults(string $moduleType): array
    {
        $defaults = self::getAllDefaults();
        return $defaults[$moduleType] ?? [];
    }

    /**
     * Merge user attributes with defaults
     */
    /**
     * Module types where background styling should NOT have defaults applied
     * These are structural elements where AI specifies styling explicitly
     */
    private static array $noDefaultBackgroundFor = ['section', 'row', 'column'];
    
    /**
     * Attributes to skip for structural elements (preserve AI styling intent)
     */
    private static array $structuralSkipAttrs = [
        'background_color',
        'background_type', 
        'background_image',
        'background_gradient',
        'text_color',
    ];

    public static function mergeWithDefaults(string $moduleType, array $attrs): array
    {
        $defaults = self::getDefaults($moduleType);
        $isStructural = in_array($moduleType, self::$noDefaultBackgroundFor);

        // Only apply defaults for keys that are not set
        foreach ($defaults as $key => $value) {
            // For structural elements (section/row/column), skip background/text defaults
            // This preserves AI-generated styling intent
            if ($isStructural && in_array($key, self::$structuralSkipAttrs)) {
                continue;
            }
            
            if (!isset($attrs[$key]) || $attrs[$key] === '' || $attrs[$key] === null) {
                $attrs[$key] = $value;
            }
        }

        return $attrs;
    }

    /**
     * All module defaults
     */
    public static function getAllDefaults(): array
    {
        return [
            // =====================================================
            // HEADING
            // =====================================================
            'heading' => [
                'text' => 'Your Heading Here',
                'level' => 'h2',
                'font_family' => self::FONT_FAMILY,
                'font_size' => self::FONT_SIZE_4XL,
                'font_weight' => '700',
                'line_height' => self::LINE_HEIGHT_TIGHT,
                'text_color' => self::TEXT_DARK,
                'text_align' => 'left',
            ],

            // =====================================================
            // TEXT
            // =====================================================
            'text' => [
                'content' => '<p>Your content goes here. Edit this text to add your own content.</p>',
                'font_family' => self::FONT_FAMILY,
                'font_size' => self::FONT_SIZE_BASE,
                'line_height' => self::LINE_HEIGHT,
                'text_color' => self::TEXT,
            ],

            // =====================================================
            // BUTTON
            // =====================================================
            'button' => [
                'text' => 'Click Here',
                'url' => '#',
                'font_family' => self::FONT_FAMILY,
                'font_size' => self::FONT_SIZE_BASE,
                'font_weight' => '600',
                'background_type' => 'color',  // ADDED 2026-02-03
                'background_color' => self::PRIMARY,
                'text_color' => self::WHITE,
                'background_color__hover' => self::PRIMARY_DARK,
                'text_color__hover' => self::WHITE,
                'padding' => [
                    'top' => self::SPACING_SM,
                    'right' => self::SPACING_LG,
                    'bottom' => self::SPACING_SM,
                    'left' => self::SPACING_LG,
                ],
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],

            // =====================================================
            // BLURB
            // =====================================================
            'blurb' => [
                'title' => 'Your Title Here',
                'content' => '<p>Your content goes here. Add a short description.</p>',
                'use_icon' => true,
                'font_icon' => 'star',
                'icon_color' => self::PRIMARY,
                'icon_font_size' => 48,
                'title_font_size' => self::FONT_SIZE_XL,
                'title_font_weight' => '600',
                'title_color' => self::TEXT_DARK,
                'content_color' => self::TEXT,
                'content_font_size' => self::FONT_SIZE_BASE,
                'text_orientation' => 'center',
                'header_level' => 'h4',
            ],

            // =====================================================
            // IMAGE
            // =====================================================
            'image' => [
                'alt' => '',
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],

            // =====================================================
            // DIVIDER
            // =====================================================
            'divider' => [
                'color' => self::BORDER,
                'divider_style' => 'solid',
                'divider_weight' => 1,
                'divider_position' => 'center',
            ],

            // =====================================================
            // TESTIMONIAL
            // =====================================================
            'testimonial' => [
                'content' => 'This is an amazing product! I highly recommend it to everyone.',
                'author' => 'John Doe',
                'position' => 'CEO, Company',
                'font_family' => self::FONT_FAMILY,
                'content_font_size' => self::FONT_SIZE_LG,
                'content_color' => self::TEXT,
                'author_font_size' => self::FONT_SIZE_BASE,
                'author_font_weight' => '600',
                'author_color' => self::TEXT_DARK,
                'position_font_size' => self::FONT_SIZE_SM,
                'position_color' => self::TEXT_LIGHT,
                'background_type' => 'color',  // ADDED 2026-02-03
                'background_color' => self::WHITE,
                'padding' => [
                    'top' => self::SPACING_LG,
                    'right' => self::SPACING_LG,
                    'bottom' => self::SPACING_LG,
                    'left' => self::SPACING_LG,
                ],
                'border_radius' => [
                    'top_left' => self::RADIUS_LG,
                    'top_right' => self::RADIUS_LG,
                    'bottom_right' => self::RADIUS_LG,
                    'bottom_left' => self::RADIUS_LG,
                ],
            ],

            // =====================================================
            // TEAM MEMBER
            // =====================================================
            'team_member' => [
                'name' => 'John Doe',
                'position' => 'Job Title',
                'font_family' => self::FONT_FAMILY,
                'name_font_size' => self::FONT_SIZE_XL,
                'name_font_weight' => '600',
                'name_color' => self::TEXT_DARK,
                'position_font_size' => self::FONT_SIZE_SM,
                'position_color' => self::TEXT,
                'text_align' => 'center',
            ],

            // =====================================================
            // PRICING TABLE
            // =====================================================
            'pricing_table' => [
                'title' => 'Basic',
                'subtitle' => 'For individuals',
                'price' => '29',
                'currency' => '$',
                'period' => '/month',
                'features' => "Feature One\nFeature Two\nFeature Three\nFeature Four",
                'button_text' => 'Get Started',
                'link_url' => '#',
                'font_family' => self::FONT_FAMILY,
                'background_type' => 'color',  // ADDED 2026-02-03
                'background_color' => self::WHITE,
                'border_color' => self::BORDER,
                'title_font_size' => self::FONT_SIZE_2XL,
                'title_font_weight' => '700',
                'title_color' => self::TEXT_DARK,
                'price_font_size' => self::FONT_SIZE_5XL,
                'price_font_weight' => '800',
                'price_color' => self::TEXT_DARK,
                'features_color' => self::TEXT,
                'button_background' => self::PRIMARY,
                'button_text_color' => self::WHITE,
                'padding' => [
                    'top' => self::SPACING_XL,
                    'right' => self::SPACING_LG,
                    'bottom' => self::SPACING_XL,
                    'left' => self::SPACING_LG,
                ],
                'border_radius' => [
                    'top_left' => self::RADIUS_LG,
                    'top_right' => self::RADIUS_LG,
                    'bottom_right' => self::RADIUS_LG,
                    'bottom_left' => self::RADIUS_LG,
                ],
            ],

            // =====================================================
            // CTA
            // =====================================================
            'cta' => [
                'title' => 'Ready to Get Started?',
                'content' => '<p>Contact us today and let us help your business grow.</p>',
                'button_text' => 'Contact Us',
                'link_url' => '#',
                'font_family' => self::FONT_FAMILY,
                'title_font_size' => self::FONT_SIZE_4XL,
                'title_font_weight' => '700',
                'title_color' => self::WHITE,
                'content_color' => self::TEXT_LIGHT,
                'background_type' => 'color',  // ADDED 2026-02-03: explicit background_type
                'background_color' => self::DARK,
                'button_background' => self::PRIMARY,
                'button_text_color' => self::WHITE,
                'padding' => [
                    'top' => self::SPACING_3XL,
                    'right' => self::SPACING_2XL,
                    'bottom' => self::SPACING_3XL,
                    'left' => self::SPACING_2XL,
                ],
            ],

            // =====================================================
            // ACCORDION
            // =====================================================
            'accordion' => [
                'toggle_icon' => 'arrow',
                'icon_color' => self::TEXT_DARK,
                'open_toggle_background_color' => self::LIGHT,
            ],

            'accordion_item' => [
                'title' => 'Accordion Item',
                'content' => '<p>Your accordion content goes here.</p>',
                'font_family' => self::FONT_FAMILY,
                'title_font_size' => self::FONT_SIZE_BASE,
                'title_font_weight' => '600',
                'title_color' => self::TEXT_DARK,
                'content_color' => self::TEXT,
                'background_type' => 'color',  // ADDED 2026-02-03
                'background_color' => self::WHITE,
                'border_color' => self::BORDER,
            ],

            // =====================================================
            // TABS
            // =====================================================
            'tabs' => [
                'tab_font_family' => self::FONT_FAMILY,
                'tab_font_size' => self::FONT_SIZE_BASE,
                'tab_font_weight' => '500',
                'tab_text_color' => self::TEXT,
                'tab_text_color_active' => self::PRIMARY,
                'tab_background_color' => 'transparent',
                'tab_background_color_active' => self::WHITE,
                'body_background_color' => self::WHITE,
            ],

            'tabs_item' => [
                'title' => 'Tab',
                'content' => '<p>Your tab content goes here.</p>',
                'content_color' => self::TEXT,
            ],

            // =====================================================
            // GALLERY
            // =====================================================
            'gallery' => [
                'columns' => 3,
                'gap' => 20,
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],

            // =====================================================
            // ICON
            // =====================================================
            'icon' => [
                'font_icon' => 'star',
                'icon_color' => self::PRIMARY,
                'icon_color__hover' => self::PRIMARY_DARK,
                'icon_font_size' => 48,
            ],

            // =====================================================
            // SOCIAL ICONS
            // =====================================================
            'social_icons' => [
                'icon_color' => self::TEXT,
                'icon_color_hover' => self::PRIMARY,
                'icon_size' => 20,
                'icon_spacing' => 12,
            ],

            'social_follow' => [
                'icon_color' => self::TEXT,
                'icon_color__hover' => self::PRIMARY,
                'icon_size' => 24,
                'icon_spacing' => 16,
            ],

            // =====================================================
            // COUNTERS
            // =====================================================
            'number_counter' => [
                'title' => 'Happy Clients',
                'number' => 100,
                'percent_sign' => false,
                'font_family' => self::FONT_FAMILY,
                'number_font_size' => self::FONT_SIZE_5XL,
                'number_font_weight' => '800',
                'number_color' => self::PRIMARY,
                'title_font_size' => self::FONT_SIZE_BASE,
                'title_color' => self::TEXT,
            ],

            'circle_counter' => [
                'title' => 'Progress',
                'number' => 75,
                'bar_color' => self::PRIMARY,
                'track_color' => self::BORDER,
                'number_color' => self::TEXT_DARK,
                'title_color' => self::TEXT,
            ],

            'bar_counter' => [
                'title' => 'Skill',
                'percent' => 80,
                'bar_color' => self::PRIMARY,
                'track_color' => self::BORDER,
                'title_color' => self::TEXT_DARK,
            ],

            // =====================================================
            // COUNTDOWN
            // =====================================================
            'countdown' => [
                'font_family' => self::FONT_FAMILY,
                'number_font_size' => self::FONT_SIZE_5XL,
                'number_font_weight' => '700',
                'number_color' => self::TEXT_DARK,
                'label_font_size' => self::FONT_SIZE_SM,
                'label_color' => self::TEXT,
                'separator_color' => self::TEXT_LIGHT,
            ],

            // =====================================================
            // CONTACT FORM
            // =====================================================
            'contact_form' => [
                'button_text' => 'Send Message',
                'button_background' => self::PRIMARY,
                'button_text_color' => self::WHITE,
                'input_background' => self::WHITE,
                'input_border_color' => self::BORDER,
                'input_text_color' => self::TEXT_DARK,
                'input_border_radius' => self::RADIUS_MD,
                'label_color' => self::TEXT_DARK,
            ],

            // =====================================================
            // BLOG / POSTS
            // =====================================================
            'blog' => [
                'columns' => 3,
                'gap' => 30,
                'title_font_size' => self::FONT_SIZE_XL,
                'title_font_weight' => '600',
                'title_color' => self::TEXT_DARK,
                'excerpt_color' => self::TEXT,
                'meta_color' => self::TEXT_LIGHT,
                'card_background' => self::WHITE,
                'card_border_radius' => self::RADIUS_LG,
            ],

            // =====================================================
            // MENU (Header)
            // =====================================================
            'menu' => [
                'menu_style' => 'left_aligned',
                'font_family' => self::FONT_FAMILY,
                'menu_font_size' => 15,
                'menu_font_weight' => '500',
                'menu_text_color' => self::TEXT,
                'menu_text_color__hover' => self::PRIMARY,
                'icon_color' => self::TEXT,
                'icon_color__hover' => self::PRIMARY,
                'menu_item_spacing' => 24,
                'icon_size' => 20,
                'logo_width' => 150,
            ],

            // =====================================================
            // SECTION
            // =====================================================
            'section' => [
                // FIXED 2026-02-08: Don't force white background on AI-generated sections
                // AI should explicitly set background_type and background_color when needed
                'background_type' => 'none',
                'fullwidth' => false,
                'inner_width' => 1200,
                'padding' => [
                    'top' => 80,
                    'right' => 0,
                    'bottom' => 80,
                    'left' => 0,
                ],
                'padding_tablet' => [
                    'top' => 60,
                    'right' => 0,
                    'bottom' => 60,
                    'left' => 0,
                ],
                'padding_phone' => [
                    'top' => 40,
                    'right' => 0,
                    'bottom' => 40,
                    'left' => 0,
                ],
            ],

            // =====================================================
            // ROW
            // =====================================================
            'row' => [
                'columns' => '1',
                'column_gap' => 30,
                'row_gap' => 30,
                'equal_heights' => true,
                'vertical_align' => 'stretch',
                'horizontal_align' => 'stretch',
                'max_width' => 1200,
                'padding' => [
                    'top' => 0,
                    'right' => 20,
                    'bottom' => 0,
                    'left' => 20,
                ],
            ],

            // =====================================================
            // COLUMN
            // =====================================================
            'column' => [
                'vertical_align' => 'top',
                'padding' => [
                    'top' => self::SPACING_MD,
                    'right' => self::SPACING_MD,
                    'bottom' => self::SPACING_MD,
                    'left' => self::SPACING_MD,
                ],
            ],

            // =====================================================
            // CODE
            // =====================================================
            'code' => [
                'background_type' => 'color',  // ADDED 2026-02-03: explicit background_type
                'background_color' => self::DARK,
                'text_color' => self::WHITE,
                'font_size' => self::FONT_SIZE_SM,
                'padding' => [
                    'top' => self::SPACING_MD,
                    'right' => self::SPACING_MD,
                    'bottom' => self::SPACING_MD,
                    'left' => self::SPACING_MD,
                ],
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],

            // =====================================================
            // VIDEO
            // =====================================================
            'video' => [
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],

            // =====================================================
            // MAP
            // =====================================================
            'map' => [
                'height' => 400,
                'border_radius' => [
                    'top_left' => self::RADIUS_MD,
                    'top_right' => self::RADIUS_MD,
                    'bottom_right' => self::RADIUS_MD,
                    'bottom_left' => self::RADIUS_MD,
                ],
            ],
        ];
    }

    /**
     * Get color palette for UI
     */
    public static function getColorPalette(): array
    {
        return [
            'primary' => self::PRIMARY,
            'primary_dark' => self::PRIMARY_DARK,
            'secondary' => self::SECONDARY,
            'dark' => self::DARK,
            'dark_light' => self::DARK_LIGHT,
            'text_dark' => self::TEXT_DARK,
            'text' => self::TEXT,
            'text_light' => self::TEXT_LIGHT,
            'white' => self::WHITE,
            'light' => self::LIGHT,
            'border' => self::BORDER,
        ];
    }
}
