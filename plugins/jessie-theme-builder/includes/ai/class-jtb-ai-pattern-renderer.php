<?php
/**
 * JTB AI Pattern Renderer
 * Converts abstract composition patterns into concrete JTB structures
 *
 * This class takes pattern blueprints from JTB_AI_Composer
 * and renders them into actual JTB section/row/column/module structures
 * with generated content.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Pattern_Renderer
{
    /**
     * Render a full page from composed patterns
     *
     * @param array $composedPatterns Output from JTB_AI_Composer::composePage()
     * @param array $context Content generation context
     * @return array JTB-compatible sections array
     */
    public static function renderPage(array $composedPatterns, array $context = []): array
    {
        $sections = [];

        foreach ($composedPatterns as $index => $pattern) {
            $patternContext = array_merge($context, [
                'section_index' => $index,
                'total_sections' => count($composedPatterns),
                'pattern_name' => $pattern['pattern'] ?? 'unknown',
                'pattern_variant' => $pattern['variant'] ?? 'default',
                'pattern_purpose' => $pattern['purpose'] ?? null,
            ]);

            $section = self::renderPattern($pattern, $patternContext);
            if (!empty($section)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Render a single pattern into JTB structure
     *
     * @param array $pattern Pattern definition from Composer
     * @param array $context Content context
     * @return array JTB section structure
     */
    public static function renderPattern(array $pattern, array $context = []): array
    {
        $patternName = $pattern['pattern'] ?? 'unknown';
        $variant = $pattern['variant'] ?? 'default';
        $patternAttrs = $pattern['attrs'] ?? [];
        $rows = $pattern['rows'] ?? [];
        $style = $context['style'] ?? 'modern';

        // ========================================
        // GOLDEN PRESET CONTRACT: Resolve visual_context
        // ========================================
        $patternVisualContext = $patternAttrs['visual_context'] ?? 'INHERIT';
        $pageTheme = $context['page_theme'] ?? 'LIGHT';

        // Resolve INHERIT to actual context based on page theme
        $resolvedContext = JTB_AI_Styles::resolveVisualContext($patternVisualContext, $pageTheme);

        // Get colors from GOLDEN PRESET based on resolved visual context
        $contextColors = JTB_AI_Styles::getContextColors($resolvedContext, $style);

        // PROPAGATE: Update context with resolved visual context and colors
        // This will flow down to all child rows, columns, and modules
        $context['visual_context'] = $resolvedContext;
        $context['colors'] = $contextColors;

        // Generate section ID
        $sectionId = self::generateId('section');

        // Build section attributes (now with visual_context in $context)
        $sectionAttrs = self::buildSectionAttrs($patternAttrs, $context);

        // Build children (rows) - context now carries visual_context and colors
        $children = [];
        foreach ($rows as $rowIndex => $rowDef) {
            $rowContext = array_merge($context, [
                'row_index' => $rowIndex,
                'is_first_row' => $rowIndex === 0,
                'is_last_row' => $rowIndex === count($rows) - 1,
            ]);

            $row = self::renderRow($rowDef, $rowContext);
            if (!empty($row)) {
                $children[] = $row;
            }
        }

        return [
            'id' => $sectionId,
            'type' => 'section',
            'attrs' => $sectionAttrs,
            'children' => $children,
            '_pattern' => $patternName,
            '_variant' => $variant,
            '_visual_context' => $resolvedContext,
        ];
    }

    /**
     * Render a row from pattern definition with complete styling
     */
    private static function renderRow(array $rowDef, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $preset = JTB_AI_Styles::getStylePreset($style, $context);
        $spacing = $preset['spacing'] ?? [];

        $columns = $rowDef['columns'] ?? [];
        $rowAttrs = [];

        // Build column layout string
        $columnWidths = array_map(fn($col) => $col['width'] ?? '1_1', $columns);
        $rowAttrs['columns'] = implode('_', $columnWidths);

        // Column gap with responsive values
        $gapSize = $rowDef['gap'] ?? 'default';
        if (!empty($rowDef['no_gap'])) {
            $rowAttrs['column_gap'] = 0;
            $rowAttrs['column_gap__tablet'] = 0;
            $rowAttrs['column_gap__phone'] = 0;
        } else {
            $gap = match($gapSize) {
                'large' => 48,
                'small' => 16,
                'none' => 0,
                default => 32,
            };
            $rowAttrs['column_gap'] = $gap;
            $rowAttrs['column_gap__tablet'] = (int)($gap * 0.75);
            $rowAttrs['column_gap__phone'] = (int)($gap * 0.5);
        }

        // Row gap (vertical spacing between rows)
        $rowAttrs['row_gap'] = 40;
        $rowAttrs['row_gap__tablet'] = 32;
        $rowAttrs['row_gap__phone'] = 24;

        // Vertical alignment
        $rowAttrs['vertical_align'] = $rowDef['vertical_align'] ?? 'top';

        // Max width constraint for content rows
        $rowAttrs['max_width'] = 1200;

        // Margin for row spacing
        $isFirstRow = $context['is_first_row'] ?? false;
        $isLastRow = $context['is_last_row'] ?? false;

        if (!$isFirstRow) {
            $rowAttrs['margin'] = ['top' => 40, 'right' => 0, 'bottom' => 0, 'left' => 0];
            $rowAttrs['margin__tablet'] = ['top' => 32, 'right' => 0, 'bottom' => 0, 'left' => 0];
            $rowAttrs['margin__phone'] = ['top' => 24, 'right' => 0, 'bottom' => 0, 'left' => 0];
        }

        // Build children (columns)
        $children = [];
        foreach ($columns as $colIndex => $colDef) {
            $colContext = array_merge($context, [
                'column_index' => $colIndex,
                'column_count' => count($columns),
            ]);

            $column = self::renderColumn($colDef, $colContext);
            if (!empty($column)) {
                $children[] = $column;
            }
        }

        return [
            'id' => self::generateId('row'),
            'type' => 'row',
            'attrs' => $rowAttrs,
            'children' => $children,
        ];
    }

    /**
     * Render a column from pattern definition with styling
     */
    private static function renderColumn(array $colDef, array $context): array
    {
        $style = $context['style'] ?? 'modern';

        // GOLDEN PRESET CONTRACT: Use colors from context (propagated from renderPattern)
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $width = $colDef['width'] ?? '1_1';
        $modules = $colDef['modules'] ?? [];

        $colAttrs = [
            'width' => $width,
        ];

        // Background handling
        if (!empty($colDef['background'])) {
            if ($colDef['background'] === 'card') {
                $colAttrs['background_color'] = '#FFFFFF';
                $colAttrs['border_radius'] = ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12];
                $colAttrs['box_shadow'] = '0 4px 6px -1px rgba(0,0,0,0.1)';
                $colAttrs['padding'] = ['top' => 32, 'right' => 24, 'bottom' => 32, 'left' => 24];
            } elseif ($colDef['background'] === 'primary') {
                $colAttrs['background_color'] = $colors['primary'] ?? '#3B82F6';
            } elseif ($colDef['background'] === 'secondary') {
                $colAttrs['background_color'] = $colors['background_alt'] ?? '#F9FAFB';
            } else {
                $colAttrs['background_type'] = $colDef['background'];
            }
        }

        // Padding
        if (!empty($colDef['padding'])) {
            $colAttrs['padding'] = self::getPaddingValue($colDef['padding']);
        }

        // Max width constraint (useful for centered content)
        if (!empty($colDef['max_width'])) {
            $colAttrs['max_width'] = $colDef['max_width'];
        }

        // Content alignment
        $colAttrs['content_align'] = $colDef['align'] ?? 'left';

        // Vertical alignment within column
        $colAttrs['vertical_align'] = $colDef['vertical_align'] ?? 'top';

        // Build children (modules)
        $children = [];
        foreach ($modules as $modIndex => $modDef) {
            $modContext = array_merge($context, [
                'module_index' => $modIndex,
                'module_role' => $modDef['role'] ?? null,
                'module_style' => $modDef['style'] ?? null,
                'item_index' => $modDef['index'] ?? $modIndex,
                'level' => $modDef['level'] ?? null,
                'align' => $modDef['align'] ?? null,
            ]);

            $module = self::renderModule($modDef, $modContext);
            if (!empty($module)) {
                $children[] = $module;
            }
        }

        return [
            'id' => self::generateId('column'),
            'type' => 'column',
            'attrs' => $colAttrs,
            'children' => $children,
        ];
    }

    /**
     * Render a module with content
     */
    private static function renderModule(array $modDef, array $context): array
    {
        $type = $modDef['type'] ?? 'text';
        $role = $modDef['role'] ?? null;
        $style = $modDef['style'] ?? null;
        $children = $modDef['children'] ?? [];

        // Generate content based on role
        $content = self::generateModuleContent($type, $role, $context);

        // Apply style modifications
        $content = self::applyStyleToContent($content, $style, $type, $context);

        // Merge with any explicit attributes from pattern
        foreach ($modDef as $key => $value) {
            if (!in_array($key, ['type', 'role', 'style', 'children', 'index'])) {
                $content[$key] = $value;
            }
        }

        // Build module structure
        $module = [
            'id' => self::generateId($type),
            'type' => $type,
            'attrs' => $content,
            'children' => [],
        ];

        // Render child modules (for accordion, tabs, slider)
        if (!empty($children)) {
            foreach ($children as $childIndex => $childDef) {
                $childContext = array_merge($context, [
                    'child_index' => $childIndex,
                    'parent_type' => $type,
                    'parent_role' => $role,
                ]);
                $child = self::renderModule($childDef, $childContext);
                if (!empty($child)) {
                    $module['children'][] = $child;
                }
            }
        }

        return $module;
    }

    // ========================================
    // CONTENT GENERATION BY ROLE
    // ========================================

    /**
     * Generate module content based on its role in the composition
     * Uses JTB_AI_Content for dynamic content generation with fallback to static pools
     */
    private static function generateModuleContent(string $type, ?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $industry = $context['industry'] ?? 'technology';
        $purpose = $context['pattern_purpose'] ?? null;
        // Use child_index if this is a child module (e.g., accordion_item), otherwise item_index
        $itemIndex = $context['child_index'] ?? $context['item_index'] ?? 0;

        // Build content context for JTB_AI_Content
        // Include module-specific index keys for proper content variation
        $contentContext = [
            'style' => $style,
            'industry' => $industry,
            'role' => $role,
            'index' => $itemIndex,
            'module_index' => $itemIndex,
            // Module-specific indices for JTB_AI_Content
            'blurb_index' => $itemIndex,
            'testimonial_index' => $itemIndex,
            'pricing_index' => $itemIndex,
            'team_index' => $itemIndex,
            'counter_index' => $itemIndex,
            'faq_index' => $itemIndex,
            'purpose' => $purpose,
            'section_index' => $context['section_index'] ?? 0,
            'child_index' => $context['child_index'] ?? 0,
            'parent_index' => $context['module_index'] ?? 0,
            'col_index' => $context['column_index'] ?? 0,
            'level' => $context['level'] ?? 'h2',
            'align' => $context['align'] ?? 'center',
        ];

        // Try to use JTB_AI_Content for ALL module types (no whitelist - dynamically supports every module)
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Content')) {
            try {
                $content = JTB_AI_Content::generateModuleContent($type, $contentContext);
                if (!empty($content)) {
                    // Merge with role-specific styling
                    return array_merge($content, self::getRoleStyling($role, $type, $context));
                }
            } catch (\Exception $e) {
                // Fall through to static generation
                error_log('JTB AI Content fallback: ' . $e->getMessage());
            }
        }

        // Fallback to static content generation
        return match($type) {
            'heading' => self::generateHeadingContent($role, $context),
            'text' => self::generateTextContent($role, $context),
            'button' => self::generateButtonContent($role, $context),
            'image' => self::generateImageContent($role, $context),
            'blurb' => self::generateBlurbContent($role, $context),
            'testimonial' => self::generateTestimonialContent($role, $context),
            'pricing_table' => self::generatePricingContent($role, $context),
            'number_counter' => self::generateCounterContent($role, 'number', $context),
            'circle_counter' => self::generateCounterContent($role, 'circle', $context),
            'bar_counter' => self::generateCounterContent($role, 'bar', $context),
            'cta' => self::generateCtaContent($role, $context),
            'accordion_item' => self::generateAccordionItemContent($role, $context),
            'tabs_item' => self::generateTabsItemContent($role, $context),
            'team_member' => self::generateTeamMemberContent($role, $context),
            'contact_form' => self::generateContactFormContent($role, $context),
            'video' => self::generateVideoContent($role, $context),
            'divider' => self::generateDividerContent($role, $context),
            'icon' => self::generateIconContent($role, $context),
            'social_follow' => self::generateSocialContent($role, $context),
            'map' => self::generateMapContent($role, $context),
            'countdown' => self::generateCountdownContent($role, $context),
            'search' => ['placeholder' => 'Search...'],
            'toggle' => self::generateToggleContent($role, $context),
            'slider' => [],
            'accordion' => [],
            'tabs' => [],
            default => [],
        };
    }

    /**
     * Get role-specific styling (colors, alignment, etc.)
     * Uses colors from context (propagated via Golden Preset Contract)
     */
    private static function getRoleStyling(?string $role, string $type, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $typography = JTB_AI_Styles::getTypography($style);
        $attrs = [];

        // ========================================
        // GOLDEN PRESET CONTRACT: Use context colors
        // ========================================
        // Colors come from context (set by renderPattern based on visual_context)
        // These are already correct for the section's visual context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        // Heading level based on role
        if ($type === 'heading') {
            if ($role === 'hero_title') {
                $attrs['level'] = 'h1';
                $attrs['font_size'] = $typography['h1_size'] ?? 48;
                // Use heading color from context (already adapted for visual_context)
                $attrs['text_color'] = $colors['heading'];
            } elseif (in_array($role, ['section_title', 'contact_heading'])) {
                $attrs['level'] = 'h2';
                $attrs['font_size'] = $typography['h2_size'] ?? 36;
                $attrs['text_color'] = $colors['heading'];
            } elseif ($role === 'trust_label') {
                $attrs['level'] = 'h6';
                $attrs['font_size'] = 14;
                $attrs['text_color'] = $colors['text_light'];
            }

            // Center align for certain roles
            if (in_array($role, ['section_title', 'hero_title', 'trust_label', 'statement', 'bridge_message'])) {
                $attrs['text_align'] = 'center';
            }
        }

        // Button styling based on role
        if ($type === 'button') {
            if (str_contains($role ?? '', 'primary') || $role === 'hero_cta') {
                $attrs['background_color'] = $colors['button_background'];
                $attrs['text_color'] = $colors['button_text'];
            } elseif (str_contains($role ?? '', 'secondary')) {
                $attrs['background_color'] = 'transparent';
                $attrs['text_color'] = $colors['primary'];
                $attrs['border_color'] = $colors['primary'];
            }
        }

        // Blurb icon color - use icon_color from context (adapts to visual context)
        if ($type === 'blurb') {
            $attrs['icon_color'] = $colors['icon_color'] ?? $colors['primary'];
            $attrs['title_color'] = $colors['heading'];
            $attrs['text_color'] = $colors['text'];
        }

        // Text module - use text color from context
        if ($type === 'text') {
            $attrs['text_color'] = $colors['text'];
        }

        // Testimonial - use appropriate colors
        if ($type === 'testimonial') {
            $attrs['text_color'] = $colors['text'];
            $attrs['author_color'] = $colors['heading'];
        }

        return $attrs;
    }

    /**
     * Generate heading content by role
     */
    private static function generateHeadingContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $industry = $context['industry'] ?? 'technology';
        $purpose = $context['pattern_purpose'] ?? 'inform';
        $sectionIndex = $context['section_index'] ?? 0;
        $level = $context['level'] ?? 'h2';

        // Role-based headlines
        $headlines = [
            // Hero headlines
            'hero_title' => [
                'technology' => ['Build the Future Today', 'Innovation Starts Here', 'Transform Your Digital World'],
                'agency' => ['Creative Solutions That Work', 'Ideas That Move Business', 'Where Vision Meets Execution'],
                'saas' => ['Work Smarter, Not Harder', 'Your Success, Simplified', 'The Platform You\'ve Been Waiting For'],
                'default' => ['Welcome to the Future', 'Experience Excellence', 'Where Quality Meets Innovation'],
            ],
            'hero_subtitle' => [
                'technology' => ['Cutting-edge solutions for modern challenges', 'Empowering teams with intelligent tools'],
                'agency' => ['Strategy, design, and development under one roof', 'Transforming brands through creative excellence'],
                'default' => ['Discover what makes us different', 'Your journey to success starts here'],
            ],

            // Section titles - now uses purpose directly with variety based on section index
            'section_title' => self::getSectionTitleByPurposeVaried($purpose, $industry, $sectionIndex),
            'category_title' => ['General', 'Technical', 'Billing', 'Support'],

            // Narrative beats
            'beat_title' => self::getBeatTitles($purpose, $context['item_index'] ?? 0),

            // Other roles
            'contact_heading' => ['Get in Touch', 'Let\'s Talk', 'Ready to Start?'],
            'trust_label' => ['Trusted by', 'Used by', 'Powering'],
            'bridge_message' => ['But that\'s not all', 'Here\'s what\'s next', 'Ready for more?'],
            'statement' => ['Quality without compromise', 'Built for the future', 'Designed to perform'],

            // CTA escalation - decision moment (stronger than hero)
            'cta_decision' => [
                'technology' => ['This Is Your Moment', 'Don\'t Let Another Day Pass', 'The Future Won\'t Wait'],
                'agency' => ['Your Competition Already Started', 'Transform Today, Lead Tomorrow', 'Make Your Move'],
                'saas' => ['Join 10,000+ Teams Who Made the Switch', 'Stop Wishing. Start Building.', 'Your Best Decision This Year'],
                'default' => ['Now Is the Time', 'Take the Leap', 'Your Success Starts Here'],
            ],
        ];

        // Get text based on role
        $texts = $headlines[$role] ?? $headlines['section_title'];
        if (is_array($texts) && isset($texts[$industry])) {
            $texts = $texts[$industry];
        }
        if (!is_array($texts)) {
            $texts = [$texts];
        }

        $index = $context['item_index'] ?? 0;
        $text = $texts[$index % count($texts)] ?? $texts[0];

        // Determine heading level
        if ($role === 'hero_title') {
            $level = 'h1';
        } elseif ($role === 'cta_decision') {
            $level = 'h2';  // Same level as hero but different message
        } elseif (in_array($role, ['section_title', 'contact_heading'])) {
            $level = 'h2';
        } elseif (in_array($role, ['beat_title', 'category_title'])) {
            $level = 'h3';
        } elseif ($role === 'trust_label') {
            $level = 'h6';
        }

        // ========================================
        // GOLDEN PRESET CONTRACT: Use colors from context
        // ========================================
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $typography = JTB_AI_Styles::getTypography($style);

        $fontSize = match($level) {
            'h1' => $typography['h1_size'] ?? 48,
            'h2' => $typography['h2_size'] ?? 36,
            'h3' => $typography['h3_size'] ?? 28,
            'h4' => 22,
            'h5' => 18,
            'h6' => 14,
            default => 36,
        };

        // Responsive font sizes
        $fontSizeTablet = (int)($fontSize * 0.85);
        $fontSizePhone = (int)($fontSize * 0.7);

        $attrs = [
            'text' => $text,
            'level' => $level,
            'font_family' => $typography['heading_font'] ?? 'Inter',
            'font_size' => $fontSize,
            'font_size__tablet' => $fontSizeTablet,
            'font_size__phone' => $fontSizePhone,
            'font_weight' => $typography['heading_weight'] ?? '700',
            // Use heading color from context (already adapted for visual_context)
            'text_color' => $colors['heading'] ?? '#111827',
            'line_height' => $typography['heading_line_height'] ?? 1.2,
        ];

        // Alignment based on context
        if (in_array($role, ['section_title', 'hero_title', 'trust_label', 'statement', 'bridge_message', 'cta_decision']) ||
            ($context['align'] ?? null) === 'center') {
            $attrs['text_align'] = 'center';
        }

        return $attrs;
    }

    /**
     * Generate text/paragraph content by role
     */
    private static function generateTextContent(?string $role, array $context): array
    {
        $industry = $context['industry'] ?? 'technology';
        $purpose = $context['pattern_purpose'] ?? 'inform';
        $itemIndex = $context['item_index'] ?? 0;

        $texts = [
            'hero_subtitle' => [
                'technology' => 'Empower your team with intelligent tools designed for the modern workplace. Streamline workflows, boost productivity, and achieve more.',
                'agency' => 'We craft digital experiences that connect brands with their audiences. From strategy to execution, we\'re your creative partner.',
                'saas' => 'Join thousands of teams who have transformed the way they work. Simple, powerful, and built for growth.',
                'default' => 'Discover a better way to achieve your goals. Our solutions are designed with your success in mind.',
            ],
            'section_intro' => [
                'features' => 'Everything you need to succeed, all in one place.',
                'testimonials' => 'Don\'t just take our word for it. Here\'s what our customers say.',
                'pricing' => 'Simple, transparent pricing that grows with you.',
                'faq' => 'Got questions? We\'ve got answers.',
                'contact' => 'We\'d love to hear from you. Reach out and let\'s start a conversation.',
                'default' => 'Learn more about what makes us different.',
            ],
            'beat_content' => self::getBeatContent($purpose, $itemIndex),
            'contact_info' => "We're here to help. Reach out through any of the channels below, and our team will get back to you within 24 hours.",
            'quote' => [
                '"The best way to predict the future is to create it."',
                '"Innovation distinguishes between a leader and a follower."',
                '"Quality is not an act, it is a habit."',
            ],
            'before_state' => 'Where you are now',
            'after_state' => 'Where you could be',

            // CTA urgency - scarcity/value summary (escalation from hero)
            'cta_urgency' => [
                'technology' => 'Every day without the right tools is a day of lost productivity. Your team deserves better.',
                'agency' => 'Your competitors are already investing in their brand. The question is: will you lead or follow?',
                'saas' => 'Free 14-day trial. No credit card. Cancel anytime. What do you have to lose?',
                'default' => 'Limited spots available. Start your free trial today and see the difference.',
            ],
        ];

        // Get text
        $textOptions = $texts[$role] ?? $texts['section_intro'];
        if (is_array($textOptions)) {
            if (isset($textOptions[$industry])) {
                $text = $textOptions[$industry];
            } elseif (isset($textOptions[$purpose])) {
                $text = $textOptions[$purpose];
            } else {
                $text = $textOptions[$itemIndex % count($textOptions)] ?? ($textOptions['default'] ?? reset($textOptions));
            }
        } else {
            $text = $textOptions;
        }

        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $attrs = [
            'content' => $text,
            'text_color' => $colors['text'] ?? '#4B5563',
            'font_size' => $role === 'quote' ? 20 : 16,
            'line_height' => 1.7,
        ];

        if ($role === 'quote') {
            $attrs['font_style'] = 'italic';
            $attrs['text_align'] = 'center';
        }

        if (in_array($role, ['section_intro', 'hero_subtitle', 'cta_urgency'])) {
            $attrs['text_align'] = 'center';
        }

        return $attrs;
    }

    /**
     * Generate button content by role
     */
    private static function generateButtonContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $buttons = [
            'primary_cta' => ['text' => 'Get Started', 'url' => '#contact', 'style' => 'primary'],
            'secondary_cta' => ['text' => 'Learn More', 'url' => '#features', 'style' => 'secondary'],
            'read_more' => ['text' => 'Read More →', 'url' => '#', 'style' => 'link'],
            'submit' => ['text' => 'Submit', 'url' => '#', 'style' => 'primary'],
            // CTA final button - stronger than hero, action-oriented
            'cta_final' => ['text' => 'Start Free Trial Now →', 'url' => '#signup', 'style' => 'primary'],
        ];

        $config = $buttons[$role] ?? $buttons['primary_cta'];

        $attrs = [
            'text' => $config['text'],
            'link_url' => $config['url'],
            'font_weight' => '600',
            'border_radius' => ['top_left' => 8, 'top_right' => 8, 'bottom_right' => 8, 'bottom_left' => 8],
        ];

        if ($config['style'] === 'primary') {
            $attrs['background_color'] = $colors['primary'] ?? '#3B82F6';
            $attrs['text_color'] = '#FFFFFF';
        } elseif ($config['style'] === 'secondary') {
            $attrs['background_color'] = $colors['background_alt'] ?? '#F3F4F6';
            $attrs['text_color'] = $colors['text'] ?? '#1F2937';
        } elseif ($config['style'] === 'link') {
            $attrs['background_color'] = 'transparent';
            $attrs['text_color'] = $colors['primary'] ?? '#3B82F6';
        }

        return $attrs;
    }

    /**
     * Generate image content by role
     */
    private static function generateImageContent(?string $role, array $context): array
    {
        $industry = $context['industry'] ?? 'technology';
        $itemIndex = $context['item_index'] ?? 0;

        // Try to get image from Pexels if configured
        $src = '';
        $alt = ucfirst(str_replace('_', ' ', $role ?? 'image'));

        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $result = null;
            if ($role === 'hero_image') {
                $result = JTB_AI_Pexels::getHeroImage(['industry' => $industry]);
            } elseif ($role === 'beat_image') {
                $result = JTB_AI_Pexels::getFeatureImage(['index' => $itemIndex, 'industry' => $industry]);
            } elseif ($role !== 'logo') {
                $result = JTB_AI_Pexels::getAboutImage(['industry' => $industry]);
            }

            if ($result && $result['ok']) {
                $src = $result['url'] ?? $result['url_large'] ?? '';
                $alt = $result['alt'] ?? $alt;
            }
        }

        // Fallback to placeholder if no image from Pexels
        if (empty($src)) {
            // Generate deterministic placeholder based on role and index
            $placeholderColor = self::getPlaceholderColor($role, $itemIndex);
            $width = ($role === 'hero_image') ? 1200 : 600;
            $height = ($role === 'hero_image') ? 600 : 400;
            $src = "https://placehold.co/{$width}x{$height}/{$placeholderColor}/ffffff?text=" . urlencode($alt);
        }

        return [
            'src' => $src,
            'alt' => $alt,
            'border_radius' => ['top_left' => 12, 'top_right' => 12, 'bottom_right' => 12, 'bottom_left' => 12],
        ];
    }

    /**
     * Get placeholder color based on role and index for visual variety
     */
    private static function getPlaceholderColor(?string $role, int $index): string
    {
        $colors = [
            'hero_image' => ['4F46E5', '7C3AED', '2563EB', '0891B2'], // Indigo/purple/blue tones
            'beat_image' => ['059669', '0D9488', '0891B2', '0284C7'], // Green/teal/blue tones
            'feature_image' => ['7C3AED', '8B5CF6', '6366F1', '4F46E5'], // Purple tones
            'default' => ['6366F1', '8B5CF6', '06B6D4', '10B981'], // Mixed
        ];

        $colorSet = $colors[$role] ?? $colors['default'];
        return $colorSet[$index % count($colorSet)];
    }

    /**
     * Generate blurb content by role
     */
    private static function generateBlurbContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $purpose = $context['pattern_purpose'] ?? 'inform';
        $itemIndex = $context['item_index'] ?? 0;
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        // Feature/service blurbs
        $features = [
            ['icon' => 'zap', 'title' => 'Lightning Fast', 'content' => 'Optimized performance that keeps your workflow moving at full speed.'],
            ['icon' => 'shield', 'title' => 'Secure by Design', 'content' => 'Enterprise-grade security protecting your data at every level.'],
            ['icon' => 'users', 'title' => 'Team Collaboration', 'content' => 'Work together seamlessly with real-time collaboration tools.'],
            ['icon' => 'trending-up', 'title' => 'Analytics & Insights', 'content' => 'Data-driven decisions with powerful analytics dashboards.'],
            ['icon' => 'globe', 'title' => 'Global Scale', 'content' => 'Infrastructure that grows with you, anywhere in the world.'],
            ['icon' => 'clock', 'title' => '24/7 Support', 'content' => 'Round-the-clock assistance from our dedicated support team.'],
            ['icon' => 'layers', 'title' => 'Integrations', 'content' => 'Connect with hundreds of tools you already use and love.'],
            ['icon' => 'code', 'title' => 'Developer Friendly', 'content' => 'Robust APIs and documentation for custom solutions.'],
        ];

        // Contact method blurbs
        $contactMethods = [
            ['icon' => 'mail', 'title' => 'Email Us', 'content' => 'hello@example.com'],
            ['icon' => 'phone', 'title' => 'Call Us', 'content' => '+1 (555) 123-4567'],
            ['icon' => 'message-circle', 'title' => 'Live Chat', 'content' => 'Available 24/7'],
        ];

        // Select based on role
        if ($role === 'contact_method') {
            $item = $contactMethods[$itemIndex % count($contactMethods)];
        } elseif ($role === 'step' && !empty($context['numbered'])) {
            $item = $features[$itemIndex % count($features)];
            $item['title'] = 'Step ' . ($itemIndex + 1) . ': ' . $item['title'];
        } else {
            $item = $features[$itemIndex % count($features)];
        }

        return [
            'font_icon' => $item['icon'],
            'title' => $item['title'],
            'content' => '<p>' . $item['content'] . '</p>',
            // GOLDEN PRESET: Use colors from context
            'icon_color' => $colors['icon_color'] ?? $colors['primary'] ?? '#3B82F6',
            'title_color' => $colors['heading'] ?? '#111827',
            'text_color' => $colors['text'] ?? '#4B5563',
            'text_orientation' => 'center',
        ];
    }

    /**
     * Generate testimonial content
     */
    private static function generateTestimonialContent(?string $role, array $context): array
    {
        $itemIndex = $context['item_index'] ?? 0;

        $testimonials = [
            [
                'content' => 'This platform has completely transformed how our team works. The efficiency gains have been remarkable.',
                'author' => 'Sarah Chen',
                'job_title' => 'CTO',
                'company' => 'TechStart Inc.',
            ],
            [
                'content' => 'Finally, a solution that actually delivers on its promises. Our productivity increased by 40% in the first month.',
                'author' => 'Michael Roberts',
                'job_title' => 'Operations Director',
                'company' => 'Global Ventures',
            ],
            [
                'content' => 'The support team is incredible. Any time we have a question, they\'re there with helpful answers.',
                'author' => 'Emily Watson',
                'job_title' => 'Product Manager',
                'company' => 'Innovate Labs',
            ],
        ];

        $item = $testimonials[$itemIndex % count($testimonials)];

        // Try to get portrait from Pexels
        $portrait = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $result = JTB_AI_Pexels::getPersonPhoto(['gender' => $itemIndex % 2 === 0 ? 'female' : 'male']);
            if ($result['ok']) {
                $portrait = $result['url'] ?? '';
            }
        }

        return [
            'content' => '<p>' . $item['content'] . '</p>',
            'author' => $item['author'],
            'job_title' => $item['job_title'],
            'company' => $item['company'],
            'portrait_url' => $portrait,
        ];
    }

    /**
     * Generate pricing table content
     */
    private static function generatePricingContent(?string $role, array $context): array
    {
        $itemIndex = $context['item_index'] ?? 0;
        $isFeatured = $context['featured'] ?? ($itemIndex === 1);

        $plans = [
            [
                'title' => 'Starter',
                'price' => '$29',
                'period' => '/month',
                'features' => "Up to 5 users\nBasic analytics\nEmail support\n5GB storage",
                'button_text' => 'Start Free Trial',
            ],
            [
                'title' => 'Professional',
                'price' => '$79',
                'period' => '/month',
                'features' => "Up to 20 users\nAdvanced analytics\nPriority support\n50GB storage\nAPI access",
                'button_text' => 'Get Started',
            ],
            [
                'title' => 'Enterprise',
                'price' => '$199',
                'period' => '/month',
                'features' => "Unlimited users\nCustom analytics\n24/7 support\nUnlimited storage\nDedicated manager",
                'button_text' => 'Contact Sales',
            ],
        ];

        $plan = $plans[$itemIndex % count($plans)];
        $plan['featured'] = $isFeatured;

        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        // Pricing tables need card backgrounds for visibility on any section
        return array_merge($plan, [
            'background_color' => $colors['card_background'] ?? '#FFFFFF',
            'title_color' => $colors['heading'] ?? '#111827',
            'text_color' => $colors['text'] ?? '#4B5563',
            'price_color' => $colors['heading'] ?? '#111827',
            'button_background_color' => $isFeatured ? ($colors['button_background'] ?? '#3B82F6') : ($colors['background_alt'] ?? '#F3F4F6'),
            'button_text_color' => $isFeatured ? ($colors['button_text'] ?? '#FFFFFF') : ($colors['text'] ?? '#1F2937'),
        ]);
    }

    /**
     * Generate counter content
     */
    private static function generateCounterContent(?string $role, string $counterType, array $context): array
    {
        $itemIndex = $context['item_index'] ?? 0;

        // CTA social proof metrics - trust signals for decision moment
        $ctaMetrics = [
            ['number' => '10K', 'title' => 'Teams Trust Us', 'suffix' => '+'],
            ['number' => '4.9', 'title' => 'Average Rating', 'suffix' => '/5'],
        ];

        $metrics = [
            ['number' => '10K', 'title' => 'Happy Customers', 'suffix' => '+'],
            ['number' => '99.9', 'title' => 'Uptime', 'suffix' => '%'],
            ['number' => '50', 'title' => 'Countries', 'suffix' => '+'],
            ['number' => '24/7', 'title' => 'Support', 'suffix' => ''],
            ['number' => '500', 'title' => 'Integrations', 'suffix' => '+'],
            ['number' => '4.9', 'title' => 'Rating', 'suffix' => '/5'],
        ];

        // Use CTA-specific metrics for social proof in CTA section
        if ($role === 'cta_social_proof') {
            $metric = $ctaMetrics[$itemIndex % count($ctaMetrics)];
        } else {
            $metric = $metrics[$itemIndex % count($metrics)];
        }

        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $attrs = [
            'number' => $metric['number'],
            'title' => $metric['title'],
            'suffix' => $metric['suffix'],
            // Use heading color for counter text
            'number_color' => $colors['heading'] ?? '#111827',
            'title_color' => $colors['text'] ?? '#4B5563',
        ];

        if ($counterType === 'bar') {
            $attrs['percent'] = intval($metric['number']) ?: 75;
            // GOLDEN PRESET: bar_color adapts to context
            $attrs['bar_color'] = $colors['accent'] ?? $colors['primary'] ?? '#3B82F6';
        } elseif ($counterType === 'circle') {
            $attrs['percent'] = intval($metric['number']) ?: 75;
            // GOLDEN PRESET: circle_color adapts to context
            $attrs['circle_color'] = $colors['accent'] ?? $colors['primary'] ?? '#3B82F6';
        }
        // number_color and title_color already set above from context

        return $attrs;
    }

    /**
     * Generate CTA content
     */
    private static function generateCtaContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $ctas = [
            'final_cta' => [
                'title' => 'Ready to Get Started?',
                'content' => '<p>Join thousands of teams already using our platform to achieve more.</p>',
                'button_text' => 'Start Your Free Trial',
            ],
            'primary_path' => [
                'title' => 'Start Building Today',
                'content' => '<p>Get instant access to all features with our 14-day free trial.</p>',
                'button_text' => 'Start Free Trial',
            ],
            'secondary_path' => [
                'title' => 'Need More Information?',
                'content' => '<p>Schedule a demo with our team to see the platform in action.</p>',
                'button_text' => 'Book a Demo',
            ],
            'disclosure_cta' => [
                'title' => 'Ready to take the next step?',
                'content' => '<p>Let\'s discuss how we can help you achieve your goals.</p>',
                'button_text' => 'Get in Touch',
            ],
        ];

        $cta = $ctas[$role] ?? $ctas['final_cta'];

        return [
            'title' => $cta['title'],
            'content' => $cta['content'],
            'button_text' => $cta['button_text'],
            'link_url' => '#contact',
            // GOLDEN PRESET: Use colors from context
            'title_color' => $colors['heading'] ?? '#111827',
            'text_color' => $colors['text'] ?? '#4B5563',
            'promo_color' => $colors['primary'] ?? '#3B82F6',
            'button_background_color' => $colors['button_background'] ?? '#3B82F6',
            'button_text_color' => $colors['button_text'] ?? '#FFFFFF',
        ];
    }

    /**
     * Generate accordion item content
     */
    private static function generateAccordionItemContent(?string $role, array $context): array
    {
        $itemIndex = $context['item_index'] ?? $context['child_index'] ?? 0;

        $faqs = [
            ['title' => 'How does the free trial work?', 'content' => 'You get full access to all features for 14 days, no credit card required. At the end of your trial, you can choose a plan that fits your needs.'],
            ['title' => 'Can I change my plan later?', 'content' => 'Absolutely! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we\'ll prorate any charges.'],
            ['title' => 'Is my data secure?', 'content' => 'Security is our top priority. We use enterprise-grade encryption, regular security audits, and comply with GDPR, SOC 2, and other standards.'],
            ['title' => 'Do you offer refunds?', 'content' => 'Yes, we offer a 30-day money-back guarantee. If you\'re not satisfied, contact us for a full refund, no questions asked.'],
            ['title' => 'What kind of support do you offer?', 'content' => 'All plans include email support. Professional and Enterprise plans get priority support and dedicated success managers.'],
            ['title' => 'Can I integrate with other tools?', 'content' => 'Yes! We offer integrations with 500+ popular tools including Slack, Salesforce, HubSpot, and more. We also have a robust API for custom integrations.'],
        ];

        $faq = $faqs[$itemIndex % count($faqs)];

        return [
            'title' => $faq['title'],
            'content' => '<p>' . $faq['content'] . '</p>',
        ];
    }

    /**
     * Generate tabs item content
     */
    private static function generateTabsItemContent(?string $role, array $context): array
    {
        $itemIndex = $context['item_index'] ?? $context['child_index'] ?? 0;

        $tabs = [
            ['title' => 'Overview', 'content' => 'A comprehensive solution designed to streamline your workflow and boost productivity across your entire organization.'],
            ['title' => 'Features', 'content' => 'Packed with powerful features including real-time collaboration, advanced analytics, automated workflows, and seamless integrations.'],
            ['title' => 'Use Cases', 'content' => 'Perfect for teams of all sizes, from startups to enterprises. Used by marketing teams, developers, project managers, and more.'],
        ];

        $tab = $tabs[$itemIndex % count($tabs)];

        return [
            'title' => $tab['title'],
            'content' => '<p>' . $tab['content'] . '</p>',
        ];
    }

    /**
     * Generate team member content
     */
    private static function generateTeamMemberContent(?string $role, array $context): array
    {
        $itemIndex = $context['item_index'] ?? 0;

        $team = [
            ['name' => 'Alex Johnson', 'position' => 'CEO & Founder', 'bio' => 'Visionary leader with 15+ years in tech.'],
            ['name' => 'Sarah Chen', 'position' => 'CTO', 'bio' => 'Engineering excellence and innovation.'],
            ['name' => 'Michael Roberts', 'position' => 'Head of Design', 'bio' => 'Creating beautiful, intuitive experiences.'],
            ['name' => 'Emily Watson', 'position' => 'Head of Marketing', 'bio' => 'Connecting brands with audiences.'],
            ['name' => 'David Kim', 'position' => 'VP Engineering', 'bio' => 'Building scalable, reliable systems.'],
            ['name' => 'Lisa Park', 'position' => 'Head of Customer Success', 'bio' => 'Ensuring every customer thrives.'],
        ];

        $member = $team[$itemIndex % count($team)];

        // Try to get photo from Pexels
        $photo = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $result = JTB_AI_Pexels::getPersonPhoto(['gender' => $itemIndex % 2 === 0 ? 'male' : 'female']);
            if ($result['ok']) {
                $photo = $result['url'] ?? '';
            }
        }

        return [
            'name' => $member['name'],
            'position' => $member['position'],
            'content' => '<p>' . $member['bio'] . '</p>',
            'image' => $photo,
        ];
    }

    /**
     * Generate contact form content
     */
    private static function generateContactFormContent(?string $role, array $context): array
    {
        return [
            'title' => 'Send us a message',
            'submit_text' => 'Send Message',
            'success_message' => 'Thank you! We\'ll be in touch soon.',
        ];
    }

    /**
     * Generate video content
     */
    private static function generateVideoContent(?string $role, array $context): array
    {
        return [
            'src' => '',
            'poster' => '',
            'autoplay' => false,
        ];
    }

    /**
     * Generate divider content
     */
    private static function generateDividerContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        return [
            'divider_style' => $role === 'icon' ? 'icon' : 'solid',
            'divider_color' => $colors['divider_color'] ?? $colors['border'] ?? '#E5E7EB',
            'divider_width' => '50%',
        ];
    }

    /**
     * Generate icon content
     */
    private static function generateIconContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        $icon = match($role) {
            'arrow', 'arrow_right' => 'arrow-right',
            'decorative_icon' => 'star',
            default => 'star',
        };

        return [
            'font_icon' => $icon,
            'icon_color' => $colors['icon_color'] ?? $colors['primary'] ?? '#3B82F6',
            'icon_size' => $role === 'decorative_icon' ? 48 : 24,
        ];
    }

    /**
     * Generate social follow content
     */
    private static function generateSocialContent(?string $role, array $context): array
    {
        return [
            'networks' => ['facebook', 'twitter', 'instagram', 'linkedin'],
        ];
    }

    /**
     * Generate map content
     */
    private static function generateMapContent(?string $role, array $context): array
    {
        return [
            'address' => '123 Business Street, San Francisco, CA 94102',
            'zoom' => 14,
            'height' => 300,
        ];
    }

    /**
     * Generate countdown content
     */
    private static function generateCountdownContent(?string $role, array $context): array
    {
        // Set to 30 days from now
        $targetDate = date('Y-m-d H:i:s', strtotime('+30 days'));

        return [
            'target_date' => $targetDate,
            'labels' => ['Days', 'Hours', 'Minutes', 'Seconds'],
        ];
    }

    /**
     * Generate toggle content (for pricing monthly/annual switch)
     */
    private static function generateToggleContent(?string $role, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        // GOLDEN PRESET: Use colors from context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);

        // Toggle is typically used for pricing period switching
        if ($role === 'pricing_toggle' || $role === 'period_toggle') {
            return [
                'title' => '',
                'open_text' => 'Monthly',
                'close_text' => 'Annual',
                'open_by_default' => true,
                'accent_color' => $colors['accent'] ?? $colors['primary'] ?? '#3B82F6',
                'text_color' => $colors['text'] ?? '#4B5563',
            ];
        }

        // Generic toggle
        return [
            'title' => 'Toggle',
            'content' => '<p>Toggle content here.</p>',
            'open_by_default' => false,
            'title_color' => $colors['heading'] ?? '#111827',
            'text_color' => $colors['text'] ?? '#4B5563',
        ];
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Build section attributes from pattern attrs with complete styling
     * Uses colors from context (set by renderPattern via Golden Preset Contract)
     */
    private static function buildSectionAttrs(array $patternAttrs, array $context): array
    {
        $style = $context['style'] ?? 'modern';
        $sectionIndex = $context['section_index'] ?? 0;
        $patternName = $context['pattern_name'] ?? 'unknown';

        // Use colors from context (set by Golden Preset Contract in renderPattern)
        // These are already resolved based on visual_context
        $colors = $context['colors'] ?? JTB_AI_Styles::getContextColors('LIGHT', $style);
        $visualContext = $context['visual_context'] ?? 'LIGHT';

        $attrs = [
            'section_type' => $patternAttrs['section_type'] ?? 'content',
            // CRITICAL: Pattern identification for AutoFix system (Stages 11-17)
            '_pattern' => $patternName,
            '_visual_context' => $visualContext,
            'visual_context' => $visualContext,
        ];

        // Padding with responsive values - larger for hero and CTA sections
        $sectionType = $patternAttrs['section_type'] ?? 'content';
        $isHero = $sectionType === 'hero' || str_contains($patternName, 'hero');
        $isCta = str_contains($patternName, 'cta') || str_contains($patternName, 'final');

        $paddingDesktop = ($isHero || $isCta) ? 100 : 80;
        $paddingTablet = ($isHero || $isCta) ? 80 : 60;
        $paddingPhone = ($isHero || $isCta) ? 60 : 40;

        if (($patternAttrs['padding'] ?? null) === 'compact') {
            $paddingDesktop = 48;
            $paddingTablet = 36;
            $paddingPhone = 24;
        } elseif (($patternAttrs['padding'] ?? null) === 'minimal') {
            $paddingDesktop = 32;
            $paddingTablet = 24;
            $paddingPhone = 16;
        } elseif (($patternAttrs['padding'] ?? null) === 'large') {
            $paddingDesktop = 120;
            $paddingTablet = 100;
            $paddingPhone = 80;
        }

        $attrs['padding'] = [
            'top' => $paddingDesktop,
            'right' => 24,
            'bottom' => $paddingDesktop,
            'left' => 24,
        ];

        $attrs['padding__tablet'] = [
            'top' => $paddingTablet,
            'right' => 20,
            'bottom' => $paddingTablet,
            'left' => 20,
        ];

        $attrs['padding__phone'] = [
            'top' => $paddingPhone,
            'right' => 16,
            'bottom' => $paddingPhone,
            'left' => 16,
        ];

        // ========================================
        // GOLDEN PRESET CONTRACT: Background colors from visual_context
        // ========================================
        // The section_background color comes from getContextColors() which
        // already knows the correct background for the visual context:
        // - LIGHT: white/light gray
        // - DARK: dark gray/navy
        // - PRIMARY: primary brand color
        $bgStyle = $patternAttrs['background_style'] ?? 'auto';

        // Use section_background from context colors as base
        $sectionBg = $colors['section_background'] ?? $colors['background'] ?? '#FFFFFF';

        if ($visualContext === 'PRIMARY' || $bgStyle === 'primary_color' || $isCta) {
            // PRIMARY context or CTA sections use primary color
            $attrs['background_color'] = $colors['primary'] ?? '#3B82F6';
            if ($isCta) {
                $attrs['background_gradient'] = 'linear-gradient(135deg, ' .
                    ($colors['primary'] ?? '#3B82F6') . ' 0%, ' .
                    ($colors['secondary'] ?? '#1E40AF') . ' 100%)';
                $attrs['use_background_gradient'] = true;
            }
        } elseif ($visualContext === 'DARK' || $bgStyle === 'contrast') {
            // DARK context uses dark section background
            $attrs['background_color'] = $sectionBg;
        } elseif ($bgStyle === 'gradient') {
            $attrs['background_gradient'] = 'linear-gradient(180deg, ' .
                ($colors['background'] ?? '#FFFFFF') . ' 0%, ' .
                ($colors['background_alt'] ?? '#F9FAFB') . ' 100%)';
            $attrs['use_background_gradient'] = true;
        } elseif ($bgStyle === 'alternate' || $bgStyle === 'auto') {
            // For LIGHT context, alternate between background colors
            $isSpecialSection = str_contains($patternName, 'testimonial') ||
                               str_contains($patternName, 'pricing') ||
                               str_contains($patternName, 'faq');

            if ($isSpecialSection) {
                $attrs['background_color'] = $colors['background_alt'] ?? '#F9FAFB';
            } elseif ($isHero) {
                $attrs['background_color'] = $colors['background'] ?? '#FFFFFF';
            } else {
                $attrs['background_color'] = $sectionIndex % 2 === 0
                    ? ($colors['background'] ?? '#FFFFFF')
                    : ($colors['background_alt'] ?? '#F9FAFB');
            }
        } else {
            $attrs['background_color'] = $sectionBg;
        }

        // Min height for hero sections
        if (!empty($patternAttrs['min_height'])) {
            $attrs['min_height'] = $patternAttrs['min_height'];
        } elseif ($isHero) {
            $attrs['min_height'] = '80vh';
            $attrs['min_height__tablet'] = '70vh';
            $attrs['min_height__phone'] = 'auto';
        }

        // Vertical alignment for hero
        if ($isHero) {
            $attrs['vertical_align'] = 'center';
        }

        // Full width
        if (!empty($patternAttrs['full_width'])) {
            $attrs['full_width'] = true;
        }

        // Inner width - content constraint
        $attrs['inner_width'] = 1200;

        // Overflow handling
        $attrs['overflow'] = 'hidden';

        return $attrs;
    }

    /**
     * Apply style modifications to content
     */
    private static function applyStyleToContent(array $content, ?string $styleName, string $type, array $context): array
    {
        if (!$styleName) {
            return $content;
        }

        // Style-specific modifications
        switch ($styleName) {
            case 'large':
                if ($type === 'testimonial') {
                    $content['font_size'] = 20;
                } elseif ($type === 'blurb') {
                    $content['icon_font_size'] = 48;
                }
                break;

            case 'card':
                $content['use_background'] = true;
                $content['background_color'] = '#FFFFFF';
                $content['box_shadow'] = '0 4px 6px rgba(0,0,0,0.1)';
                break;

            case 'centered':
                $content['text_align'] = 'center';
                $content['text_orientation'] = 'center';
                break;

            case 'icon_top':
                $content['icon_placement'] = 'top';
                break;
        }

        return $content;
    }

    /**
     * Get section title by purpose
     */
    private static function getSectionTitleByPurpose(string $purpose, string $industry): array
    {
        $titles = [
            // Primary purposes from composer
            'capture' => ['Welcome', 'Build Something Amazing', 'Get Started'],
            'explain' => ['Why Choose Us', 'What We Offer', 'Our Approach'],
            'features' => ['Powerful Features', 'Everything You Need', 'Built for Success'],
            'proof' => ['What Our Customers Say', 'Trusted by Thousands', 'Real Results'],
            'convert' => ['Simple Pricing', 'Choose Your Plan', 'Start Today'],
            'reassure' => ['Frequently Asked Questions', 'Got Questions?', 'We\'re Here to Help'],
            'educate' => ['How It Works', 'Our Process', 'Your Journey'],
            'humanize' => ['Meet the Team', 'The People Behind', 'Our Experts'],
            'credibility' => ['By the Numbers', 'Our Impact', 'Proven Results'],
            'demonstrate' => ['See It In Action', 'Use Cases', 'How It Works'],
            'convince' => ['Why It Matters', 'The Benefits', 'What You Get'],
            'overview' => ['What We Offer', 'Our Services', 'Solutions'],
            'process' => ['How It Works', 'Our Process', 'Step by Step'],
            'story' => ['Our Story', 'About Us', 'Our Journey'],
            'showcase' => ['Our Work', 'Portfolio', 'Case Studies'],
            'detail' => ['In Detail', 'Deep Dive', 'Learn More'],
            'pause' => ['', '', ''], // Breathing space - no title
            'close' => ['Ready to Start?', 'Get Started Today', 'Let\'s Talk'],
            'default' => ['Learn More', 'Discover', 'Explore'],
        ];

        return $titles[$purpose] ?? $titles['default'];
    }

    /**
     * Get section title by purpose with variation based on section index
     * This ensures different sections don't all have the same title
     */
    private static function getSectionTitleByPurposeVaried(string $purpose, string $industry, int $sectionIndex): array
    {
        $titlePool = self::getSectionTitleByPurpose($purpose, $industry);

        // Return as array with one title selected based on section index
        // This provides variety across sections
        if (!empty($titlePool)) {
            $selectedIndex = $sectionIndex % count($titlePool);
            return [$titlePool[$selectedIndex]];
        }

        return [''];
    }

    /**
     * Get beat titles for narrative patterns
     * Returns the full array so index modulo works correctly
     */
    private static function getBeatTitles(string $purpose, int $index): array
    {
        $beats = [
            'explain' => ['Understand Your Needs', 'Design the Solution', 'Implement & Iterate', 'Measure Success', 'Scale & Grow'],
            'benefits' => ['Save Time', 'Reduce Costs', 'Increase Quality', 'Scale Effortlessly', 'Stay Ahead'],
            'convince' => ['Save Time & Money', 'Boost Productivity', 'Reduce Risk', 'Scale With Ease', 'Future-Proof Your Business'],
            'demonstrate' => ['See How It Works', 'Watch It In Action', 'Real-World Examples', 'Success Stories'],
            'process' => ['Discovery', 'Strategy', 'Execution', 'Optimization', 'Growth'],
            'story' => ['Where We Started', 'The Turning Point', 'Our Mission Today', 'Looking Forward'],
            'case_studies' => ['The Challenge', 'Our Approach', 'The Solution', 'The Results', 'What\'s Next'],
            'default' => ['First', 'Then', 'Finally', 'Beyond', 'Forever'],
        ];

        // Return the full array - index modulo is applied in generateHeadingContent
        return $beats[$purpose] ?? $beats['default'];
    }

    /**
     * Get beat content for narrative patterns
     */
    private static function getBeatContent(string $purpose, int $index): string
    {
        $contents = [
            'explain' => [
                'We start by understanding your unique challenges and goals through in-depth discovery sessions.',
                'Our team crafts a tailored solution that addresses your specific needs and fits your workflow.',
                'We work closely with you to implement the solution, iterating based on your feedback.',
                'Clear metrics and reporting help you see the real impact on your business.',
                'As you grow, our platform scales with you, supporting your continued success.',
            ],
            'benefits' => [
                'Automate repetitive tasks and focus on what matters most to your business.',
                'Eliminate inefficiencies and reduce operational costs across your organization.',
                'Maintain consistent quality with built-in checks and standardized processes.',
                'Handle growth without adding complexity or overhead to your operations.',
                'Stay competitive with tools that evolve alongside industry trends.',
            ],
            'convince' => [
                'Cut hours off your workweek by automating tedious manual tasks. More time for what matters.',
                'Teams using our platform see an average 40% increase in output. Real results, not promises.',
                'Enterprise-grade security and reliability built-in. Sleep well knowing your data is protected.',
                'From startup to enterprise, our infrastructure grows with you. No migration headaches.',
                'Built on modern architecture with regular updates. Your investment is protected for years to come.',
            ],
            'demonstrate' => [
                'See exactly how the platform handles your most common workflows in real-time.',
                'Watch as tasks that used to take hours are completed in minutes.',
                'Explore real implementations from companies in your industry.',
                'Discover how teams like yours have achieved measurable results.',
            ],
            'story' => [
                'What started as a simple idea in a small garage has grown into something we\'re truly proud of.',
                'A pivotal moment changed everything - we realized there had to be a better way.',
                'Today, we\'re on a mission to empower teams worldwide with tools they actually love using.',
                'The future is bright, and we\'re just getting started. Join us on this journey.',
            ],
        ];

        $content = $contents[$purpose] ?? $contents['explain'];
        return $content[$index % count($content)];
    }

    /**
     * Get padding value from string
     */
    private static function getPaddingValue(string $size): array
    {
        $value = match($size) {
            'large' => 48,
            'small' => 16,
            'none' => 0,
            default => 24,
        };

        return ['top' => $value, 'right' => $value, 'bottom' => $value, 'left' => $value];
    }

    /**
     * Generate unique ID
     */
    private static function generateId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(8));
    }
}
