<?php
/**
 * JTB AI Composer
 * Compositional system for modern, unique layouts
 *
 * This class defines layout PATTERNS, not content.
 * Patterns are structural blueprints that can be filled with any content.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Composer
{
    // ========================================
    // TENSION LEVELS - for rhythm planning
    // ========================================

    const TENSION_HIGH = 3;      // Hero, Final CTA
    const TENSION_MEDIUM = 2;    // Grid, Zigzag, Pricing
    const TENSION_LOW = 1;       // Breathing, Testimonial

    // Pattern tension mapping
    private static array $patternTension = [
        'hero_asymmetric' => self::TENSION_HIGH,
        'hero_centered' => self::TENSION_HIGH,
        'hero_split' => self::TENSION_HIGH,
        'grid_density' => self::TENSION_MEDIUM,
        'grid_featured' => self::TENSION_MEDIUM,
        'zigzag_narrative' => self::TENSION_MEDIUM,
        'progressive_disclosure' => self::TENSION_MEDIUM,
        'testimonial_spotlight' => self::TENSION_LOW,
        'trust_metrics' => self::TENSION_MEDIUM,
        'pricing_tiered' => self::TENSION_MEDIUM,
        'pricing_comparison' => self::TENSION_MEDIUM,
        'faq_expandable' => self::TENSION_LOW,
        'tabbed_content' => self::TENSION_MEDIUM,
        'breathing_space' => self::TENSION_LOW,
        'visual_bridge' => self::TENSION_LOW,
        'final_cta' => self::TENSION_HIGH,
        'contact_gateway' => self::TENSION_MEDIUM,
    ];

    // ========================================
    // COMPATIBILITY MATRIX
    // ========================================

    // 2 = natural, 1 = possible with transition, 0 = avoid
    private static array $compatibilityMatrix = [
        'hero_asymmetric' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'breathing_space' => 2,
            'testimonial_spotlight' => 1, 'trust_metrics' => 2, 'pricing_tiered' => 1,
            'progressive_disclosure' => 2, 'final_cta' => 0
        ],
        'hero_centered' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'breathing_space' => 2,
            'trust_metrics' => 2, 'testimonial_spotlight' => 1, 'pricing_tiered' => 1,
            'progressive_disclosure' => 2, 'final_cta' => 0
        ],
        'hero_split' => [
            'grid_density' => 2, 'zigzag_narrative' => 1, 'breathing_space' => 2,
            'trust_metrics' => 2, 'testimonial_spotlight' => 1, 'pricing_tiered' => 1,
            'progressive_disclosure' => 2, 'final_cta' => 0
        ],
        'grid_density' => [
            'zigzag_narrative' => 2, 'testimonial_spotlight' => 2, 'breathing_space' => 2,
            'pricing_tiered' => 2, 'final_cta' => 2, 'trust_metrics' => 1,
            'grid_density' => 1, 'faq_expandable' => 2
        ],
        'zigzag_narrative' => [
            'grid_density' => 2, 'testimonial_spotlight' => 2, 'breathing_space' => 2,
            'pricing_tiered' => 1, 'final_cta' => 2, 'trust_metrics' => 2,
            'zigzag_narrative' => 0, 'faq_expandable' => 2
        ],
        'testimonial_spotlight' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'pricing_tiered' => 2,
            'final_cta' => 2, 'breathing_space' => 1, 'trust_metrics' => 1,
            'testimonial_spotlight' => 0, 'faq_expandable' => 2
        ],
        'trust_metrics' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'testimonial_spotlight' => 2,
            'pricing_tiered' => 2, 'final_cta' => 2, 'breathing_space' => 1,
            'faq_expandable' => 2
        ],
        'pricing_tiered' => [
            'faq_expandable' => 2, 'testimonial_spotlight' => 2, 'final_cta' => 2,
            'breathing_space' => 1, 'grid_density' => 0, 'zigzag_narrative' => 0,
            'pricing_tiered' => 0
        ],
        'faq_expandable' => [
            'final_cta' => 2, 'contact_gateway' => 2, 'testimonial_spotlight' => 1,
            'breathing_space' => 1, 'grid_density' => 1
        ],
        'breathing_space' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'testimonial_spotlight' => 2,
            'pricing_tiered' => 2, 'final_cta' => 2, 'trust_metrics' => 2,
            'faq_expandable' => 2, 'breathing_space' => 0
        ],
        'visual_bridge' => [
            'grid_density' => 2, 'zigzag_narrative' => 2, 'testimonial_spotlight' => 2,
            'pricing_tiered' => 2, 'final_cta' => 2, 'trust_metrics' => 2,
            'faq_expandable' => 2
        ],
        'final_cta' => [
            'contact_gateway' => 1, 'final_cta' => 0
        ],
        'contact_gateway' => [
            'final_cta' => 0, 'contact_gateway' => 0
        ],
    ];

    // ========================================
    // MAIN COMPOSITION METHOD
    // ========================================

    /**
     * Compose a full page layout using patterns
     *
     * @param string $pageIntent What the page should achieve
     * @param array $options Composition options
     * @return array Array of section structures
     */
    public static function composePage(string $pageIntent, array $options = []): array
    {
        // Determine pattern sequence based on intent
        $sequence = self::determinePatternSequence($pageIntent, $options);

        // Build sections from patterns
        $sections = [];
        $previousPattern = null;

        foreach ($sequence as $index => $patternConfig) {
            $patternName = $patternConfig['pattern'];
            $variant = $patternConfig['variant'] ?? 'default';
            $purpose = $patternConfig['purpose'] ?? null;
            $context = array_merge($options, [
                'section_index' => $index,
                'total_sections' => count($sequence),
                'previous_pattern' => $previousPattern,
                'pattern_purpose' => $purpose,
            ]);

            // Check if we need a transition
            if ($previousPattern && self::needsTransition($previousPattern, $patternName)) {
                $transitionSection = self::composePattern('breathing_space', 'minimal', $context);
                if (!empty($transitionSection)) {
                    $transitionSection['purpose'] = 'pause';
                    $sections[] = $transitionSection;
                }
            }

            // Compose the pattern
            $section = self::composePattern($patternName, $variant, $context);
            if (!empty($section)) {
                // Add purpose from sequence to the composed section
                $section['purpose'] = $purpose;
                $sections[] = $section;
            }

            $previousPattern = $patternName;
        }

        return $sections;
    }

    /**
     * Determine pattern sequence based on page intent
     */
    private static function determinePatternSequence(string $intent, array $options): array
    {
        // HARDCODED SEQUENCES REMOVED - AI must generate layouts!
        // This legacy system is deprecated. Use AST pipeline instead.
        throw new \RuntimeException('Legacy pattern sequences removed. Enable use_ast=true for AI-driven layouts.');
    }

    /**
     * Build custom pattern sequence - REMOVED
     */
    private static function buildCustomSequence(string $intent, array $options): array
    {
        // REMOVED - AI must generate layouts
        throw new \RuntimeException('Legacy pattern sequences removed. Enable use_ast=true for AI-driven layouts.');
    }

    /**
     * Check if transition is needed between patterns
     */
    private static function needsTransition(string $from, string $to): bool
    {
        // Never add transition after breathing_space (it IS the transition)
        if ($from === 'breathing_space') {
            return false;
        }

        // Never add transition before breathing_space (it IS the transition)
        if ($to === 'breathing_space') {
            return false;
        }

        // Get compatibility score
        $score = self::$compatibilityMatrix[$from][$to] ?? 1;

        // Need transition if compatibility is low
        return $score === 1;
    }

    // ========================================
    // PATTERN COMPOSITION METHODS
    // ========================================

    /**
     * Compose a single pattern
     */
    public static function composePattern(string $patternName, string $variant = 'default', array $context = []): array
    {
        return match($patternName) {
            // Hero patterns
            'hero_asymmetric' => self::composeHeroAsymmetric($variant, $context),
            'hero_centered' => self::composeHeroCentered($variant, $context),
            'hero_split' => self::composeHeroSplit($variant, $context),

            // Content flow patterns
            'grid_density' => self::composeGridDensity($variant, $context),
            'grid_featured' => self::composeGridFeatured($variant, $context),
            'zigzag_narrative' => self::composeZigzagNarrative($variant, $context),
            'progressive_disclosure' => self::composeProgressiveDisclosure($variant, $context),

            // Social proof patterns
            'testimonial_spotlight' => self::composeTestimonialSpotlight($variant, $context),
            'trust_metrics' => self::composeTrustMetrics($variant, $context),

            // Pricing patterns
            'pricing_tiered' => self::composePricingTiered($variant, $context),
            'pricing_comparison' => self::composePricingComparison($variant, $context),

            // Interaction patterns
            'faq_expandable' => self::composeFaqExpandable($variant, $context),
            'tabbed_content' => self::composeTabbedContent($variant, $context),

            // Transitional patterns
            'breathing_space' => self::composeBreathingSpace($variant, $context),
            'visual_bridge' => self::composeVisualBridge($variant, $context),

            // Closure patterns
            'final_cta' => self::composeFinalCta($variant, $context),
            'contact_gateway' => self::composeContactGateway($variant, $context),

            // ========================================
            // LEGACY ALIASES (for UI compatibility)
            // Maps old pattern names to new compositional patterns
            // ========================================
            'features_grid' => self::composeGridDensity($variant === 'default' ? 'features' : $variant, $context),
            'features_alternating' => self::composeZigzagNarrative($variant === 'default' ? 'benefits' : $variant, $context),
            'benefits_list' => self::composeZigzagNarrative('benefits', $context),
            'content_showcase' => self::composeGridFeatured($variant === 'default' ? 'featured_left' : $variant, $context),
            'testimonial_carousel' => self::composeTestimonialSpotlight($variant === 'default' ? 'carousel' : $variant, $context),
            'testimonial_grid' => self::composeTestimonialSpotlight('grid', $context),
            'logos_strip' => self::composeTrustMetrics('logos', $context),
            'stats_bar' => self::composeTrustMetrics($variant, $context),
            'pricing_single' => self::composePricingTiered('two_plans', $context),
            'faq_accordion' => self::composeFaqExpandable($variant === 'default' ? 'single_column' : $variant, $context),
            'cta_split' => self::composeFinalCta($variant === 'default' ? 'two_paths' : $variant, $context),
            'cta_centered' => self::composeFinalCta('simple', $context),
            'contact_form' => self::composeContactGateway($variant === 'default' ? 'with_form' : $variant, $context),

            default => []
        };
    }

    // ========================================
    // HERO PATTERNS
    // ========================================

    /**
     * Hero Asymmetric - strong visual anchor
     * Variants: image_right, image_left, with_screenshot, layered
     */
    private static function composeHeroAsymmetric(string $variant, array $context): array
    {
        $isReversed = in_array($variant, ['image_left', 'reversed']);
        $contentWidth = $variant === 'layered' ? '3_5' : '2_3';
        $imageWidth = $variant === 'layered' ? '2_5' : '1_3';

        $contentColumn = [
            'width' => $contentWidth,
            'modules' => [
                ['type' => 'heading', 'role' => 'hero_title', 'level' => 'h1'],
                ['type' => 'text', 'role' => 'hero_subtitle'],
                ['type' => 'button', 'role' => 'primary_cta'],
            ]
        ];

        $imageColumn = [
            'width' => $imageWidth,
            'modules' => [
                ['type' => 'image', 'role' => 'hero_image']
            ]
        ];

        // Add secondary button for some variants
        if (in_array($variant, ['with_screenshot', 'layered'])) {
            $contentColumn['modules'][] = ['type' => 'button', 'role' => 'secondary_cta'];
        }

        // Determine visual context based on variant
        $visualContext = in_array($variant, ['dark', 'inverted']) ? 'DARK' : 'INHERIT';

        return [
            'pattern' => 'hero_asymmetric',
            'variant' => $variant,
            'tension' => self::TENSION_HIGH,
            'attrs' => [
                'section_type' => 'hero',
                'visual_context' => $visualContext,
                'min_height' => '90vh',
                'background_style' => $variant === 'layered' ? 'gradient' : 'solid',
                'vertical_align' => 'center',
            ],
            'rows' => [
                [
                    'columns' => $isReversed
                        ? [$imageColumn, $contentColumn]
                        : [$contentColumn, $imageColumn],
                    'vertical_align' => 'center',
                    'gap' => 'large',
                ]
            ],
            'composition_axis' => [
                'vertical' => 'left_edge_heading',
                'horizontal' => 'button_center',
            ]
        ];
    }

    /**
     * Hero Centered - single focused message
     * Variants: minimal, with_video, with_counterpoint, dramatic
     */
    private static function composeHeroCentered(string $variant, array $context): array
    {
        $modules = [
            ['type' => 'heading', 'role' => 'hero_title', 'level' => 'h1', 'align' => 'center'],
            ['type' => 'text', 'role' => 'hero_subtitle', 'align' => 'center', 'max_width' => '70%'],
            ['type' => 'button', 'role' => 'primary_cta', 'align' => 'center'],
        ];

        // Add video for video variant
        if ($variant === 'with_video') {
            $modules[] = ['type' => 'video', 'role' => 'demo_video', 'style' => 'modal_trigger'];
        }

        $rows = [
            [
                'columns' => [
                    ['width' => '1_1', 'modules' => $modules]
                ],
                'content_align' => 'center',
            ]
        ];

        // Add counterpoint row
        if ($variant === 'with_counterpoint') {
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'number_counter', 'role' => 'metric_1']]],
                    ['width' => '1_3', 'modules' => [['type' => 'number_counter', 'role' => 'metric_2']]],
                    ['width' => '1_3', 'modules' => [['type' => 'number_counter', 'role' => 'metric_3']]],
                ],
                'spacing' => 'tight',
            ];
        }

        // Dramatic variant = dark context
        $visualContext = $variant === 'dramatic' ? 'DARK' : 'INHERIT';

        return [
            'pattern' => 'hero_centered',
            'variant' => $variant,
            'tension' => self::TENSION_HIGH,
            'attrs' => [
                'section_type' => 'hero',
                'visual_context' => $visualContext,
                'min_height' => $variant === 'dramatic' ? '100vh' : '80vh',
                'background_style' => $variant === 'dramatic' ? 'image_overlay' : 'gradient',
                'text_align' => 'center',
            ],
            'rows' => $rows,
            'composition_axis' => [
                'vertical' => 'center',
                'horizontal' => 'center',
            ]
        ];
    }

    /**
     * Hero Split - two narratives side by side
     * Variants: equal, diagonal, inverted_colors, stacked_mobile
     */
    private static function composeHeroSplit(string $variant, array $context): array
    {
        $leftModules = [
            ['type' => 'heading', 'role' => 'hero_title', 'level' => 'h1'],
            ['type' => 'text', 'role' => 'hero_subtitle'],
            ['type' => 'button', 'role' => 'primary_cta'],
        ];

        $rightModules = [
            ['type' => 'image', 'role' => 'hero_image', 'style' => 'cover']
        ];

        if ($variant === 'video_background') {
            $rightModules = [
                ['type' => 'video', 'role' => 'background_video', 'style' => 'background']
            ];
        }

        // Inverted colors = dark context
        $visualContext = $variant === 'inverted_colors' ? 'DARK' : 'INHERIT';

        return [
            'pattern' => 'hero_split',
            'variant' => $variant,
            'tension' => self::TENSION_HIGH,
            'attrs' => [
                'section_type' => 'hero',
                'visual_context' => $visualContext,
                'full_width' => true,
                'no_container' => true,
                'min_height' => '100vh',
                'split_style' => $variant === 'diagonal' ? 'diagonal' : 'straight',
            ],
            'rows' => [
                [
                    'columns' => [
                        [
                            'width' => '1_2',
                            'modules' => $leftModules,
                            'background' => $variant === 'inverted_colors' ? 'dark' : 'light',
                            'padding' => 'large',
                        ],
                        [
                            'width' => '1_2',
                            'modules' => $rightModules,
                            'background' => 'media',
                            'padding' => 'none',
                        ],
                    ],
                    'no_gap' => true,
                ]
            ],
            'composition_axis' => [
                'vertical' => 'split_line',
                'horizontal' => 'center',
            ]
        ];
    }

    // ========================================
    // CONTENT FLOW PATTERNS
    // ========================================

    /**
     * Grid Density - multiple equal items
     * Variants: features, services, team, masonry, progressive
     */
    private static function composeGridDensity(string $variant, array $context): array
    {
        // Determine grid configuration
        $config = match($variant) {
            'features' => ['cols' => 3, 'rows' => 2, 'module' => 'blurb', 'style' => 'icon_top'],
            'services' => ['cols' => 3, 'rows' => 2, 'module' => 'blurb', 'style' => 'card'],
            'team' => ['cols' => 4, 'rows' => 2, 'module' => 'team_member', 'style' => 'centered'],
            'masonry' => ['cols' => 3, 'rows' => 3, 'module' => 'blurb', 'style' => 'varied_height'],
            'progressive' => ['cols' => [2, 3, 4], 'rows' => 3, 'module' => 'blurb', 'style' => 'increasing'],
            default => ['cols' => 3, 'rows' => 2, 'module' => 'blurb', 'style' => 'default'],
        };

        $rows = [];

        // Header row
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                        ['type' => 'text', 'role' => 'section_intro', 'align' => 'center', 'max_width' => '60%'],
                    ]
                ]
            ]
        ];

        // Grid rows
        if ($variant === 'progressive') {
            // Progressive density: 2 cols, then 3, then 4
            foreach ($config['cols'] as $rowIndex => $colCount) {
                $width = self::getColumnWidth($colCount);
                $columns = [];
                for ($i = 0; $i < $colCount; $i++) {
                    $columns[] = [
                        'width' => $width,
                        'modules' => [
                            ['type' => $config['module'], 'role' => 'grid_item', 'index' => ($rowIndex * 3) + $i]
                        ]
                    ];
                }
                $rows[] = ['columns' => $columns];
            }
        } else {
            // Regular grid
            $width = self::getColumnWidth($config['cols']);
            for ($row = 0; $row < $config['rows']; $row++) {
                $columns = [];
                for ($col = 0; $col < $config['cols']; $col++) {
                    $columns[] = [
                        'width' => $width,
                        'modules' => [
                            ['type' => $config['module'], 'role' => 'grid_item', 'index' => ($row * $config['cols']) + $col]
                        ]
                    ];
                }
                $rows[] = ['columns' => $columns];
            }
        }

        return [
            'pattern' => 'grid_density',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'features',
                'visual_context' => 'INHERIT',
                'background_style' => 'alternate',
            ],
            'rows' => $rows,
            'composition_axis' => [
                'vertical' => 'grid_lines',
                'horizontal' => 'heading_baseline',
            ]
        ];
    }

    /**
     * Grid Featured - one large item + smaller grid
     * Variants: featured_left, featured_right, featured_top
     */
    private static function composeGridFeatured(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2'],
                    ]
                ]
            ]
        ];

        if ($variant === 'featured_top') {
            // Featured on top, grid below
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'blurb', 'role' => 'featured_item', 'style' => 'large']
                        ]
                    ]
                ]
            ];
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'grid_item', 'index' => 0]]],
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'grid_item', 'index' => 1]]],
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'grid_item', 'index' => 2]]],
                ]
            ];
        } else {
            // Featured side by side with grid
            $featuredColumn = [
                'width' => '2_3',
                'modules' => [
                    ['type' => 'blurb', 'role' => 'featured_item', 'style' => 'large']
                ]
            ];
            $gridColumn = [
                'width' => '1_3',
                'modules' => [
                    ['type' => 'blurb', 'role' => 'grid_item', 'index' => 0],
                    ['type' => 'blurb', 'role' => 'grid_item', 'index' => 1],
                ]
            ];

            $rows[] = [
                'columns' => $variant === 'featured_left'
                    ? [$featuredColumn, $gridColumn]
                    : [$gridColumn, $featuredColumn]
            ];
        }

        return [
            'pattern' => 'grid_featured',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'features',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Zigzag Narrative - alternating content/image
     * Variants: three_beats, five_beats, with_dividers, with_counters, benefits, case_studies, process
     */
    private static function composeZigzagNarrative(string $variant, array $context): array
    {
        $beatCount = match($variant) {
            'five_beats', 'case_studies' => 5,
            'three_beats', 'benefits', 'process' => 3,
            default => 3,
        };

        $rows = [];

        // Optional header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        // Generate beats
        for ($i = 0; $i < $beatCount; $i++) {
            $isReversed = $i % 2 === 1;

            $contentModules = [
                ['type' => 'heading', 'role' => 'beat_title', 'level' => 'h3', 'index' => $i],
                ['type' => 'text', 'role' => 'beat_content', 'index' => $i],
            ];

            // Add counter for numbered variants
            if (in_array($variant, ['with_counters', 'process'])) {
                array_unshift($contentModules, [
                    'type' => 'number_counter',
                    'role' => 'step_number',
                    'value' => $i + 1,
                    'style' => 'circle'
                ]);
            }

            // Add button for case studies
            if ($variant === 'case_studies') {
                $contentModules[] = ['type' => 'button', 'role' => 'read_more', 'style' => 'link'];
            }

            $contentColumn = [
                'width' => '1_2',
                'modules' => $contentModules
            ];

            $imageColumn = [
                'width' => '1_2',
                'modules' => [
                    ['type' => 'image', 'role' => 'beat_image', 'index' => $i]
                ]
            ];

            $rows[] = [
                'columns' => $isReversed
                    ? [$imageColumn, $contentColumn]
                    : [$contentColumn, $imageColumn],
                'vertical_align' => 'center',
            ];

            // Add divider between beats
            if ($variant === 'with_dividers' && $i < $beatCount - 1) {
                $rows[] = [
                    'columns' => [
                        [
                            'width' => '1_1',
                            'modules' => [
                                ['type' => 'divider', 'role' => 'beat_separator', 'style' => 'icon']
                            ]
                        ]
                    ]
                ];
            }
        }

        return [
            'pattern' => 'zigzag_narrative',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'narrative',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
            'composition_axis' => [
                'vertical' => 'alternating_sides',
                'horizontal' => 'content_middle',
            ]
        ];
    }

    /**
     * Progressive Disclosure - content that builds
     * Variants: numbered, accordion, tabbed, timeline
     */
    private static function composeProgressiveDisclosure(string $variant, array $context): array
    {
        $rows = [];

        // Intro section
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2'],
                        ['type' => 'text', 'role' => 'section_intro'],
                    ]
                ]
            ]
        ];

        if ($variant === 'accordion') {
            // Accordion expansion
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            [
                                'type' => 'accordion',
                                'role' => 'disclosure_content',
                                'children' => [
                                    ['type' => 'accordion_item', 'role' => 'step', 'index' => 0],
                                    ['type' => 'accordion_item', 'role' => 'step', 'index' => 1],
                                    ['type' => 'accordion_item', 'role' => 'step', 'index' => 2],
                                    ['type' => 'accordion_item', 'role' => 'step', 'index' => 3],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } elseif ($variant === 'tabbed') {
            // Tabbed content
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            [
                                'type' => 'tabs',
                                'role' => 'disclosure_content',
                                'children' => [
                                    ['type' => 'tabs_item', 'role' => 'step', 'index' => 0],
                                    ['type' => 'tabs_item', 'role' => 'step', 'index' => 1],
                                    ['type' => 'tabs_item', 'role' => 'step', 'index' => 2],
                                    ['type' => 'tabs_item', 'role' => 'step', 'index' => 3],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            // Numbered/timeline grid
            $rows[] = [
                'columns' => [
                    ['width' => '1_2', 'modules' => [
                        ['type' => 'blurb', 'role' => 'step', 'index' => 0, 'numbered' => true]
                    ]],
                    ['width' => '1_2', 'modules' => [
                        ['type' => 'blurb', 'role' => 'step', 'index' => 1, 'numbered' => true]
                    ]],
                ]
            ];
            $rows[] = [
                'columns' => [
                    ['width' => '1_2', 'modules' => [
                        ['type' => 'blurb', 'role' => 'step', 'index' => 2, 'numbered' => true]
                    ]],
                    ['width' => '1_2', 'modules' => [
                        ['type' => 'blurb', 'role' => 'step', 'index' => 3, 'numbered' => true]
                    ]],
                ]
            ];
        }

        // Synthesis/CTA
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'cta', 'role' => 'disclosure_cta', 'style' => 'subtle']
                    ]
                ]
            ]
        ];

        return [
            'pattern' => 'progressive_disclosure',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'process',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    // ========================================
    // SOCIAL PROOF PATTERNS
    // ========================================

    /**
     * Testimonial Spotlight - featured recommendations
     * Variants: featured, carousel, grid, minimal, with_context, video
     */
    private static function composeTestimonialSpotlight(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        if ($variant === 'featured' || $variant === 'minimal') {
            // Single large testimonial
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'testimonial', 'role' => 'featured_testimonial', 'style' => 'large']
                        ]
                    ]
                ]
            ];
        } elseif ($variant === 'carousel') {
            // Slider
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            [
                                'type' => 'slider',
                                'role' => 'testimonial_carousel',
                                'children' => [
                                    ['type' => 'testimonial', 'role' => 'testimonial', 'index' => 0],
                                    ['type' => 'testimonial', 'role' => 'testimonial', 'index' => 1],
                                    ['type' => 'testimonial', 'role' => 'testimonial', 'index' => 2],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        } elseif ($variant === 'grid') {
            // 3-column grid
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'testimonial', 'role' => 'testimonial', 'index' => 0]]],
                    ['width' => '1_3', 'modules' => [['type' => 'testimonial', 'role' => 'testimonial', 'index' => 1, 'featured' => true]]],
                    ['width' => '1_3', 'modules' => [['type' => 'testimonial', 'role' => 'testimonial', 'index' => 2]]],
                ]
            ];
        } elseif ($variant === 'with_context') {
            // Testimonial with metric context
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_3',
                        'modules' => [
                            ['type' => 'number_counter', 'role' => 'social_proof_metric', 'value' => '500+']
                        ]
                    ],
                    [
                        'width' => '2_3',
                        'modules' => [
                            ['type' => 'testimonial', 'role' => 'featured_testimonial']
                        ]
                    ],
                ]
            ];
        } elseif ($variant === 'video') {
            // Video testimonial
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'video', 'role' => 'video_testimonial']
                        ]
                    ],
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'testimonial', 'role' => 'featured_testimonial', 'style' => 'quote_only']
                        ]
                    ],
                ]
            ];
        }

        return [
            'pattern' => 'testimonial_spotlight',
            'variant' => $variant,
            'tension' => self::TENSION_LOW,
            'attrs' => [
                'section_type' => 'testimonials',
                'visual_context' => 'INHERIT',
                'background_style' => 'alternate',
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Trust Metrics - credibility through numbers
     * Variants: default, with_icons, timeline, logos, achievements, bar_counters
     */
    private static function composeTrustMetrics(string $variant, array $context): array
    {
        $rows = [];

        if ($variant === 'logos') {
            // Partner/client logos
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'heading', 'role' => 'trust_label', 'level' => 'h6', 'align' => 'center', 'text' => 'Trusted by'],
                        ]
                    ]
                ]
            ];
            $rows[] = [
                'columns' => [
                    ['width' => '1_5', 'modules' => [['type' => 'image', 'role' => 'logo', 'index' => 0, 'style' => 'logo']]],
                    ['width' => '1_5', 'modules' => [['type' => 'image', 'role' => 'logo', 'index' => 1, 'style' => 'logo']]],
                    ['width' => '1_5', 'modules' => [['type' => 'image', 'role' => 'logo', 'index' => 2, 'style' => 'logo']]],
                    ['width' => '1_5', 'modules' => [['type' => 'image', 'role' => 'logo', 'index' => 3, 'style' => 'logo']]],
                    ['width' => '1_5', 'modules' => [['type' => 'image', 'role' => 'logo', 'index' => 4, 'style' => 'logo']]],
                ]
            ];
        } elseif ($variant === 'bar_counters') {
            // Progress bars
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'bar_counter', 'role' => 'metric', 'index' => 0],
                            ['type' => 'bar_counter', 'role' => 'metric', 'index' => 1],
                            ['type' => 'bar_counter', 'role' => 'metric', 'index' => 2],
                            ['type' => 'bar_counter', 'role' => 'metric', 'index' => 3],
                        ]
                    ]
                ]
            ];
        } else {
            // Number counters (default, with_icons, achievements)
            $moduleType = $variant === 'achievements' ? 'circle_counter' : 'number_counter';

            $rows[] = [
                'columns' => [
                    ['width' => '1_4', 'modules' => [['type' => $moduleType, 'role' => 'metric', 'index' => 0, 'with_icon' => $variant === 'with_icons']]],
                    ['width' => '1_4', 'modules' => [['type' => $moduleType, 'role' => 'metric', 'index' => 1, 'with_icon' => $variant === 'with_icons']]],
                    ['width' => '1_4', 'modules' => [['type' => $moduleType, 'role' => 'metric', 'index' => 2, 'with_icon' => $variant === 'with_icons']]],
                    ['width' => '1_4', 'modules' => [['type' => $moduleType, 'role' => 'metric', 'index' => 3, 'with_icon' => $variant === 'with_icons']]],
                ]
            ];
        }

        // Trust metrics with contrast background = DARK context
        return [
            'pattern' => 'trust_metrics',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'stats',
                'visual_context' => 'DARK',
                'background_style' => 'contrast',
                'padding' => 'compact',
            ],
            'rows' => $rows,
        ];
    }

    // ========================================
    // PRICING PATTERNS
    // ========================================

    /**
     * Pricing Tiered - comparison of plans
     * Variants: two_plans, three_plans, highlighted_center, with_toggle
     */
    private static function composePricingTiered(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                        ['type' => 'text', 'role' => 'section_intro', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        // Toggle for monthly/annual
        if ($variant === 'with_toggle') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'toggle', 'role' => 'pricing_toggle', 'style' => 'pill']
                        ]
                    ]
                ]
            ];
        }

        // Pricing tables
        if ($variant === 'two_plans') {
            $rows[] = [
                'columns' => [
                    ['width' => '1_2', 'modules' => [['type' => 'pricing_table', 'role' => 'plan', 'index' => 0]]],
                    ['width' => '1_2', 'modules' => [['type' => 'pricing_table', 'role' => 'plan', 'index' => 1, 'featured' => true]]],
                ]
            ];
        } else {
            // Three plans (default)
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'pricing_table', 'role' => 'plan', 'index' => 0]]],
                    ['width' => '1_3', 'modules' => [['type' => 'pricing_table', 'role' => 'plan', 'index' => 1, 'featured' => in_array($variant, ['highlighted_center', 'three_plans'])]]],
                    ['width' => '1_3', 'modules' => [['type' => 'pricing_table', 'role' => 'plan', 'index' => 2]]],
                ]
            ];
        }

        return [
            'pattern' => 'pricing_tiered',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'pricing',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Pricing Comparison - feature matrix
     */
    private static function composePricingComparison(string $variant, array $context): array
    {
        // Complex feature comparison matrix - simplified for now
        return self::composePricingTiered('three_plans', $context);
    }

    // ========================================
    // INTERACTION PATTERNS
    // ========================================

    /**
     * FAQ Expandable - accordion knowledge base
     * Variants: single_column, two_column, categorized, with_search
     */
    private static function composeFaqExpandable(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        // Search for with_search variant
        if ($variant === 'with_search') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'search', 'role' => 'faq_search', 'style' => 'centered']
                        ]
                    ]
                ]
            ];
        }

        if ($variant === 'two_column' || $variant === 'categorized') {
            // Two column layout
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'heading', 'role' => 'category_title', 'level' => 'h4', 'index' => 0],
                            [
                                'type' => 'accordion',
                                'role' => 'faq_group',
                                'index' => 0,
                                'children' => [
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 0],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 1],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 2],
                                ]
                            ]
                        ]
                    ],
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'heading', 'role' => 'category_title', 'level' => 'h4', 'index' => 1],
                            [
                                'type' => 'accordion',
                                'role' => 'faq_group',
                                'index' => 1,
                                'children' => [
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 3],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 4],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 5],
                                ]
                            ]
                        ]
                    ],
                ]
            ];
        } else {
            // Single column
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            [
                                'type' => 'accordion',
                                'role' => 'faq_list',
                                'children' => [
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 0],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 1],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 2],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 3],
                                    ['type' => 'accordion_item', 'role' => 'faq', 'index' => 4],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'pattern' => 'faq_expandable',
            'variant' => $variant,
            'tension' => self::TENSION_LOW,
            'attrs' => [
                'section_type' => 'faq',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Tabbed Content - alternative views
     * Variants: horizontal, vertical, icon_tabs, use_cases
     */
    private static function composeTabbedContent(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        if ($variant === 'vertical') {
            // Vertical tabs layout
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_4',
                        'modules' => [
                            ['type' => 'tabs', 'role' => 'tab_navigation', 'style' => 'vertical']
                        ]
                    ],
                    [
                        'width' => '3_4',
                        'modules' => [
                            ['type' => 'tabs', 'role' => 'tab_content', 'style' => 'content_only',
                                'children' => [
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 0],
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 1],
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 2],
                                ]
                            ]
                        ]
                    ],
                ]
            ];
        } else {
            // Horizontal tabs (default)
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            [
                                'type' => 'tabs',
                                'role' => 'tabbed_content',
                                'style' => $variant === 'icon_tabs' ? 'icons' : 'default',
                                'children' => [
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 0],
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 1],
                                    ['type' => 'tabs_item', 'role' => 'tab', 'index' => 2],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return [
            'pattern' => 'tabbed_content',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'tabs',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    // ========================================
    // TRANSITIONAL PATTERNS
    // ========================================

    /**
     * Breathing Space - visual pause
     * Variants: minimal, quote, icon, single_metric, statement
     */
    private static function composeBreathingSpace(string $variant, array $context): array
    {
        $modules = match($variant) {
            'quote' => [
                ['type' => 'text', 'role' => 'quote', 'style' => 'blockquote', 'align' => 'center']
            ],
            'icon' => [
                ['type' => 'icon', 'role' => 'decorative_icon', 'style' => 'large', 'align' => 'center']
            ],
            'single_metric' => [
                ['type' => 'number_counter', 'role' => 'dramatic_metric', 'style' => 'large']
            ],
            'statement' => [
                ['type' => 'heading', 'role' => 'statement', 'level' => 'h3', 'align' => 'center']
            ],
            default => [
                ['type' => 'divider', 'role' => 'separator', 'style' => 'short']
            ],
        };

        return [
            'pattern' => 'breathing_space',
            'variant' => $variant,
            'tension' => self::TENSION_LOW,
            'attrs' => [
                'section_type' => 'transition',
                'visual_context' => 'INHERIT',
                'padding' => 'minimal',
                'background_style' => 'subtle_change',
            ],
            'rows' => [
                [
                    'columns' => [
                        [
                            'width' => '1_1',
                            'modules' => $modules,
                            'max_width' => '60%',
                            'align' => 'center',
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * Visual Bridge - connecting different contexts
     * Variants: arrow, timeline, gradient_text
     */
    private static function composeVisualBridge(string $variant, array $context): array
    {
        $rows = [];

        if ($variant === 'timeline') {
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'text', 'role' => 'before_state']]],
                    ['width' => '1_3', 'modules' => [['type' => 'icon', 'role' => 'arrow', 'style' => 'arrow_right']]],
                    ['width' => '1_3', 'modules' => [['type' => 'text', 'role' => 'after_state']]],
                ]
            ];
        } else {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'heading', 'role' => 'bridge_message', 'level' => 'h3', 'align' => 'center'],
                            ['type' => 'text', 'role' => 'bridge_detail', 'align' => 'center'],
                        ]
                    ]
                ]
            ];
        }

        return [
            'pattern' => 'visual_bridge',
            'variant' => $variant,
            'tension' => self::TENSION_LOW,
            'attrs' => [
                'section_type' => 'transition',
                'visual_context' => 'INHERIT',
                'background_style' => 'gradient_transition',
            ],
            'rows' => $rows,
        ];
    }

    // ========================================
    // CLOSURE PATTERNS
    // ========================================

    /**
     * Final CTA - closing call to action
     * Variants: simple, with_guarantee, two_paths, with_countdown, trial
     */
    private static function composeFinalCta(string $variant, array $context): array
    {
        $rows = [];

        // Optional trust element above CTA
        if ($variant === 'with_guarantee') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'testimonial', 'role' => 'mini_testimonial', 'style' => 'inline']
                        ]
                    ]
                ]
            ];
        }

        if ($variant === 'with_countdown') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'countdown', 'role' => 'urgency_timer'],
                        ]
                    ]
                ]
            ];
        }

        if ($variant === 'two_paths') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'cta', 'role' => 'primary_path', 'style' => 'prominent']
                        ]
                    ],
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'cta', 'role' => 'secondary_path', 'style' => 'subtle']
                        ]
                    ],
                ]
            ];
        } else {
            // Single centered CTA
            $ctaModules = [
                ['type' => 'cta', 'role' => 'final_cta', 'style' => $variant === 'trial' ? 'trial' : 'default']
            ];

            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => $ctaModules
                    ]
                ]
            ];
        }

        // Final CTA uses primary color background = PRIMARY context
        return [
            'pattern' => 'final_cta',
            'variant' => $variant,
            'tension' => self::TENSION_HIGH,
            'attrs' => [
                'section_type' => 'cta',
                'visual_context' => 'PRIMARY',
                'background_style' => 'primary_color',
            ],
            'rows' => $rows,
        ];
    }

    /**
     * Contact Gateway - multiple contact options
     * Variants: with_form, with_map, minimal, full, multi_channel
     */
    private static function composeContactGateway(string $variant, array $context): array
    {
        $rows = [];

        // Header
        $rows[] = [
            'columns' => [
                [
                    'width' => '1_1',
                    'modules' => [
                        ['type' => 'heading', 'role' => 'section_title', 'level' => 'h2', 'align' => 'center'],
                    ]
                ]
            ]
        ];

        if ($variant === 'minimal') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'contact_form', 'role' => 'contact_form', 'style' => 'centered']
                        ],
                        'max_width' => '600px',
                        'align' => 'center',
                    ]
                ]
            ];
        } elseif ($variant === 'with_map') {
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'map', 'role' => 'location_map']
                        ]
                    ],
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'contact_form', 'role' => 'contact_form']
                        ]
                    ],
                ]
            ];
        } elseif ($variant === 'multi_channel') {
            $rows[] = [
                'columns' => [
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'contact_method', 'index' => 0, 'icon' => 'mail']]],
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'contact_method', 'index' => 1, 'icon' => 'phone']]],
                    ['width' => '1_3', 'modules' => [['type' => 'blurb', 'role' => 'contact_method', 'index' => 2, 'icon' => 'message-circle']]],
                ]
            ];
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_1',
                        'modules' => [
                            ['type' => 'contact_form', 'role' => 'contact_form']
                        ]
                    ]
                ]
            ];
        } else {
            // Full (default) - form + info
            $rows[] = [
                'columns' => [
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'contact_form', 'role' => 'contact_form']
                        ]
                    ],
                    [
                        'width' => '1_2',
                        'modules' => [
                            ['type' => 'heading', 'role' => 'contact_heading', 'level' => 'h4'],
                            ['type' => 'text', 'role' => 'contact_info'],
                            ['type' => 'social_follow', 'role' => 'social_links'],
                        ]
                    ],
                ]
            ];
        }

        return [
            'pattern' => 'contact_gateway',
            'variant' => $variant,
            'tension' => self::TENSION_MEDIUM,
            'attrs' => [
                'section_type' => 'contact',
                'visual_context' => 'INHERIT',
            ],
            'rows' => $rows,
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get column width string from count
     */
    private static function getColumnWidth(int $count): string
    {
        return match($count) {
            2 => '1_2',
            3 => '1_3',
            4 => '1_4',
            5 => '1_5',
            6 => '1_6',
            default => '1_1',
        };
    }

    /**
     * Get all available patterns
     */
    public static function getAvailablePatterns(): array
    {
        return [
            'hero' => [
                'hero_asymmetric' => ['image_right', 'image_left', 'with_screenshot', 'layered'],
                'hero_centered' => ['minimal', 'with_video', 'with_counterpoint', 'dramatic'],
                'hero_split' => ['equal', 'diagonal', 'inverted_colors', 'video_background'],
            ],
            'content_flow' => [
                'grid_density' => ['features', 'services', 'team', 'masonry', 'progressive'],
                'grid_featured' => ['featured_left', 'featured_right', 'featured_top'],
                'zigzag_narrative' => ['three_beats', 'five_beats', 'with_dividers', 'with_counters', 'benefits', 'case_studies', 'process'],
                'progressive_disclosure' => ['numbered', 'accordion', 'tabbed', 'timeline'],
            ],
            'social_proof' => [
                'testimonial_spotlight' => ['featured', 'carousel', 'grid', 'minimal', 'with_context', 'video'],
                'trust_metrics' => ['default', 'with_icons', 'timeline', 'logos', 'achievements', 'bar_counters'],
            ],
            'pricing' => [
                'pricing_tiered' => ['two_plans', 'three_plans', 'highlighted_center', 'with_toggle'],
                'pricing_comparison' => ['matrix'],
            ],
            'interaction' => [
                'faq_expandable' => ['single_column', 'two_column', 'categorized', 'with_search'],
                'tabbed_content' => ['horizontal', 'vertical', 'icon_tabs', 'use_cases'],
            ],
            'transitional' => [
                'breathing_space' => ['minimal', 'quote', 'icon', 'single_metric', 'statement'],
                'visual_bridge' => ['arrow', 'timeline', 'gradient_text'],
            ],
            'closure' => [
                'final_cta' => ['simple', 'with_guarantee', 'two_paths', 'with_countdown', 'trial'],
                'contact_gateway' => ['with_form', 'with_map', 'minimal', 'full', 'multi_channel'],
            ],
        ];
    }

    /**
     * Get pattern info
     */
    public static function getPatternInfo(string $patternName): array
    {
        $info = [
            'hero_asymmetric' => [
                'name' => 'Asymmetric Hero',
                'description' => 'Strong visual anchor with content/image asymmetry',
                'tension' => self::TENSION_HIGH,
                'use_when' => 'Main message needs visual support',
            ],
            'hero_centered' => [
                'name' => 'Centered Hero',
                'description' => 'Single focused message, no distractions',
                'tension' => self::TENSION_HIGH,
                'use_when' => 'One strong, simple message',
            ],
            'hero_split' => [
                'name' => 'Split Hero',
                'description' => 'Two narratives side by side',
                'tension' => self::TENSION_HIGH,
                'use_when' => 'Contrast or dual messaging',
            ],
            'grid_density' => [
                'name' => 'Dense Grid',
                'description' => 'Multiple equal items in grid layout',
                'tension' => self::TENSION_MEDIUM,
                'use_when' => 'Features, services, team members',
            ],
            'zigzag_narrative' => [
                'name' => 'Zigzag Narrative',
                'description' => 'Alternating content/image storytelling',
                'tension' => self::TENSION_MEDIUM,
                'use_when' => 'Process explanation, benefits, story',
            ],
            'testimonial_spotlight' => [
                'name' => 'Testimonial Spotlight',
                'description' => 'Featured recommendations',
                'tension' => self::TENSION_LOW,
                'use_when' => 'Social proof, credibility',
            ],
            'trust_metrics' => [
                'name' => 'Trust Metrics',
                'description' => 'Numbers that build credibility',
                'tension' => self::TENSION_MEDIUM,
                'use_when' => 'Quantifiable achievements',
            ],
            'pricing_tiered' => [
                'name' => 'Tiered Pricing',
                'description' => 'Plan comparison',
                'tension' => self::TENSION_MEDIUM,
                'use_when' => 'Multiple pricing options',
            ],
            'faq_expandable' => [
                'name' => 'Expandable FAQ',
                'description' => 'Accordion knowledge base',
                'tension' => self::TENSION_LOW,
                'use_when' => 'Common questions, objection handling',
            ],
            'breathing_space' => [
                'name' => 'Breathing Space',
                'description' => 'Visual pause between sections',
                'tension' => self::TENSION_LOW,
                'use_when' => 'Between dense sections',
            ],
            'final_cta' => [
                'name' => 'Final CTA',
                'description' => 'Closing call to action',
                'tension' => self::TENSION_HIGH,
                'use_when' => 'Page conclusion',
            ],
            'contact_gateway' => [
                'name' => 'Contact Gateway',
                'description' => 'Multiple contact options',
                'tension' => self::TENSION_MEDIUM,
                'use_when' => 'Contact page or section',
            ],
        ];

        return $info[$patternName] ?? [];
    }

    /**
     * Validate pattern sequence
     */
    public static function validateSequence(array $sequence): array
    {
        $issues = [];
        $previous = null;

        foreach ($sequence as $index => $config) {
            $pattern = $config['pattern'];

            // Check compatibility with previous
            if ($previous) {
                $score = self::$compatibilityMatrix[$previous][$pattern] ?? 1;
                if ($score === 0) {
                    $issues[] = [
                        'type' => 'incompatible',
                        'position' => $index,
                        'message' => "Pattern '$pattern' should not follow '$previous'",
                        'suggestion' => 'Add a transitional pattern between them',
                    ];
                }
            }

            // Check tension rhythm
            if ($index > 0 && $index < count($sequence) - 1) {
                $prevTension = self::$patternTension[$previous] ?? 2;
                $currTension = self::$patternTension[$pattern] ?? 2;
                $nextPattern = $sequence[$index + 1]['pattern'] ?? null;
                $nextTension = $nextPattern ? (self::$patternTension[$nextPattern] ?? 2) : 2;

                // Three high-tension sections in a row is fatiguing
                if ($prevTension === 3 && $currTension === 3 && $nextTension === 3) {
                    $issues[] = [
                        'type' => 'tension_fatigue',
                        'position' => $index,
                        'message' => 'Three consecutive high-tension sections',
                        'suggestion' => 'Insert a breathing_space or low-tension pattern',
                    ];
                }
            }

            $previous = $pattern;
        }

        return $issues;
    }

    /**
     * Get composition sequences for all intents
     * Used by frontend to show preview of what will be generated
     *
     * @return array Map of intent => simplified pattern sequence
     */
    public static function getCompositionSequences(): array
    {
        $sequences = self::getDefaultSequences();
        $simplified = [];

        foreach ($sequences as $intent => $patternSequence) {
            $simplified[$intent] = array_map(function($config) {
                return [
                    'pattern' => $config['pattern'],
                    'variant' => $config['variant'] ?? 'default',
                    'purpose' => $config['purpose'] ?? null,
                ];
            }, $patternSequence);
        }

        return $simplified;
    }

    /**
     * Get default sequences (extracted for reuse)
     */
    private static function getDefaultSequences(): array
    {
        return [
            'product_launch' => [
                ['pattern' => 'hero_asymmetric', 'variant' => 'image_right', 'purpose' => 'capture'],
                ['pattern' => 'trust_metrics', 'variant' => 'with_icons', 'purpose' => 'credibility'],
                ['pattern' => 'zigzag_narrative', 'variant' => 'three_beats', 'purpose' => 'explain'],
                ['pattern' => 'testimonial_spotlight', 'variant' => 'featured', 'purpose' => 'proof'],
                ['pattern' => 'pricing_tiered', 'variant' => 'three_plans', 'purpose' => 'convert'],
                ['pattern' => 'faq_expandable', 'variant' => 'two_column', 'purpose' => 'reassure'],
                ['pattern' => 'final_cta', 'variant' => 'with_guarantee', 'purpose' => 'close'],
            ],
            'service_showcase' => [
                ['pattern' => 'hero_centered', 'variant' => 'with_video', 'purpose' => 'capture'],
                ['pattern' => 'grid_density', 'variant' => 'featured_first', 'purpose' => 'overview'],
                ['pattern' => 'breathing_space', 'variant' => 'quote', 'purpose' => 'pause'],
                ['pattern' => 'progressive_disclosure', 'variant' => 'numbered', 'purpose' => 'process'],
                ['pattern' => 'testimonial_spotlight', 'variant' => 'carousel', 'purpose' => 'proof'],
                ['pattern' => 'contact_gateway', 'variant' => 'with_map', 'purpose' => 'convert'],
            ],
            'brand_story' => [
                ['pattern' => 'hero_split', 'variant' => 'diagonal', 'purpose' => 'capture'],
                ['pattern' => 'zigzag_narrative', 'variant' => 'five_beats', 'purpose' => 'story'],
                ['pattern' => 'trust_metrics', 'variant' => 'timeline', 'purpose' => 'credibility'],
                ['pattern' => 'grid_density', 'variant' => 'team', 'purpose' => 'humanize'],
                ['pattern' => 'testimonial_spotlight', 'variant' => 'grid', 'purpose' => 'proof'],
                ['pattern' => 'final_cta', 'variant' => 'two_paths', 'purpose' => 'close'],
            ],
            'saas_landing' => [
                ['pattern' => 'hero_asymmetric', 'variant' => 'with_screenshot', 'purpose' => 'capture'],
                ['pattern' => 'trust_metrics', 'variant' => 'logos', 'purpose' => 'credibility'],
                ['pattern' => 'grid_density', 'variant' => 'features', 'purpose' => 'explain'],
                ['pattern' => 'tabbed_content', 'variant' => 'use_cases', 'purpose' => 'demonstrate'],
                ['pattern' => 'zigzag_narrative', 'variant' => 'benefits', 'purpose' => 'convince'],
                ['pattern' => 'pricing_tiered', 'variant' => 'highlighted_center', 'purpose' => 'convert'],
                ['pattern' => 'faq_expandable', 'variant' => 'categorized', 'purpose' => 'reassure'],
                ['pattern' => 'final_cta', 'variant' => 'trial', 'purpose' => 'close'],
            ],
            'portfolio' => [
                ['pattern' => 'hero_centered', 'variant' => 'minimal', 'purpose' => 'capture'],
                ['pattern' => 'grid_density', 'variant' => 'masonry', 'purpose' => 'showcase'],
                ['pattern' => 'breathing_space', 'variant' => 'statement', 'purpose' => 'pause'],
                ['pattern' => 'zigzag_narrative', 'variant' => 'case_studies', 'purpose' => 'detail'],
                ['pattern' => 'testimonial_spotlight', 'variant' => 'minimal', 'purpose' => 'proof'],
                ['pattern' => 'contact_gateway', 'variant' => 'minimal', 'purpose' => 'convert'],
            ],
            'agency' => [
                ['pattern' => 'hero_split', 'variant' => 'video_background', 'purpose' => 'capture'],
                ['pattern' => 'grid_density', 'variant' => 'services', 'purpose' => 'explain'],
                ['pattern' => 'trust_metrics', 'variant' => 'achievements', 'purpose' => 'credibility'],
                ['pattern' => 'zigzag_narrative', 'variant' => 'process', 'purpose' => 'educate'],
                ['pattern' => 'grid_density', 'variant' => 'team', 'purpose' => 'humanize'],
                ['pattern' => 'testimonial_spotlight', 'variant' => 'video', 'purpose' => 'proof'],
                ['pattern' => 'contact_gateway', 'variant' => 'full', 'purpose' => 'convert'],
            ],
        ];
    }
}
