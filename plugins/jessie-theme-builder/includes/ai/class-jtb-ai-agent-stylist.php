<?php
/**
 * JTB AI Agent Stylist
 *
 * Multi-agent system: Stylist agent responsible for applying design attributes.
 *
 * PHP-FIRST APPROACH:
 * - 90% styles from JTB_AI_Styles (existing code, ~1400 lines)
 * - AI calls only for 10% creative overrides (hero gradients, CTA special effects)
 *
 * Uses PATH-based references (e.g., "home/hero/col0/heading_0")
 * All module fields from JTB_Registry (ZERO hardcodes)
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Agent_Stylist
{
    /**
     * Section types that should get special AI creative treatment
     * Everything else uses PHP-only styles from JTB_AI_Styles
     */
    private const CREATIVE_SECTIONS = [
        'hero',
        'fullwidth_hero',
        'cta',
        'call_to_action',
        'final_cta',
        'stats',
        'counters'
    ];

    /**
     * Execute stylist agent
     *
     * @param array $session Session data with skeleton, path_map, style, industry, color_scheme
     * @return array Result with styles array keyed by PATH
     */
    public static function execute(array $session): array
    {
        $startTime = microtime(true);

        // Get data from session
        $skeleton = $session['skeleton'] ?? [];
        $pathMap = $session['path_map'] ?? [];
        $style = $session['style'] ?? 'modern';
        $industry = $session['industry'] ?? 'general';
        $colorScheme = $session['color_scheme'] ?? [];

        if (empty($pathMap)) {
            return [
                'ok' => false,
                'error' => 'No path_map found. Run architect step first.'
            ];
        }

        // Build context for styling
        $context = [
            'style' => $style,
            'industry' => $industry,
            'colors' => $colorScheme,
            'mockup_structure' => $session['mockup_structure'] ?? []
        ];

        // Generate styles for all paths
        $styles = [];
        $tokensUsed = 0;
        $creativeSections = [];

        // Collect section metadata for context
        $sectionMetadata = self::collectSectionMetadata($skeleton, $pathMap);

        // Process each path
        foreach ($pathMap as $path => $id) {
            // Parse path to get module type and context
            $pathInfo = self::parsePath($path);

            if (!$pathInfo) {
                continue;
            }

            $moduleType = $pathInfo['module_type'];
            $blueprint = $pathInfo['blueprint'] ?? 'generic';
            $region = $pathInfo['region'] ?? null;

            // Skip if module type could not be determined
            if (empty($moduleType)) {
                continue;
            }

            // Build module-specific context
            $moduleContext = array_merge($context, [
                'path' => $path,
                'id' => $id,
                'blueprint' => $blueprint,
                'region' => $region,
                'section_index' => $pathInfo['section_index'] ?? 0,
                'is_hero' => in_array($blueprint, ['hero', 'fullwidth_hero']),
                'is_cta' => in_array($blueprint, ['cta', 'call_to_action', 'final_cta']),
                'is_header' => $region === 'header',
                'is_footer' => $region === 'footer'
            ]);

            // Check if this path's section needs creative treatment
            if (self::needsCreativeTreatment($blueprint, $moduleContext)) {
                $creativeSections[$path] = [
                    'module_type' => $moduleType,
                    'blueprint' => $blueprint,
                    'context' => $moduleContext
                ];
            }

            // Generate PHP-based styles
            $moduleStyles = self::generateModuleStyles($moduleType, $style, $moduleContext);

            if (!empty($moduleStyles)) {
                $styles[$path] = $moduleStyles;
            }
        }

        // Apply AI creative overrides if needed
        if (!empty($creativeSections)) {
            $creativeResult = self::applyCreativeOverrides($creativeSections, $styles, $context);
            $styles = array_merge($styles, $creativeResult['styles']);
            $tokensUsed = $creativeResult['tokens_used'] ?? 0;
        }

        // Apply visual rhythm adjustments (Stage 13)
        $styles = self::applyVisualRhythm($styles, $sectionMetadata, $context);

        // Apply section background alternation
        $styles = self::applySectionBackgrounds($styles, $sectionMetadata, $context);

        $timeMs = (int)((microtime(true) - $startTime) * 1000);

        return [
            'ok' => true,
            'styles' => $styles,
            'tokens_used' => $tokensUsed,
            'stats' => [
                'time_ms' => $timeMs,
                'modules_styled' => count($styles),
                'creative_sections' => count($creativeSections),
                'php_only_sections' => count($styles) - count($creativeSections)
            ]
        ];
    }

    /**
     * Generate styles for a module using JTB_AI_Styles (PHP-FIRST)
     *
     * @param string $moduleType Module type (heading, text, blurb, etc.)
     * @param string $style Design style (modern, minimal, bold, etc.)
     * @param array $context Module context
     * @return array Style attributes
     */
    private static function generateModuleStyles(string $moduleType, string $style, array $context): array
    {
        $moduleType = self::normalizeModuleSlug($moduleType);

        // Get base styles from existing JTB_AI_Styles class
        switch ($moduleType) {
            case 'section':
                return self::getSectionStyles($style, $context);

            case 'row':
                return JTB_AI_Styles::getRowAttrs($style, $context);

            case 'column':
                return JTB_AI_Styles::getColumnAttrs($style, $context);

            case 'heading':
                return self::getHeadingStyles($style, $context);

            case 'text':
                return JTB_AI_Styles::getTextStyles($style, $context);

            case 'button':
                return self::getButtonStyles($style, $context);

            case 'blurb':
                return JTB_AI_Styles::getBlurbStyles($style, $context);

            case 'image':
                return JTB_AI_Styles::getImageStyles($style, $context);

            case 'testimonial':
                return JTB_AI_Styles::getTestimonialStyles($style, $context);

            case 'pricing_table':
                return self::getPricingStyles($style, $context);

            case 'cta':
                return JTB_AI_Styles::getCTAStyles($style, $context);

            case 'team_member':
                return JTB_AI_Styles::getTeamMemberStyles($style, $context);

            case 'number_counter':
            case 'circle_counter':
            case 'bar_counter':
                return JTB_AI_Styles::getCounterStyles($style, $context);

            case 'accordion':
            case 'accordion_item':
                return self::getAccordionStyles($style, $context);

            case 'tabs':
            case 'tabs_item':
                return self::getTabsStyles($style, $context);

            case 'divider':
                return self::getDividerStyles($style, $context);

            case 'icon':
                return self::getIconStyles($style, $context);

            case 'social_icons':
            case 'social_follow':
                return self::getSocialIconsStyles($style, $context);

            case 'gallery':
                return self::getGalleryStyles($style, $context);

            case 'slider':
            case 'fullwidth_slider':
                return self::getSliderStyles($style, $context);

            case 'video':
                return self::getVideoStyles($style, $context);

            case 'contact_form':
                return self::getContactFormStyles($style, $context);

            case 'blog':
            case 'portfolio':
                return self::getBlogStyles($style, $context);

            case 'map':
                return self::getMapStyles($style, $context);

            // Theme modules
            case 'menu':
            case 'footer_menu':
                return self::getMenuStyles($style, $context);

            case 'site_logo':
                return self::getLogoStyles($style, $context);

            case 'breadcrumbs':
                return self::getBreadcrumbsStyles($style, $context);

            case 'search_form':
                return self::getSearchFormStyles($style, $context);

            case 'copyright':
            case 'footer_info':
                return self::getFooterInfoStyles($style, $context);

            default:
                // Generic module styling
                return self::getGenericModuleStyles($style, $context);
        }
    }

    /**
     * Get section styles with blueprint-specific adjustments
     */
    private static function getSectionStyles(string $style, array $context): array
    {
        $blueprint = $context['blueprint'] ?? 'generic';

        // Use JTB_AI_Styles::getSectionAttrs
        $attrs = JTB_AI_Styles::getSectionAttrs($blueprint, $style, $context);

        // Add additional metadata for AutoFix stages
        $attrs['_pattern'] = $blueprint;
        $attrs['_visual_context'] = JTB_AI_Styles::getVisualContextForSection($blueprint);

        return $attrs;
    }

    /**
     * Get heading styles with level-specific adjustments
     */
    private static function getHeadingStyles(string $style, array $context): array
    {
        // Determine heading level from role or default to h2
        $level = 'h2';

        if (!empty($context['role'])) {
            if (strpos($context['role'], 'h1') !== false) {
                $level = 'h1';
            } elseif (strpos($context['role'], 'h3') !== false) {
                $level = 'h3';
            } elseif (strpos($context['role'], 'title') !== false && ($context['is_hero'] ?? false)) {
                $level = 'h1';
            }
        }

        // Hero section headings are usually h1
        if ($context['is_hero'] ?? false) {
            $level = 'h1';
        }

        $headingContext = array_merge($context, ['level' => $level]);
        $attrs = JTB_AI_Styles::getHeadingStyles($style, $headingContext);

        // Apply visual context colors
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        if ($visualContext === 'DARK' || $visualContext === 'PRIMARY') {
            $attrs['text_color'] = $contextColors['heading'];
        }

        return $attrs;
    }

    /**
     * Get button styles with variant detection
     */
    private static function getButtonStyles(string $style, array $context): array
    {
        // Determine variant from context
        $variant = 'primary';

        if (!empty($context['role'])) {
            if (strpos($context['role'], 'secondary') !== false) {
                $variant = 'secondary';
            } elseif (strpos($context['role'], 'ghost') !== false) {
                $variant = 'ghost';
            }
        }

        // CTA sections typically have inverted buttons
        if ($context['is_cta'] ?? false) {
            $variant = 'secondary'; // White/light button on colored background
        }

        $buttonContext = array_merge($context, ['variant' => $variant]);
        $attrs = JTB_AI_Styles::getButtonStyles($style, $buttonContext);

        // Apply visual context colors
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        if ($visualContext === 'DARK' || $visualContext === 'PRIMARY') {
            $attrs['background_color'] = $contextColors['button_primary_bg'];
            $attrs['text_color'] = $contextColors['button_primary_text'];
        }

        return $attrs;
    }

    /**
     * Get pricing table styles with featured detection
     */
    private static function getPricingStyles(string $style, array $context): array
    {
        // Check if this is a featured pricing card
        $isFeatured = false;

        if (!empty($context['role']) && strpos($context['role'], 'featured') !== false) {
            $isFeatured = true;
        }

        // Middle card in a 3-column layout is often featured
        $sectionIndex = $context['section_index'] ?? 0;
        if ($sectionIndex === 1) {
            $isFeatured = true;
        }

        $pricingContext = array_merge($context, ['featured' => $isFeatured]);
        return JTB_AI_Styles::getPricingStyles($style, $pricingContext);
    }

    /**
     * Get accordion styles
     */
    private static function getAccordionStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);

        return [
            'toggle_icon_color' => $colors['primary'],
            'toggle_icon_size' => 20,
            'title_font_family' => $typography['heading_font'],
            'title_font_size' => 18,
            'title_font_weight' => '600',
            'title_color' => $colors['heading'],
            'title_color__hover' => $colors['primary'],
            'content_font_family' => $typography['body_font'],
            'content_font_size' => 16,
            'content_color' => $colors['text'],
            'item_border_color' => $colors['background_alt'] ?? '#E5E7EB',
            'item_border_width' => 1,
            'item_padding' => ['top' => 20, 'right' => 24, 'bottom' => 20, 'left' => 24]
        ];
    }

    /**
     * Get tabs styles
     */
    private static function getTabsStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);

        return [
            'tab_font_family' => $typography['heading_font'],
            'tab_font_size' => 16,
            'tab_font_weight' => '500',
            'tab_color' => $colors['text_light'],
            'tab_color_active' => $colors['primary'],
            'tab_background' => 'transparent',
            'tab_background_active' => $colors['background_alt'] ?? '#F9FAFB',
            'tab_border_color' => $colors['background_alt'] ?? '#E5E7EB',
            'tab_border_color_active' => $colors['primary'],
            'content_padding' => ['top' => 24, 'right' => 0, 'bottom' => 0, 'left' => 0],
            'content_font_size' => 16,
            'content_color' => $colors['text']
        ];
    }

    /**
     * Get divider styles
     */
    private static function getDividerStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        return [
            'divider_color' => $contextColors['divider_color'],
            'divider_style' => 'solid',
            'divider_weight' => 1,
            'divider_width' => 100,
            'divider_alignment' => 'center',
            'margin' => ['top' => 30, 'right' => 0, 'bottom' => 30, 'left' => 0]
        ];
    }

    /**
     * Get icon styles
     */
    private static function getIconStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        return [
            'icon_color' => $contextColors['icon_color'],
            'icon_font_size' => 48,
            'icon_alignment' => 'center',
            'use_circle' => false,
            'circle_color' => $colors['background_alt']
        ];
    }

    /**
     * Get social icons styles
     */
    private static function getSocialIconsStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        $isFooter = $context['is_footer'] ?? false;

        return [
            'icon_color' => $isFooter ? $contextColors['text_light'] : $colors['text_light'],
            'icon_color__hover' => $colors['primary'],
            'icon_font_size' => $isFooter ? 20 : 24,
            'icon_spacing' => 16,
            'icon_alignment' => $isFooter ? 'left' : 'center'
        ];
    }

    /**
     * Get gallery styles
     */
    private static function getGalleryStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $borderRadius = $style === 'modern' ? 12 : ($style === 'minimal' ? 0 : 8);

        return [
            'gallery_columns' => 3,
            'gallery_columns__tablet' => 2,
            'gallery_columns__phone' => 1,
            'gallery_gap' => 20,
            'image_border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'enable_lightbox' => true,
            'show_title' => false,
            'hover_overlay_color' => 'rgba(0,0,0,0.3)',
            'hover_icon_color' => '#FFFFFF'
        ];
    }

    /**
     * Get slider styles
     */
    private static function getSliderStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);

        return [
            'arrows_color' => '#FFFFFF',
            'arrows_background' => 'rgba(0,0,0,0.3)',
            'arrows_background__hover' => 'rgba(0,0,0,0.5)',
            'dots_color' => 'rgba(255,255,255,0.5)',
            'dots_color_active' => '#FFFFFF',
            'autoplay' => true,
            'autoplay_speed' => 5000,
            'transition_duration' => 500,
            'show_arrows' => true,
            'show_dots' => true,
            'infinite' => true
        ];
    }

    /**
     * Get video styles
     */
    private static function getVideoStyles(string $style, array $context): array
    {
        $borderRadius = $style === 'modern' ? 12 : ($style === 'minimal' ? 0 : 8);
        $shadows = JTB_AI_Styles::getShadowStyles($style);

        return [
            'border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'box_shadow_style' => $style === 'minimal' ? 'none' : 'preset2',
            'play_icon_color' => '#FFFFFF',
            'play_icon_background' => 'rgba(0,0,0,0.6)'
        ];
    }

    /**
     * Get contact form styles
     */
    private static function getContactFormStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);
        $borderRadius = $style === 'modern' ? 8 : ($style === 'minimal' ? 0 : 6);

        return [
            'label_font_family' => $typography['body_font'],
            'label_font_size' => 14,
            'label_font_weight' => '500',
            'label_color' => $colors['text'],
            'input_background' => '#FFFFFF',
            'input_border_color' => '#E5E7EB',
            'input_border_color__focus' => $colors['primary'],
            'input_border_width' => 1,
            'input_border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'input_padding' => ['top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 16],
            'input_font_size' => 16,
            'input_text_color' => $colors['text'],
            'placeholder_color' => $colors['text_light'],
            'field_spacing' => 20,
            'submit_button_alignment' => 'left'
        ];
    }

    /**
     * Get blog/portfolio styles
     */
    private static function getBlogStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);
        $borderRadius = $style === 'modern' ? 12 : ($style === 'minimal' ? 0 : 8);
        $shadows = JTB_AI_Styles::getShadowStyles($style);

        return [
            'columns' => 3,
            'columns__tablet' => 2,
            'columns__phone' => 1,
            'gap' => 30,
            'card_background' => $colors['background'],
            'card_border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'card_box_shadow' => $shadows['card'],
            'card_box_shadow__hover' => $shadows['elevated'],
            'card_padding' => ['top' => 0, 'right' => 0, 'bottom' => 24, 'left' => 0],
            'image_height' => 200,
            'title_font_family' => $typography['heading_font'],
            'title_font_size' => 20,
            'title_font_weight' => '600',
            'title_color' => $colors['heading'],
            'title_color__hover' => $colors['primary'],
            'meta_font_size' => 14,
            'meta_color' => $colors['text_light'],
            'excerpt_font_size' => 15,
            'excerpt_color' => $colors['text'],
            'show_featured_image' => true,
            'show_date' => true,
            'show_author' => true,
            'show_excerpt' => true,
            'excerpt_length' => 120
        ];
    }

    /**
     * Get map styles
     */
    private static function getMapStyles(string $style, array $context): array
    {
        $borderRadius = $style === 'modern' ? 12 : ($style === 'minimal' ? 0 : 8);

        return [
            'map_height' => 400,
            'map_height__tablet' => 350,
            'map_height__phone' => 300,
            'border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'grayscale' => $style === 'minimal',
            'zoom' => 14
        ];
    }

    /**
     * Get menu styles
     */
    private static function getMenuStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);
        $isHeader = $context['is_header'] ?? false;
        $isFooter = $context['is_footer'] ?? false;
        $visualContext = $context['visual_context'] ?? 'LIGHT';
        $contextColors = JTB_AI_Styles::getContextColors($visualContext, $style);

        $textColor = $isFooter ? $contextColors['text_light'] : $contextColors['text'];

        return [
            'menu_font_family' => $typography['body_font'],
            'menu_font_size' => $isFooter ? 14 : 15,
            'menu_font_weight' => '500',
            'menu_text_transform' => 'none',
            'menu_link_color' => $textColor,
            'menu_link_color__hover' => $colors['primary'],
            'menu_link_color_active' => $colors['primary'],
            'menu_link_padding' => ['top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 16],
            'menu_orientation' => $isHeader ? 'horizontal' : ($isFooter ? 'vertical' : 'horizontal'),
            'dropdown_background' => '#FFFFFF',
            'dropdown_text_color' => $colors['text'],
            'dropdown_text_color__hover' => $colors['primary'],
            'dropdown_border_radius' => 8,
            'dropdown_box_shadow' => '0 10px 40px rgba(0,0,0,0.1)'
        ];
    }

    /**
     * Get logo styles
     */
    private static function getLogoStyles(string $style, array $context): array
    {
        return [
            'logo_max_height' => 50,
            'logo_max_height__tablet' => 45,
            'logo_max_height__phone' => 40
        ];
    }

    /**
     * Get breadcrumbs styles
     */
    private static function getBreadcrumbsStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);

        return [
            'breadcrumb_font_family' => $typography['body_font'],
            'breadcrumb_font_size' => 14,
            'breadcrumb_color' => $colors['text_light'],
            'breadcrumb_link_color' => $colors['text'],
            'breadcrumb_link_color__hover' => $colors['primary'],
            'breadcrumb_separator' => '/',
            'breadcrumb_separator_color' => $colors['text_light']
        ];
    }

    /**
     * Get search form styles
     */
    private static function getSearchFormStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $borderRadius = $style === 'modern' ? 8 : ($style === 'minimal' ? 0 : 6);

        return [
            'input_background' => '#FFFFFF',
            'input_border_color' => '#E5E7EB',
            'input_border_color__focus' => $colors['primary'],
            'input_border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ],
            'input_padding' => ['top' => 12, 'right' => 16, 'bottom' => 12, 'left' => 40],
            'icon_color' => $colors['text_light'],
            'button_background' => $colors['primary'],
            'button_color' => '#FFFFFF'
        ];
    }

    /**
     * Get footer info/copyright styles
     */
    private static function getFooterInfoStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);

        return [
            'font_family' => $typography['body_font'],
            'font_size' => 14,
            'text_color' => 'rgba(255,255,255,0.6)',
            'link_color' => 'rgba(255,255,255,0.8)',
            'link_color__hover' => '#FFFFFF',
            'text_align' => 'center'
        ];
    }

    /**
     * Get generic module styles (fallback)
     */
    private static function getGenericModuleStyles(string $style, array $context): array
    {
        $colors = JTB_AI_Styles::getColorPalette($style);
        $typography = JTB_AI_Styles::getTypography($style);

        return [
            'font_family' => $typography['body_font'],
            'font_size' => $typography['body_size'],
            'text_color' => $colors['text']
        ];
    }

    /**
     * Check if a section needs creative AI treatment
     */
    private static function needsCreativeTreatment(string $blueprint, array $context): bool
    {
        // Only apply creative treatment to specific section types
        if (!in_array($blueprint, self::CREATIVE_SECTIONS)) {
            return false;
        }

        // Only apply if mockup had special visual treatment
        $mockupStructure = $context['mockup_structure'] ?? [];
        // Could check for mockup hints about gradients, overlays, etc.

        return true;
    }

    /**
     * Apply AI creative overrides for special sections
     * This is the 10% of styling that uses AI calls
     */
    private static function applyCreativeOverrides(array $creativeSections, array $baseStyles, array $context): array
    {
        $overrides = [];
        $tokensUsed = 0;

        $style = $context['style'] ?? 'modern';
        $industry = $context['industry'] ?? 'general';
        $colors = $context['colors'] ?? [];

        // For now, apply PHP-based creative variations
        // AI calls can be added later for truly unique designs

        foreach ($creativeSections as $path => $info) {
            $blueprint = $info['blueprint'];
            $moduleType = $info['module_type'];
            $moduleContext = $info['context'];

            // Hero section enhancements
            if ($blueprint === 'hero' || $blueprint === 'fullwidth_hero') {
                // Apply gradient based on style and industry
                $heroOverrides = self::generateHeroCreativeStyles($style, $industry, $colors, $moduleContext);
                if (!empty($heroOverrides)) {
                    $overrides[$path] = array_merge($baseStyles[$path] ?? [], $heroOverrides);
                }
            }

            // CTA section enhancements
            if (in_array($blueprint, ['cta', 'call_to_action', 'final_cta'])) {
                $ctaOverrides = self::generateCTACreativeStyles($style, $industry, $colors, $moduleContext);
                if (!empty($ctaOverrides)) {
                    $overrides[$path] = array_merge($baseStyles[$path] ?? [], $ctaOverrides);
                }
            }

            // Stats/Counters section enhancements
            if (in_array($blueprint, ['stats', 'counters'])) {
                $statsOverrides = self::generateStatsCreativeStyles($style, $industry, $colors, $moduleContext);
                if (!empty($statsOverrides)) {
                    $overrides[$path] = array_merge($baseStyles[$path] ?? [], $statsOverrides);
                }
            }
        }

        return [
            'styles' => $overrides,
            'tokens_used' => $tokensUsed
        ];
    }

    /**
     * Generate creative styles for hero section
     */
    /**
     * Generate creative styles for hero section
     * Now industry-aware for different visual treatments
     */
    private static function generateHeroCreativeStyles(string $style, string $industry, array $colors, array $context): array
    {
        $primary = $colors['primary'] ?? '#3B82F6';
        $secondary = $colors['secondary'] ?? '#1E40AF';
        $dark = $colors['dark'] ?? '#111827';
        $accent = $colors['accent'] ?? '#F59E0B';

        // Industry-specific gradient directions and effects
        $industryEffects = [
            'technology' => [
                'gradient_angle' => 135,
                'use_radial' => false,
                'overlay' => 'rgba(0,0,0,0.1)',
                'padding_top' => 140
            ],
            'healthcare' => [
                'gradient_angle' => 180,
                'use_radial' => false,
                'overlay' => null,
                'padding_top' => 120
            ],
            'restaurant' => [
                'gradient_angle' => 0,
                'use_radial' => true,
                'overlay' => 'rgba(0,0,0,0.5)',
                'padding_top' => 200  // More dramatic for restaurant hero
            ],
            'ecommerce' => [
                'gradient_angle' => 135,
                'use_radial' => false,
                'overlay' => null,
                'padding_top' => 100
            ],
            'realestate' => [
                'gradient_angle' => 180,
                'use_radial' => false,
                'overlay' => 'rgba(0,0,0,0.4)',
                'padding_top' => 180
            ],
            'legal' => [
                'gradient_angle' => 180,
                'use_radial' => false,
                'overlay' => null,
                'padding_top' => 120
            ],
            'education' => [
                'gradient_angle' => 135,
                'use_radial' => false,
                'overlay' => null,
                'padding_top' => 120
            ],
            'fitness' => [
                'gradient_angle' => 45,
                'use_radial' => false,
                'overlay' => 'rgba(0,0,0,0.6)',
                'padding_top' => 200  // Full-screen hero for fitness
            ],
            'agency' => [
                'gradient_angle' => 135,
                'use_radial' => true,
                'overlay' => null,
                'padding_top' => 160
            ],
            'nonprofit' => [
                'gradient_angle' => 180,
                'use_radial' => false,
                'overlay' => 'rgba(0,0,0,0.3)',
                'padding_top' => 140
            ]
        ];

        // Get industry effect or default
        $effect = $industryEffects[strtolower($industry)] ?? [
            'gradient_angle' => 135,
            'use_radial' => false,
            'overlay' => null,
            'padding_top' => 140
        ];

        // Style-based gradients
        $gradients = [
            'modern' => "linear-gradient({$effect['gradient_angle']}deg, {$primary} 0%, {$secondary} 100%)",
            'bold' => "linear-gradient({$effect['gradient_angle']}deg, {$dark} 0%, {$primary} 100%)",
            'minimal' => "linear-gradient(180deg, #F9FAFB 0%, #FFFFFF 100%)",
            'elegant' => "linear-gradient({$effect['gradient_angle']}deg, {$dark} 0%, {$secondary} 100%)",
            'playful' => "linear-gradient({$effect['gradient_angle']}deg, {$primary} 0%, {$secondary} 50%, {$accent} 100%)",
            'corporate' => "linear-gradient({$effect['gradient_angle']}deg, {$secondary} 0%, {$dark} 100%)",
            'dark' => "linear-gradient({$effect['gradient_angle']}deg, #0f172a 0%, #1e293b 100%)"
        ];

        $attrs = [];

        // Apply gradient for non-minimal styles
        if ($style !== 'minimal') {
            if ($effect['use_radial']) {
                $attrs['background_gradient'] = "radial-gradient(circle at 30% 50%, {$primary} 0%, {$secondary} 100%)";
            } else {
                $attrs['background_gradient'] = $gradients[$style] ?? $gradients['modern'];
            }
            $attrs['use_background_gradient'] = true;
        }

        // Add overlay based on industry and style
        if ($effect['overlay']) {
            $attrs['background_overlay'] = $effect['overlay'];
        } elseif ($style === 'bold' || $style === 'elegant') {
            $attrs['background_overlay'] = 'rgba(0,0,0,0.4)';
        }

        // Industry-specific padding
        $paddingTop = $effect['padding_top'];
        $attrs['padding'] = ['top' => $paddingTop, 'right' => 0, 'bottom' => $paddingTop, 'left' => 0];
        $attrs['padding__tablet'] = ['top' => (int)($paddingTop * 0.75), 'right' => 0, 'bottom' => (int)($paddingTop * 0.75), 'left' => 0];
        $attrs['padding__phone'] = ['top' => (int)($paddingTop * 0.5), 'right' => 20, 'bottom' => (int)($paddingTop * 0.5), 'left' => 20];

        return $attrs;
    }

    /**
     * Generate creative styles for CTA section
     * Now industry-aware
     */
    private static function generateCTACreativeStyles(string $style, string $industry, array $colors, array $context): array
    {
        $primary = $colors['primary'] ?? '#3B82F6';
        $secondary = $colors['secondary'] ?? '#1E40AF';
        $accent = $colors['accent'] ?? '#F59E0B';
        $dark = $colors['dark'] ?? '#111827';

        // Industry-specific CTA treatments
        $industryCTA = [
            'technology' => ['angle' => 135, 'use_accent' => false],
            'healthcare' => ['angle' => 90, 'use_accent' => false],
            'restaurant' => ['angle' => 45, 'use_accent' => true],
            'ecommerce' => ['angle' => 135, 'use_accent' => true],
            'realestate' => ['angle' => 180, 'use_accent' => false],
            'legal' => ['angle' => 180, 'use_accent' => false],
            'education' => ['angle' => 135, 'use_accent' => true],
            'fitness' => ['angle' => 45, 'use_accent' => true],
            'agency' => ['angle' => 135, 'use_accent' => false],
            'nonprofit' => ['angle' => 180, 'use_accent' => true]
        ];

        $ctaStyle = $industryCTA[strtolower($industry)] ?? ['angle' => 135, 'use_accent' => false];

        // Build gradient based on industry preference
        if ($ctaStyle['use_accent']) {
            $gradient = "linear-gradient({$ctaStyle['angle']}deg, {$accent} 0%, {$primary} 100%)";
        } else {
            $gradient = "linear-gradient({$ctaStyle['angle']}deg, {$primary} 0%, {$secondary} 100%)";
        }

        // Style overrides
        if ($style === 'bold' || $style === 'playful') {
            $gradient = "linear-gradient({$ctaStyle['angle']}deg, {$accent} 0%, {$primary} 100%)";
        } elseif ($style === 'elegant' || $style === 'corporate') {
            $gradient = "linear-gradient({$ctaStyle['angle']}deg, {$dark} 0%, {$secondary} 100%)";
        } elseif ($style === 'dark') {
            $gradient = "linear-gradient({$ctaStyle['angle']}deg, {$primary} 0%, #0f172a 100%)";
        }

        $attrs = [
            'background_gradient' => $gradient,
            'use_background_gradient' => true,
            'padding' => ['top' => 100, 'right' => 0, 'bottom' => 100, 'left' => 0],
            'padding__tablet' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'padding__phone' => ['top' => 60, 'right' => 20, 'bottom' => 60, 'left' => 20]
        ];

        return $attrs;
    }

    /**
     * Generate creative styles for stats section
     * Now industry-aware
     */
    private static function generateStatsCreativeStyles(string $style, string $industry, array $colors, array $context): array
    {
        $primary = $colors['primary'] ?? '#3B82F6';
        $secondary = $colors['secondary'] ?? '#1E40AF';
        $dark = $colors['dark'] ?? '#111827';
        $accent = $colors['accent'] ?? '#F59E0B';

        // Industry-specific stats backgrounds
        $industryStats = [
            'technology' => ['gradient' => "linear-gradient(135deg, {$secondary} 0%, {$dark} 100%)", 'padding' => 80],
            'healthcare' => ['gradient' => "linear-gradient(180deg, {$secondary} 0%, {$primary} 100%)", 'padding' => 60],
            'restaurant' => ['gradient' => "linear-gradient(135deg, {$dark} 0%, {$secondary} 100%)", 'padding' => 60],
            'ecommerce' => ['gradient' => "linear-gradient(135deg, {$primary} 0%, {$accent} 100%)", 'padding' => 60],
            'realestate' => ['gradient' => "linear-gradient(180deg, {$dark} 0%, {$secondary} 100%)", 'padding' => 80],
            'legal' => ['gradient' => "linear-gradient(180deg, {$secondary} 0%, {$dark} 100%)", 'padding' => 60],
            'education' => ['gradient' => "linear-gradient(135deg, {$primary} 0%, {$secondary} 100%)", 'padding' => 60],
            'fitness' => ['gradient' => "linear-gradient(45deg, {$dark} 0%, {$primary} 100%)", 'padding' => 80],
            'agency' => ['gradient' => "linear-gradient(135deg, {$dark} 0%, {$primary} 100%)", 'padding' => 80],
            'nonprofit' => ['gradient' => "linear-gradient(180deg, {$primary} 0%, {$secondary} 100%)", 'padding' => 60]
        ];

        $statsStyle = $industryStats[strtolower($industry)] ?? [
            'gradient' => "linear-gradient(135deg, {$secondary} 0%, {$dark} 100%)",
            'padding' => 80
        ];

        // Style overrides
        if ($style === 'minimal') {
            $statsStyle['gradient'] = "linear-gradient(180deg, #F3F4F6 0%, #E5E7EB 100%)";
        } elseif ($style === 'dark') {
            $statsStyle['gradient'] = "linear-gradient(135deg, #1e293b 0%, #0f172a 100%)";
        }

        $attrs = [
            'background_gradient' => $statsStyle['gradient'],
            'use_background_gradient' => true,
            'padding' => ['top' => $statsStyle['padding'], 'right' => 0, 'bottom' => $statsStyle['padding'], 'left' => 0]
        ];

        return $attrs;
    }
    /**
     * Apply visual rhythm adjustments (spacing between sections)
     * Based on AutoFix Stage 13
     */
    private static function applyVisualRhythm(array $styles, array $sectionMetadata, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $spacing = JTB_AI_Styles::getSpacing($style);

        $prevSection = null;

        foreach ($sectionMetadata as $path => $meta) {
            if (!isset($styles[$path])) {
                continue;
            }

            $currentBlueprint = $meta['blueprint'];
            $prevBlueprint = $prevSection ? $sectionMetadata[$prevSection]['blueprint'] ?? null : null;

            // Adjust spacing based on visual flow
            $topSpacing = self::calculateTopSpacing($currentBlueprint, $prevBlueprint, $spacing);

            if ($topSpacing !== null && isset($styles[$path]['padding'])) {
                $styles[$path]['padding']['top'] = $topSpacing;
            }

            $prevSection = $path;
        }

        return $styles;
    }

    /**
     * Calculate top spacing based on section transition
     */
    private static function calculateTopSpacing(?string $current, ?string $previous, array $spacing): ?int
    {
        // First section - standard padding
        if ($previous === null) {
            return null;
        }

        // After hero - larger gap
        if ($previous === 'hero' || $previous === 'fullwidth_hero') {
            return 120;
        }

        // Before CTA - larger gap for emphasis
        if ($current === 'cta' || $current === 'final_cta') {
            return 140;
        }

        // Default - standard section padding
        return null;
    }

    /**
     * Apply section background alternation for visual rhythm
     */
    private static function applySectionBackgrounds(array $styles, array $sectionMetadata, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $colors = JTB_AI_Styles::getColorPalette($style);

        $sectionIndex = 0;
        $prevHadAltBg = false;

        foreach ($sectionMetadata as $path => $meta) {
            // Initialize styles for this section path if not exists
            if (!isset($styles[$path])) {
                $styles[$path] = [];
            }

            $blueprint = $meta['blueprint'];

            // Creative sections get special treatment (hero, cta, stats have gradients/images)
            if (in_array($blueprint, self::CREATIVE_SECTIONS)) {
                // Ensure creative sections have background_type set
                if (!isset($styles[$path]['background_type'])) {
                    if (in_array($blueprint, ['hero', 'fullwidth_hero', 'cta', 'call_to_action'])) {
                        $styles[$path]['background_type'] = 'gradient';
                        $styles[$path]['background_gradient_type'] = 'linear';
                        $styles[$path]['background_gradient_direction'] = 135;
                        $styles[$path]['background_gradient_start'] = $colors['primary'] ?? '#6366f1';
                        $styles[$path]['background_gradient_end'] = $colors['secondary'] ?? '#8b5cf6';
                    } elseif (in_array($blueprint, ['stats', 'statistics', 'numbers'])) {
                        $styles[$path]['background_type'] = 'color';
                        $styles[$path]['background_color'] = $colors['dark'] ?? '#0f172a';
                    }
                }
                $prevHadAltBg = false;
                $sectionIndex++;
                continue;
            }

            // Regular sections: alternate backgrounds for visual separation
            $styles[$path]['background_type'] = 'color';
            if (!$prevHadAltBg && $sectionIndex > 0) {
                $styles[$path]['background_color'] = $colors['background_alt'] ?? '#F9FAFB';
                $prevHadAltBg = true;
            } else {
                $styles[$path]['background_color'] = $colors['background'] ?? '#FFFFFF';
                $prevHadAltBg = false;
            }

            $sectionIndex++;
        }

        return $styles;
    }

    /**
     * Collect section metadata from skeleton for context
     */
    private static function collectSectionMetadata(array $skeleton, array $pathMap): array
    {
        $metadata = [];

        // Collect from pages
        foreach ($skeleton['pages'] ?? [] as $pageName => $pageData) {
            foreach ($pageData['sections'] ?? [] as $idx => $section) {
                // Look for pattern in attrs (set by Architect) or fallback to legacy locations
                $blueprint = $section['attrs']['_pattern'] 
                    ?? $section['_blueprint'] 
                    ?? $section['_pattern'] 
                    ?? 'generic';
                $path = $section['_path'] ?? "{$pageName}/{$blueprint}";

                $metadata[$path] = [
                    'blueprint' => $blueprint,
                    'page' => $pageName,
                    'index' => $idx,
                    'type' => 'section'
                ];
            }
        }

        // Add header/footer
        if (!empty($skeleton['header']['sections'])) {
            $metadata['header'] = [
                'blueprint' => 'header',
                'page' => null,
                'index' => 0,
                'type' => 'section'
            ];
        }

        if (!empty($skeleton['footer']['sections'])) {
            $metadata['footer'] = [
                'blueprint' => 'footer',
                'page' => null,
                'index' => 0,
                'type' => 'section'
            ];
        }

        return $metadata;
    }

    /**
     * Parse path to extract module type and context
     */
    private static function parsePath(string $path): ?array
    {
        // Path format: "page/blueprint/colN/moduleType_index" or "region/colN/moduleType_index"
        $parts = explode('/', $path);

        if (count($parts) < 2) {
            return null;
        }

        $result = [
            'path' => $path,
            'module_type' => null,
            'blueprint' => null,
            'region' => null,
            'page' => null,
            'section_index' => 0
        ];

        // Check if it's a region path (header, footer)
        if (in_array($parts[0], ['header', 'footer'])) {
            $result['region'] = $parts[0];
            // header/colN/moduleType_index
            if (count($parts) >= 3) {
                $moduleStr = end($parts);
                $result['module_type'] = self::extractModuleType($moduleStr);
            }
        } else {
            // page/blueprint/colN/moduleType_index
            $result['page'] = $parts[0];
            if (isset($parts[1])) {
                $result['blueprint'] = $parts[1];
            }
            if (count($parts) >= 4) {
                $moduleStr = end($parts);
                $result['module_type'] = self::extractModuleType($moduleStr);
            }
        }

        return $result;
    }

    /**
     * Extract module type from path segment (e.g., "heading_0" -> "heading")
     */
    private static function extractModuleType(string $segment): string
    {
        // Remove index suffix
        $moduleType = preg_replace('/_\d+$/', '', $segment);
        return self::normalizeModuleSlug($moduleType);
    }

    /**
     * Normalize module slug
     */
    private static function normalizeModuleSlug(string $slug): string
    {
        // Convert hyphens to underscores
        $slug = str_replace('-', '_', $slug);
        // Lowercase
        $slug = strtolower(trim($slug));
        return $slug;
    }
}
