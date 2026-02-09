<?php
/**
 * JTB AI Styles
 * Professional style presets for AI-generated layouts
 * Provides cohesive design attributes based on style and industry
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Styles
{
    // ========================================
    // Color Palettes by Style
    // ========================================

    private static array $colorPalettes = [
        'modern' => [
            'primary' => '#3B82F6',      // Blue
            'secondary' => '#1E40AF',    // Dark blue
            'accent' => '#F59E0B',       // Amber
            'text' => '#1F2937',         // Dark gray
            'text_light' => '#6B7280',   // Gray
            'background' => '#FFFFFF',
            'background_alt' => '#F9FAFB',
            'heading' => '#111827'
        ],
        'minimal' => [
            'primary' => '#18181B',      // Near black
            'secondary' => '#3F3F46',    // Dark gray
            'accent' => '#EF4444',       // Red accent
            'text' => '#27272A',
            'text_light' => '#71717A',
            'background' => '#FFFFFF',
            'background_alt' => '#FAFAFA',
            'heading' => '#09090B'
        ],
        'bold' => [
            'primary' => '#7C3AED',      // Violet
            'secondary' => '#4C1D95',    // Dark violet
            'accent' => '#F97316',       // Orange
            'text' => '#1E1B4B',
            'text_light' => '#6B7280',
            'background' => '#FFFFFF',
            'background_alt' => '#F5F3FF',
            'heading' => '#1E1B4B'
        ],
        'elegant' => [
            'primary' => '#78716C',      // Stone
            'secondary' => '#44403C',    // Dark stone
            'accent' => '#B45309',       // Amber dark
            'text' => '#292524',
            'text_light' => '#78716C',
            'background' => '#FAFAF9',
            'background_alt' => '#F5F5F4',
            'heading' => '#1C1917'
        ],
        'playful' => [
            'primary' => '#EC4899',      // Pink
            'secondary' => '#8B5CF6',    // Purple
            'accent' => '#06B6D4',       // Cyan
            'text' => '#1F2937',
            'text_light' => '#6B7280',
            'background' => '#FFFFFF',
            'background_alt' => '#FDF2F8',
            'heading' => '#831843'
        ],
        'corporate' => [
            'primary' => '#1E40AF',      // Blue
            'secondary' => '#1E3A8A',    // Darker blue
            'accent' => '#059669',       // Emerald
            'text' => '#1F2937',
            'text_light' => '#6B7280',
            'background' => '#FFFFFF',
            'background_alt' => '#EFF6FF',
            'heading' => '#111827'
        ],
        'dark' => [
            'primary' => '#60A5FA',      // Light blue
            'secondary' => '#93C5FD',    // Lighter blue
            'accent' => '#FBBF24',       // Yellow
            'text' => '#E5E7EB',
            'text_light' => '#9CA3AF',
            'background' => '#111827',
            'background_alt' => '#1F2937',
            'heading' => '#F9FAFB'
        ]
    ];

    // ========================================
    // Typography Presets
    // ========================================

    private static array $typographyPresets = [
        'modern' => [
            'heading_font' => 'Inter',
            'body_font' => 'Inter',
            'h1_size' => 56,
            'h2_size' => 42,
            'h3_size' => 32,
            'body_size' => 18,
            'heading_weight' => '700',
            'body_weight' => '400',
            'line_height' => 1.6,
            'heading_line_height' => 1.2,
            'letter_spacing' => '-0.02em'
        ],
        'minimal' => [
            'heading_font' => 'Helvetica Neue',
            'body_font' => 'Helvetica Neue',
            'h1_size' => 48,
            'h2_size' => 36,
            'h3_size' => 28,
            'body_size' => 16,
            'heading_weight' => '600',
            'body_weight' => '400',
            'line_height' => 1.7,
            'heading_line_height' => 1.3,
            'letter_spacing' => '0'
        ],
        'bold' => [
            'heading_font' => 'Poppins',
            'body_font' => 'Poppins',
            'h1_size' => 64,
            'h2_size' => 48,
            'h3_size' => 36,
            'body_size' => 18,
            'heading_weight' => '800',
            'body_weight' => '400',
            'line_height' => 1.6,
            'heading_line_height' => 1.1,
            'letter_spacing' => '-0.03em'
        ],
        'elegant' => [
            'heading_font' => 'Playfair Display',
            'body_font' => 'Source Sans Pro',
            'h1_size' => 52,
            'h2_size' => 40,
            'h3_size' => 30,
            'body_size' => 17,
            'heading_weight' => '600',
            'body_weight' => '400',
            'line_height' => 1.7,
            'heading_line_height' => 1.3,
            'letter_spacing' => '0'
        ],
        'playful' => [
            'heading_font' => 'Nunito',
            'body_font' => 'Nunito',
            'h1_size' => 52,
            'h2_size' => 38,
            'h3_size' => 28,
            'body_size' => 18,
            'heading_weight' => '700',
            'body_weight' => '400',
            'line_height' => 1.6,
            'heading_line_height' => 1.25,
            'letter_spacing' => '0'
        ],
        'corporate' => [
            'heading_font' => 'Roboto',
            'body_font' => 'Roboto',
            'h1_size' => 48,
            'h2_size' => 36,
            'h3_size' => 28,
            'body_size' => 16,
            'heading_weight' => '700',
            'body_weight' => '400',
            'line_height' => 1.65,
            'heading_line_height' => 1.25,
            'letter_spacing' => '0'
        ]
    ];

    // ========================================
    // Spacing Presets
    // ========================================

    private static array $spacingPresets = [
        'modern' => [
            'section_padding' => '100px',
            'section_padding_mobile' => '60px',
            'row_gap' => '40px',
            'column_gap' => '30px',
            'module_margin' => '30px',
            'content_padding' => '40px'
        ],
        'minimal' => [
            'section_padding' => '120px',
            'section_padding_mobile' => '80px',
            'row_gap' => '60px',
            'column_gap' => '40px',
            'module_margin' => '40px',
            'content_padding' => '60px'
        ],
        'bold' => [
            'section_padding' => '80px',
            'section_padding_mobile' => '50px',
            'row_gap' => '30px',
            'column_gap' => '25px',
            'module_margin' => '25px',
            'content_padding' => '35px'
        ],
        'elegant' => [
            'section_padding' => '110px',
            'section_padding_mobile' => '70px',
            'row_gap' => '50px',
            'column_gap' => '35px',
            'module_margin' => '35px',
            'content_padding' => '50px'
        ],
        'compact' => [
            'section_padding' => '60px',
            'section_padding_mobile' => '40px',
            'row_gap' => '25px',
            'column_gap' => '20px',
            'module_margin' => '20px',
            'content_padding' => '25px'
        ]
    ];

    // ========================================
    // Main Style Application Methods
    // ========================================

    /**
     * Get complete style preset
     * @param string $style Style name
     * @param array $context Additional context
     * @return array Style configuration
     */
    public static function getStylePreset(string $style, array $context = []): array
    {
        $style = strtolower($style);
        if (!isset(self::$colorPalettes[$style])) {
            $style = 'modern';
        }

        return [
            'colors' => self::getColorPalette($style),
            'typography' => self::getTypography($style),
            'spacing' => self::getSpacing($style),
            'buttons' => self::getButtonStyles($style),
            'shadows' => self::getShadowStyles($style),
            'borders' => self::getBorderStyles($style),
            'animations' => self::getAnimationPreset($style)
        ];
    }

    /**
     * Get color palette
     */
    public static function getColorPalette(string $style): array
    {
        return self::$colorPalettes[$style] ?? self::$colorPalettes['modern'];
    }

    /**
     * Get typography settings
     */
    public static function getTypography(string $style): array
    {
        return self::$typographyPresets[$style] ?? self::$typographyPresets['modern'];
    }

    /**
     * Get spacing settings
     */
    public static function getSpacing(string $style): array
    {
        return self::$spacingPresets[$style] ?? self::$spacingPresets['modern'];
    }

    // ========================================
    // Module-Specific Styles
    // ========================================

    /**
     * Get visual context for a section type
     * Used by AutoFix system (Stages 11-17) to determine:
     * - LIGHT: Standard light background (most sections)
     * - DARK: Dark background (hero, trust_metrics, stats)
     * - PRIMARY: Branded/CTA background (final_cta, cta)
     *
     * @param string $sectionType Section type
     * @return string Visual context (LIGHT, DARK, PRIMARY)
     */
    public static function getVisualContextForSection(string $sectionType): string
    {
        // PRIMARY context - Call to action sections
        $primarySections = ['cta', 'call_to_action', 'final_cta'];
        if (in_array($sectionType, $primarySections)) {
            return 'PRIMARY';
        }

        // DARK context - Hero and trust-building sections
        $darkSections = ['hero', 'fullwidth_hero', 'stats', 'counters', 'trust_metrics', 'footer'];
        if (in_array($sectionType, $darkSections)) {
            return 'DARK';
        }

        // All other sections are LIGHT
        return 'LIGHT';
    }

    /**
     * Get section attributes with style applied
     * Maps to section module fields:
     * - padding (spacing type with top/right/bottom/left)
     * - background_color (via Design tab - background group)
     * - background_gradient, use_background_gradient (gradient background)
     * - min_height (range)
     * - inner_width (range)
     */
    public static function getSectionAttrs(string $sectionType, string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $spacing = self::getSpacing($style);
        $sectionIndex = $context['section_index'] ?? 0;

        // Parse spacing values to get numeric
        $sectionPadding = (int)$spacing['section_padding'];
        $sectionPaddingMobile = (int)$spacing['section_padding_mobile'];

        // Determine visual context based on section type
        $visualContext = self::getVisualContextForSection($sectionType);

        // Base section attrs with proper spacing format
        $attrs = [
            // CRITICAL: Pattern identification for AutoFix system (Stages 11-17)
            '_pattern' => $sectionType,
            // Visual context: LIGHT, DARK, or PRIMARY
            '_visual_context' => $visualContext,
            'visual_context' => $visualContext,
            // Section type for identification in preview
            'section_type' => $sectionType,
            // Padding uses spacing type: { top, right, bottom, left }
            'padding' => [
                'top' => $sectionPadding,
                'right' => 0,
                'bottom' => $sectionPadding,
                'left' => 0
            ],
            // Responsive padding for tablet
            'padding__tablet' => [
                'top' => $sectionPaddingMobile,
                'right' => 0,
                'bottom' => $sectionPaddingMobile,
                'left' => 0
            ],
            // Responsive padding for phone
            'padding__phone' => [
                'top' => (int)($sectionPaddingMobile * 0.7),
                'right' => 20,
                'bottom' => (int)($sectionPaddingMobile * 0.7),
                'left' => 20
            ],
            // Content width
            'inner_width' => 1200
        ];

        // Apply section-specific styles
        switch ($sectionType) {
            case 'hero':
            case 'fullwidth_hero':
                // Hero gets larger padding and optional gradient
                $attrs['min_height'] = 600;
                $attrs['min_height__tablet'] = 450;
                $attrs['min_height__phone'] = 350;
                $attrs['padding'] = [
                    'top' => 140,
                    'right' => 0,
                    'bottom' => 140,
                    'left' => 0
                ];
                $attrs['padding__tablet'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                $attrs['padding__phone'] = [
                    'top' => 60,
                    'right' => 20,
                    'bottom' => 60,
                    'left' => 20
                ];

                // Hero gradient background based on style
                if ($style === 'dark') {
                    $attrs['background_gradient'] = 'linear-gradient(135deg, #1F2937 0%, #111827 100%)';
                    $attrs['use_background_gradient'] = true;
                } elseif ($style === 'bold') {
                    $attrs['background_gradient'] = 'linear-gradient(135deg, ' .
                        ($colors['primary'] ?? '#7C3AED') . ' 0%, ' .
                        ($colors['secondary'] ?? '#4C1D95') . ' 100%)';
                    $attrs['use_background_gradient'] = true;
                } else {
                    $attrs['background_color'] = $colors['background_alt'] ?? '#F9FAFB';
                }
                break;

            case 'cta':
            case 'call_to_action':
                // CTA always gets gradient background for emphasis
                $attrs['background_gradient'] = 'linear-gradient(135deg, ' .
                    ($colors['primary'] ?? '#3B82F6') . ' 0%, ' .
                    ($colors['secondary'] ?? '#1E40AF') . ' 100%)';
                $attrs['use_background_gradient'] = true;
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                $attrs['padding__tablet'] = [
                    'top' => 70,
                    'right' => 0,
                    'bottom' => 70,
                    'left' => 0
                ];
                $attrs['padding__phone'] = [
                    'top' => 50,
                    'right' => 20,
                    'bottom' => 50,
                    'left' => 20
                ];
                break;

            case 'stats':
            case 'counters':
                // Stats section with dark/branded background
                if ($style === 'dark') {
                    $attrs['background_color'] = '#374151';
                } else {
                    $attrs['background_gradient'] = 'linear-gradient(135deg, ' .
                        ($colors['secondary'] ?? '#1E40AF') . ' 0%, ' .
                        ($colors['primary'] ?? '#3B82F6') . ' 100%)';
                    $attrs['use_background_gradient'] = true;
                }
                $attrs['padding'] = [
                    'top' => 80,
                    'right' => 0,
                    'bottom' => 80,
                    'left' => 0
                ];
                break;

            case 'testimonials':
            case 'reviews':
                $attrs['background_color'] = $colors['background_alt'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'pricing':
                $attrs['background_color'] = $colors['background_alt'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'features':
            case 'benefits':
                $attrs['background_color'] = $colors['background'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'faq':
                $attrs['background_color'] = $colors['background'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'contact':
            case 'contact_form':
                $attrs['background_color'] = $colors['background_alt'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'team':
                $attrs['background_color'] = $colors['background'];
                $attrs['padding'] = [
                    'top' => 100,
                    'right' => 0,
                    'bottom' => 100,
                    'left' => 0
                ];
                break;

            case 'footer':
                $attrs['background_color'] = '#111827';
                $attrs['padding'] = [
                    'top' => 80,
                    'right' => 0,
                    'bottom' => 40,
                    'left' => 0
                ];
                break;

            default:
                // Alternate backgrounds for visual rhythm
                if ($sectionIndex % 2 === 1) {
                    $attrs['background_color'] = $colors['background_alt'];
                } else {
                    $attrs['background_color'] = $colors['background'];
                }
                break;
        }

        return $attrs;
    }

    /**
     * Get row attributes with proper spacing
     * @param string $style Style name
     * @param array $context Context data
     * @return array Row attributes
     */
    public static function getRowAttrs(string $style, array $context = []): array
    {
        $spacing = self::getSpacing($style);
        $columnGap = (int)$spacing['column_gap'];
        $rowGap = (int)$spacing['row_gap'];

        return [
            'column_gap' => $columnGap,
            'column_gap__tablet' => (int)($columnGap * 0.75),
            'column_gap__phone' => (int)($columnGap * 0.5),
            'row_gap' => $rowGap,
            'row_gap__tablet' => (int)($rowGap * 0.75),
            'row_gap__phone' => (int)($rowGap * 0.5),
            'max_width' => 1200,
            'vertical_align' => 'center',
            'margin' => [
                'top' => 0,
                'right' => 'auto',
                'bottom' => $rowGap,
                'left' => 'auto'
            ]
        ];
    }

    /**
     * Get column attributes with optional card styling
     * @param string $style Style name
     * @param array $context Context data (isCard, width)
     * @return array Column attributes
     */
    public static function getColumnAttrs(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $shadows = self::getShadowStyles($style);
        $isCard = $context['is_card'] ?? false;
        $width = $context['width'] ?? '100%';

        $attrs = [
            'width' => $width,
            'vertical_align' => 'top',
            'padding' => [
                'top' => 0,
                'right' => 0,
                'bottom' => 0,
                'left' => 0
            ]
        ];

        // Card-style columns (for blurbs, testimonials, pricing, etc.)
        if ($isCard) {
            $attrs['background_color'] = $colors['background'];
            $attrs['padding'] = [
                'top' => 32,
                'right' => 24,
                'bottom' => 32,
                'left' => 24
            ];
            $attrs['border_radius'] = [
                'top_left' => 12,
                'top_right' => 12,
                'bottom_right' => 12,
                'bottom_left' => 12
            ];
            $attrs['box_shadow'] = $shadows['card'];
            $attrs['box_shadow__hover'] = $shadows['elevated'];
            $attrs['transform__hover'] = 'translateY(-4px)';
        }

        return $attrs;
    }

    /**
     * Get heading module styles
     * Maps to heading module Design tab fields:
     * - font_family, font_size, font_weight, font_style (typography group)
     * - line_height, letter_spacing, text_color, text_align (typography group)
     * Spacing is in separate group (margin/padding)
     */
    public static function getHeadingStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $typography = self::getTypography($style);
        $level = $context['level'] ?? 'h2';

        // Size map - return numeric values for range fields
        $sizeMap = [
            'h1' => 56,
            'h2' => 42,
            'h3' => 32,
            'h4' => 24,
            'h5' => 20,
            'h6' => 18
        ];

        // Override with typography preset if available
        if (isset($typography['h1_size'])) {
            $sizeMap['h1'] = (int)$typography['h1_size'];
        }
        if (isset($typography['h2_size'])) {
            $sizeMap['h2'] = (int)$typography['h2_size'];
        }
        if (isset($typography['h3_size'])) {
            $sizeMap['h3'] = (int)$typography['h3_size'];
        }

        return [
            // Typography group fields (Design tab)
            'font_family' => $typography['heading_font'],
            'font_size' => $sizeMap[$level] ?? 42,  // numeric value
            'font_weight' => $typography['heading_weight'],
            'line_height' => (float)$typography['heading_line_height'],  // em value like 1.2
            'letter_spacing' => -1,  // numeric px value
            'text_color' => $colors['heading'],
            'text_align' => $context['text_align'] ?? 'center',
            // Spacing group fields (Design tab)
            'margin_bottom' => 20  // numeric px value
        ];
    }

    /**
     * Get text/paragraph module styles
     * Maps to text module fields:
     * - text_orientation (Content tab): left, center, right, justify
     * - font_family, font_size, font_weight, line_height, text_color (Design tab - typography)
     */
    public static function getTextStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $typography = self::getTypography($style);

        return [
            // Content field
            'text_orientation' => $context['centered'] ?? false ? 'center' : 'left',
            // Typography group fields (Design tab)
            'font_family' => $typography['body_font'],
            'font_size' => (int)$typography['body_size'],  // numeric for range field
            'font_weight' => $typography['body_weight'],
            'line_height' => (float)$typography['line_height'],  // em value
            'text_color' => $colors['text']
        ];
    }

    /**
     * Get button styles
     * Maps to button module fields:
     * - font_size, font_weight, text_color (style_config)
     * - background_color, border_color (style_config with hover)
     * - button_padding_tb, button_padding_lr (CSS var based)
     */
    public static function getButtonStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $variant = $context['variant'] ?? 'primary';
        $borderRadius = $style === 'modern' ? 8 : ($style === 'minimal' ? 0 : 6);

        $baseStyles = [
            // Typography (Design tab - typography group)
            'font_weight' => '600',
            'font_size' => 16,  // numeric for range field
            // Border (Design tab - border group)
            'border_radius' => [
                'top_left' => $borderRadius,
                'top_right' => $borderRadius,
                'bottom_right' => $borderRadius,
                'bottom_left' => $borderRadius
            ]
        ];

        if ($variant === 'primary') {
            return array_merge($baseStyles, [
                'background_color' => $colors['primary'],
                'text_color' => '#FFFFFF',
                'border_width' => [
                    'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0
                ],
                // Hover states
                'background_color__hover' => $colors['secondary'],
                'text_color__hover' => '#FFFFFF'
            ]);
        } elseif ($variant === 'secondary') {
            return array_merge($baseStyles, [
                'background_color' => 'transparent',
                'text_color' => $colors['primary'],
                'border_width' => [
                    'top' => 2, 'right' => 2, 'bottom' => 2, 'left' => 2
                ],
                'border_style' => 'solid',
                'border_color' => $colors['primary'],
                // Hover states
                'background_color__hover' => $colors['primary'],
                'text_color__hover' => '#FFFFFF'
            ]);
        } elseif ($variant === 'ghost') {
            return array_merge($baseStyles, [
                'background_color' => 'transparent',
                'text_color' => $colors['text'],
                'border_width' => [
                    'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0
                ],
                // Hover states
                'background_color__hover' => $colors['background_alt'],
                'text_color__hover' => $colors['primary']
            ]);
        }

        return $baseStyles;
    }

    /**
     * Get blurb/feature card styles
     * Maps to blurb module fields:
     * - icon_color (color picker)
     * - icon_font_size (range, unit: px)
     * - text_orientation (select: left, center, right)
     * - title_color, content_color via style_config
     */
    public static function getBlurbStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $spacing = self::getSpacing($style);

        return [
            // Content fields
            'icon_color' => $colors['primary'],
            'icon_font_size' => 48,  // Blurb uses icon_font_size (range field, numeric)
            'text_orientation' => 'center',  // left, center, right
            'image_placement' => 'top',  // top, left
            // Style config fields
            'title_color' => $colors['heading'],
            'title_font_size' => 20,  // numeric for range field
            'content_color' => $colors['text_light'],
            'content_font_size' => 16,
            // Design tab - background group
            'background_color' => $colors['background'],
            // Design tab - border group
            'border_radius' => [
                'top_left' => 12,
                'top_right' => 12,
                'bottom_right' => 12,
                'bottom_left' => 12
            ],
            // Design tab - box_shadow group
            'box_shadow_style' => $style === 'minimal' ? 'none' : 'preset1'
        ];
    }

    /**
     * Get testimonial card styles
     * Maps to testimonial module fields:
     * - portrait_width, portrait_height (range, unit: px)
     * - portrait_border_radius (range, unit: %)
     * - quote_icon_color (color)
     * - quote_icon_size (range, unit: px)
     * - author_name_color, position_color, company_color, body_color (colors)
     */
    public static function getTestimonialStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);

        return [
            // Content fields with style values
            'portrait_width' => 90,               // range field, numeric
            'portrait_height' => 90,              // range field, numeric
            'portrait_border_radius' => 50,       // range field (%), numeric
            'quote_icon_color' => $colors['primary'],
            'quote_icon_size' => 32,              // range field, numeric
            // Style config color fields
            'author_name_color' => $colors['heading'],
            'position_color' => $colors['text_light'],
            'company_color' => $colors['primary'],
            'body_color' => $colors['text'],
            // Design tab - background
            'background_color' => $colors['background'],
            // Design tab - box shadow
            'box_shadow_style' => $style === 'minimal' ? 'none' : 'preset1',
            // Design tab - border
            'border_radius' => [
                'top_left' => 16,
                'top_right' => 16,
                'bottom_right' => 16,
                'bottom_left' => 16
            ]
        ];
    }

    /**
     * Get pricing table styles
     */
    public static function getPricingStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);
        $isFeatured = $context['featured'] ?? false;

        $baseStyles = [
            'title_font_size' => '24px',
            'title_font_weight' => '700',
            'title_color' => $colors['heading'],
            'price_font_size' => '56px',
            'price_font_weight' => '800',
            'price_color' => $colors['primary'],
            'period_font_size' => '16px',
            'period_color' => $colors['text_light'],
            'feature_font_size' => '16px',
            'feature_color' => $colors['text'],
            'feature_icon_color' => '#10B981',
            'card_padding' => '40px',
            'card_border_radius' => '16px',
            'card_shadow' => self::getShadowStyles($style)['card']
        ];

        if ($isFeatured) {
            return array_merge($baseStyles, [
                'card_background' => $colors['primary'],
                'title_color' => '#FFFFFF',
                'price_color' => '#FFFFFF',
                'period_color' => 'rgba(255,255,255,0.8)',
                'feature_color' => 'rgba(255,255,255,0.9)',
                'feature_icon_color' => '#FFFFFF',
                'card_shadow' => self::getShadowStyles($style)['elevated'],
                'transform' => 'scale(1.05)'
            ]);
        }

        return array_merge($baseStyles, [
            'card_background' => $colors['background'],
            'card_border' => '1px solid ' . $colors['background_alt']
        ]);
    }

    /**
     * Get CTA section styles
     * Maps to cta module fields:
     * - promo_color (background color)
     * - title_font_size, title_color (style_config)
     * - content_color (style_config)
     * - button_bg_color, button_text_color, button_border_* (button styling)
     */
    public static function getCTAStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);

        return [
            // Content fields
            'promo_color' => $colors['primary'],  // Background color field
            // Style config fields
            'title_font_size' => 40,              // numeric for range field
            'title_color' => '#FFFFFF',
            'content_color' => 'rgba(255,255,255,0.9)',
            // Button fields (CTA has its own button fields, not separate button module)
            'button_bg_color' => '#FFFFFF',
            'button_text_color' => $colors['primary'],
            'button_border_width' => 0,
            'button_border_radius' => 8,
            // Hover states
            'button_bg_color__hover' => 'rgba(255,255,255,0.9)',
            'button_text_color__hover' => $colors['secondary']
        ];
    }

    /**
     * Get counter/stats styles
     */
    public static function getCounterStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);

        return [
            'number_font_size' => '56px',
            'number_font_weight' => '800',
            'number_color' => $colors['primary'],
            'title_font_size' => '16px',
            'title_font_weight' => '500',
            'title_color' => $colors['text_light'],
            'text_align' => 'center',
            'suffix_font_size' => '32px'
        ];
    }

    /**
     * Get team member card styles
     */
    public static function getTeamMemberStyles(string $style, array $context = []): array
    {
        $colors = self::getColorPalette($style);

        return [
            'image_border_radius' => '12px',
            'image_aspect_ratio' => '1',
            'name_font_size' => '20px',
            'name_font_weight' => '600',
            'name_color' => $colors['heading'],
            'position_font_size' => '14px',
            'position_color' => $colors['primary'],
            'bio_font_size' => '15px',
            'bio_color' => $colors['text_light'],
            'social_icon_size' => '20px',
            'social_icon_color' => $colors['text_light'],
            'social_icon_hover' => $colors['primary'],
            'card_padding' => '24px',
            'text_align' => 'center'
        ];
    }

    /**
     * Get image module styles
     * Maps to image module Design tab fields:
     * - border_radius (array format: top_left, top_right, bottom_right, bottom_left)
     * - box_shadow_style
     */
    public static function getImageStyles(string $style, array $context = []): array
    {
        $radius = $style === 'modern' ? 12 : ($style === 'minimal' ? 0 : 8);

        return [
            // Border radius as array for border group
            'border_radius' => [
                'top_left' => $radius,
                'top_right' => $radius,
                'bottom_right' => $radius,
                'bottom_left' => $radius
            ],
            // Box shadow preset
            'box_shadow_style' => $style === 'minimal' ? 'none' : 'preset2'
        ];
    }

    // ========================================
    // Support Styles
    // ========================================

    /**
     * Get shadow styles by design style
     */
    public static function getShadowStyles(string $style): array
    {
        $shadows = [
            'modern' => [
                'card' => '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)',
                'elevated' => '0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04)',
                'image' => '0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)',
                'button' => '0 4px 6px -1px rgba(0,0,0,0.1)'
            ],
            'minimal' => [
                'card' => 'none',
                'elevated' => '0 1px 3px rgba(0,0,0,0.12)',
                'image' => 'none',
                'button' => 'none'
            ],
            'bold' => [
                'card' => '0 10px 40px rgba(0,0,0,0.15)',
                'elevated' => '0 25px 50px rgba(0,0,0,0.2)',
                'image' => '0 10px 30px rgba(0,0,0,0.15)',
                'button' => '0 6px 20px rgba(0,0,0,0.15)'
            ],
            'elegant' => [
                'card' => '0 2px 15px rgba(0,0,0,0.08)',
                'elevated' => '0 10px 40px rgba(0,0,0,0.1)',
                'image' => '0 5px 15px rgba(0,0,0,0.08)',
                'button' => '0 2px 8px rgba(0,0,0,0.1)'
            ]
        ];

        return $shadows[$style] ?? $shadows['modern'];
    }

    /**
     * Get border styles
     */
    public static function getBorderStyles(string $style): array
    {
        $borders = [
            'modern' => [
                'radius_small' => '6px',
                'radius_medium' => '12px',
                'radius_large' => '16px',
                'width' => '1px',
                'color' => '#E5E7EB'
            ],
            'minimal' => [
                'radius_small' => '0',
                'radius_medium' => '0',
                'radius_large' => '0',
                'width' => '1px',
                'color' => '#E5E7EB'
            ],
            'bold' => [
                'radius_small' => '8px',
                'radius_medium' => '16px',
                'radius_large' => '24px',
                'width' => '2px',
                'color' => '#E5E7EB'
            ],
            'elegant' => [
                'radius_small' => '4px',
                'radius_medium' => '8px',
                'radius_large' => '12px',
                'width' => '1px',
                'color' => '#D4D4D8'
            ]
        ];

        return $borders[$style] ?? $borders['modern'];
    }

    /**
     * Get animation presets
     */
    public static function getAnimationPreset(string $style): array
    {
        $animations = [
            'modern' => [
                'entrance' => 'fade',
                'duration' => '600ms',
                'delay_increment' => '100ms',
                'easing' => 'cubic-bezier(0.4, 0, 0.2, 1)'
            ],
            'minimal' => [
                'entrance' => 'none',
                'duration' => '0',
                'delay_increment' => '0',
                'easing' => 'linear'
            ],
            'bold' => [
                'entrance' => 'slide-up',
                'duration' => '800ms',
                'delay_increment' => '150ms',
                'easing' => 'cubic-bezier(0.34, 1.56, 0.64, 1)'
            ],
            'elegant' => [
                'entrance' => 'fade',
                'duration' => '1000ms',
                'delay_increment' => '200ms',
                'easing' => 'ease-out'
            ],
            'playful' => [
                'entrance' => 'bounce',
                'duration' => '700ms',
                'delay_increment' => '100ms',
                'easing' => 'cubic-bezier(0.68, -0.55, 0.265, 1.55)'
            ]
        ];

        return $animations[$style] ?? $animations['modern'];
    }

    // ========================================
    // Industry-Specific Color Adjustments
    // ========================================

    /**
     * Get industry-specific color overrides
     */
    public static function getIndustryColors(string $industry): array
    {
        $industryColors = [
            'healthcare' => [
                'primary' => '#0D9488',      // Teal
                'secondary' => '#115E59',
                'accent' => '#F97316'
            ],
            'finance' => [
                'primary' => '#1E40AF',      // Blue
                'secondary' => '#1E3A8A',
                'accent' => '#10B981'
            ],
            'technology' => [
                'primary' => '#6366F1',      // Indigo
                'secondary' => '#4F46E5',
                'accent' => '#06B6D4'
            ],
            'education' => [
                'primary' => '#2563EB',      // Blue
                'secondary' => '#1D4ED8',
                'accent' => '#F59E0B'
            ],
            'restaurant' => [
                'primary' => '#DC2626',      // Red
                'secondary' => '#B91C1C',
                'accent' => '#F59E0B'
            ],
            'realestate' => [
                'primary' => '#059669',      // Emerald
                'secondary' => '#047857',
                'accent' => '#0284C7'
            ],
            'real_estate' => [
                'primary' => '#059669',      // Emerald (alias)
                'secondary' => '#047857',
                'accent' => '#0284C7'
            ],
            'fitness' => [
                'primary' => '#EF4444',      // Red
                'secondary' => '#DC2626',
                'accent' => '#F97316'
            ],
            'agency' => [
                'primary' => '#7C3AED',      // Violet
                'secondary' => '#6D28D9',
                'accent' => '#EC4899'
            ],
            'creative' => [
                'primary' => '#BE185D',      // Pink
                'secondary' => '#DB2777',
                'accent' => '#7C3AED'
            ],
            'retail' => [
                'primary' => '#DB2777',      // Pink
                'secondary' => '#EC4899',
                'accent' => '#F59E0B'
            ],
            'ecommerce' => [
                'primary' => '#DB2777',      // Pink
                'secondary' => '#EC4899',
                'accent' => '#F59E0B'
            ],
            'construction' => [
                'primary' => '#EA580C',      // Orange
                'secondary' => '#78716C',
                'accent' => '#F59E0B'
            ],
            'legal' => [
                'primary' => '#1E3A5F',      // Navy
                'secondary' => '#1E40AF',
                'accent' => '#B8860B'
            ]
        ];

        $colors = $industryColors[$industry] ?? [];

        // Auto-generate extended palette (dark, light_bg, text, text_light)
        // so all consumers get a complete color set
        if (!empty($colors) && empty($colors['dark'])) {
            $colors['dark'] = self::darkenColor($colors['primary'], 30);
            $colors['light_bg'] = self::lightenColor($colors['primary'], 92);
            $colors['text'] = self::darkenColor($colors['primary'], 40);
            $colors['text_light'] = '#6b7280';
        }

        // Provide sensible defaults when industry is unknown
        if (empty($colors)) {
            $colors = [
                'primary' => '#2563eb',
                'secondary' => '#3b82f6',
                'accent' => '#1e3a8a',
                'dark' => '#1e3a8a',
                'light_bg' => '#f8fafc',
                'text' => '#111827',
                'text_light' => '#6b7280'
            ];
        }

        return $colors;
    }

    /**
     * Darken a hex color by a percentage (0-100)
     */
    private static function darkenColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - (int)(255 * $percent / 100));
        $g = max(0, hexdec(substr($hex, 2, 2)) - (int)(255 * $percent / 100));
        $b = max(0, hexdec(substr($hex, 4, 2)) - (int)(255 * $percent / 100));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Lighten a hex color by a percentage (0-100) - creates very light tint
     */
    private static function lightenColor(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $factor = $percent / 100;
        $r = (int)(hexdec(substr($hex, 0, 2)) + (255 - hexdec(substr($hex, 0, 2))) * $factor);
        $g = (int)(hexdec(substr($hex, 2, 2)) + (255 - hexdec(substr($hex, 2, 2))) * $factor);
        $b = (int)(hexdec(substr($hex, 4, 2)) + (255 - hexdec(substr($hex, 4, 2))) * $factor);
        return sprintf('#%02x%02x%02x', min(255, $r), min(255, $g), min(255, $b));
    }

    /**
     * Merge style preset with industry colors
     */
    public static function getMergedStyles(string $style, string $industry, array $context = []): array
    {
        $preset = self::getStylePreset($style, $context);
        $industryColors = self::getIndustryColors($industry);

        if (!empty($industryColors)) {
            $preset['colors'] = array_merge($preset['colors'], $industryColors);
        }

        return $preset;
    }

    // ========================================
    // GOLDEN PRESET CONTRACT - Context Colors
    // ========================================

    /**
     * Get colors for a specific visual context
     * This is the SINGLE SOURCE OF TRUTH for module colors
     *
     * @param string $visualContext 'LIGHT' | 'DARK' | 'PRIMARY'
     * @param string $style Style name (modern, minimal, etc.)
     * @return array Complete color set for the context
     */
    public static function getContextColors(string $visualContext, string $style = 'modern'): array
    {
        $baseColors = self::getColorPalette($style);

        switch (strtoupper($visualContext)) {
            case 'DARK':
                return [
                    // Section
                    'section_background' => '#111827',
                    'section_background_alt' => '#1F2937',
                    'background' => '#111827',
                    'background_alt' => '#1F2937',
                    // Text
                    'heading' => '#F9FAFB',
                    'text' => '#E5E7EB',
                    'text_light' => '#9CA3AF',
                    // Cards
                    'card_background' => '#1F2937',
                    'card_border' => '#374151',
                    'card_shadow' => '0 4px 6px rgba(0,0,0,0.3)',
                    // Icons & accents
                    'icon' => '#60A5FA',
                    'icon_color' => '#60A5FA',
                    'primary' => $baseColors['primary'] ?? '#3B82F6',
                    'secondary' => $baseColors['secondary'] ?? '#1E40AF',
                    'accent' => $baseColors['accent'] ?? '#F59E0B',
                    // Dividers
                    'border' => '#374151',
                    'divider_color' => 'rgba(255,255,255,0.2)',
                    // Buttons (aliased for compatibility)
                    'button_primary_bg' => '#FFFFFF',
                    'button_primary_text' => $baseColors['primary'] ?? '#3B82F6',
                    'button_secondary_bg' => 'transparent',
                    'button_secondary_text' => '#FFFFFF',
                    'button_secondary_border' => 'rgba(255,255,255,0.5)',
                    // Aliases for Pattern Renderer
                    'button_background' => '#FFFFFF',
                    'button_text' => $baseColors['primary'] ?? '#3B82F6',
                ];

            case 'PRIMARY':
                return [
                    // Section
                    'section_background' => $baseColors['primary'] ?? '#3B82F6',
                    'section_background_alt' => $baseColors['secondary'] ?? '#1E40AF',
                    'background' => $baseColors['primary'] ?? '#3B82F6',
                    'background_alt' => $baseColors['secondary'] ?? '#1E40AF',
                    // Text
                    'heading' => '#FFFFFF',
                    'text' => 'rgba(255,255,255,0.9)',
                    'text_light' => 'rgba(255,255,255,0.7)',
                    // Cards
                    'card_background' => 'rgba(255,255,255,0.1)',
                    'card_border' => 'rgba(255,255,255,0.2)',
                    'card_shadow' => '0 4px 6px rgba(0,0,0,0.2)',
                    // Icons & accents
                    'icon' => '#FFFFFF',
                    'icon_color' => '#FFFFFF',
                    'primary' => $baseColors['primary'] ?? '#3B82F6',
                    'secondary' => $baseColors['secondary'] ?? '#1E40AF',
                    'accent' => '#FFFFFF',
                    // Dividers
                    'border' => 'rgba(255,255,255,0.2)',
                    'divider_color' => 'rgba(255,255,255,0.3)',
                    // Buttons
                    'button_primary_bg' => '#FFFFFF',
                    'button_primary_text' => $baseColors['primary'] ?? '#3B82F6',
                    'button_secondary_bg' => 'transparent',
                    'button_secondary_text' => '#FFFFFF',
                    'button_secondary_border' => 'rgba(255,255,255,0.5)',
                    // Aliases for Pattern Renderer
                    'button_background' => '#FFFFFF',
                    'button_text' => $baseColors['primary'] ?? '#3B82F6',
                ];

            case 'LIGHT':
            default:
                return [
                    // Section
                    'section_background' => '#FFFFFF',
                    'section_background_alt' => '#F9FAFB',
                    'background' => '#FFFFFF',
                    'background_alt' => '#F9FAFB',
                    // Text
                    'heading' => $baseColors['heading'] ?? '#111827',
                    'text' => $baseColors['text'] ?? '#4B5563',
                    'text_light' => $baseColors['text_light'] ?? '#6B7280',
                    // Cards
                    'card_background' => '#FFFFFF',
                    'card_border' => '#E5E7EB',
                    'card_shadow' => '0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06)',
                    // Icons & accents
                    'icon' => $baseColors['primary'] ?? '#3B82F6',
                    'icon_color' => $baseColors['primary'] ?? '#3B82F6',
                    'primary' => $baseColors['primary'] ?? '#3B82F6',
                    'secondary' => $baseColors['secondary'] ?? '#1E40AF',
                    'accent' => $baseColors['accent'] ?? '#F59E0B',
                    // Dividers
                    'border' => '#E5E7EB',
                    'divider_color' => '#E5E7EB',
                    // Buttons
                    'button_primary_bg' => $baseColors['primary'] ?? '#3B82F6',
                    'button_primary_text' => '#FFFFFF',
                    'button_secondary_bg' => 'transparent',
                    'button_secondary_text' => $baseColors['primary'] ?? '#3B82F6',
                    'button_secondary_border' => $baseColors['primary'] ?? '#3B82F6',
                    // Aliases for Pattern Renderer
                    'button_background' => $baseColors['primary'] ?? '#3B82F6',
                    'button_text' => '#FFFFFF',
                ];
        }
    }

    /**
     * Resolve visual context from pattern to final value
     *
     * @param string $patternContext Pattern's visual_context ('INHERIT', 'LIGHT', 'DARK', 'PRIMARY')
     * @param string $pageTheme Page-level theme ('LIGHT' or 'DARK')
     * @return string Resolved context ('LIGHT', 'DARK', or 'PRIMARY')
     */
    public static function resolveVisualContext(string $patternContext, string $pageTheme = 'LIGHT'): string
    {
        $patternContext = strtoupper($patternContext);

        if ($patternContext === 'INHERIT' || empty($patternContext)) {
            return strtoupper($pageTheme) === 'DARK' ? 'DARK' : 'LIGHT';
        }

        return in_array($patternContext, ['LIGHT', 'DARK', 'PRIMARY']) ? $patternContext : 'LIGHT';
    }
}
