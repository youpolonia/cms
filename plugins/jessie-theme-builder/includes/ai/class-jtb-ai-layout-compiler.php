<?php
/**
 * JTB AI Layout Compiler
 * Compiles Layout AST to JTB JSON structure
 *
 * This class performs PURE TRANSFORMATION - no AI logic here.
 * It maps abstract AST elements to concrete JTB modules.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Layout_Compiler
{
    // ========================================
    // ELEMENT TO MODULE MAPPING
    // ========================================

    /**
     * Map abstract element types to JTB module types
     */
    private const ELEMENT_TO_MODULE = [
        // Text elements
        'headline' => 'heading',
        'subheadline' => 'text',
        'body_text' => 'text',
        'label' => 'text',

        // Action elements
        'cta_primary' => 'button',
        'cta_secondary' => 'button',
        'link' => 'button',

        // Visual elements
        'image_hero' => 'image',
        'image_feature' => 'image',
        'image_background' => 'image',
        'video' => 'video',
        'icon' => 'blurb',
        'divider' => 'divider',

        // Data elements
        'stat' => 'number_counter',
        'counter' => 'number_counter',
        'progress' => 'bar_counter',

        // Social proof elements
        'testimonial' => 'testimonial',
        'logo' => 'image',
        'logo_grid' => 'gallery',
        'rating' => 'text',

        // Commerce elements
        'pricing_card' => 'pricing_table',
        'feature_list' => 'text',

        // Interactive elements
        'faq_item' => 'accordion',
        'tab' => 'tabs',
        'accordion' => 'accordion',

        // Form elements
        'form' => 'contact_form',
        'newsletter' => 'contact_form',

        // Composite elements
        'card' => 'blurb',
        'blurb' => 'blurb',
        'team_member' => 'team_member',
    ];

    /**
     * Map AST section types to JTB pattern names
     * (for AutoFix Stage 11-17 compatibility)
     */
    private const SECTION_TO_PATTERN = [
        'hero' => 'hero_asymmetric',
        'features' => 'grid_density',
        'testimonials' => 'testimonial_spotlight',
        'social_proof' => 'trust_metrics',
        'stats' => 'trust_metrics',
        'pricing' => 'pricing_tiered',
        'faq' => 'faq_expandable',
        'cta' => 'final_cta',
        'contact' => 'contact_gateway',
        'about' => 'zigzag_narrative',
        'team' => 'grid_density',
        'services' => 'grid_density',
        'benefits' => 'zigzag_narrative',
        'newsletter' => 'final_cta',
        'divider' => 'breathing_space',
    ];

    /**
     * Map AST layout types to pattern variants
     */
    private const LAYOUT_TO_VARIANT = [
        'asymmetric' => 'image_right',
        'centered' => 'centered',
        'split' => 'split',
        'grid' => 'three_column',
        'alternating' => 'alternating',
        'stacked' => 'stacked',
        'masonry' => 'masonry',
    ];

    // ========================================
    // MAIN COMPILATION METHOD
    // ========================================

    /**
     * Compile Layout AST to JTB structure
     *
     * @param array $ast Layout AST
     * @param array $context Content context
     * @return array JTB sections
     */
    public static function compile(array $ast, array $context = []): array
    {
        // DEBUG: Log compile entry
        file_put_contents('/tmp/jtb_compile.log', "[compile] ENTRY - sections count: " . count($ast['sections'] ?? []) . "\n", FILE_APPEND);
        $sections = [];

        $pageContext = array_merge($context, [
            'page_goal' => $ast['goal'] ?? '',
            'page_style' => $ast['style'] ?? 'modern',
            'total_sections' => count($ast['sections'] ?? []),
        ]);

        foreach ($ast['sections'] ?? [] as $index => $sectionAST) {
            $sectionContext = array_merge($pageContext, [
                'section_index' => $index,
                'is_first' => $index === 0,
                'is_last' => $index === count($ast['sections']) - 1,
            ]);

            $section = self::compileSection($sectionAST, $sectionContext);

            if (!empty($section)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    // ========================================
    // SECTION COMPILATION
    // ========================================

    /**
     * Compile section AST node to JTB section
     *
     * @param array $sectionAST Section AST
     * @param array $context Context data
     * @return array JTB section
     */
    private static function compileSection(array $sectionAST, array $context): array
    {
        $type = $sectionAST['type'] ?? 'generic';
        $intent = $sectionAST['intent'] ?? 'explain';
        $layout = $sectionAST['layout'] ?? 'stacked';
        $weight = $sectionAST['visual_weight'] ?? 'medium';

        // Map to JTB pattern name for AutoFix compatibility
        $pattern = self::SECTION_TO_PATTERN[$type] ?? $type;
        $variant = self::LAYOUT_TO_VARIANT[$layout] ?? 'default';

        // Adjust pattern based on layout
        if ($type === 'hero') {
            $pattern = match ($layout) {
                'centered' => 'hero_centered',
                'split' => 'hero_split',
                default => 'hero_asymmetric',
            };
        }

        // Get section attributes
        $attrs = self::getSectionAttrs($sectionAST, $context);

        // Build rows from columns
        $rows = self::compileRows($sectionAST['columns'] ?? [], array_merge($context, [
            'section_type' => $type,
            'section_intent' => $intent,
            'section_layout' => $layout,
        ]));

        return [
            'id' => self::generateId('section'),
            'type' => 'section',
            // AutoFix Stage 11-17 attributes at root level
            '_pattern' => $pattern,
            '_visual_context' => self::getVisualContext($sectionAST),
            '_ast_type' => $type,
            '_ast_intent' => $intent,
            '_ast_layout' => $layout,
            '_variant' => $variant,
            'attrs' => $attrs,
            'children' => $rows,
        ];
    }

    /**
     * Get section attributes
     *
     * @param array $sectionAST Section AST
     * @param array $context Context
     * @return array Section attributes
     */
    private static function getSectionAttrs(array $sectionAST, array $context): array
    {
        $style = $context['page_style'] ?? 'modern';
        $weight = $sectionAST['visual_weight'] ?? 'medium';
        $layout = $sectionAST['layout'] ?? 'stacked';

        // Base attributes
        $attrs = [
            'full_width' => true,
        ];

        // Padding based on weight
        $attrs['padding'] = match ($weight) {
            'high' => ['top' => 120, 'right' => 0, 'bottom' => 120, 'left' => 0],
            'medium' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
            'low' => ['top' => 40, 'right' => 0, 'bottom' => 40, 'left' => 0],
            default => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0],
        };

        // Background based on visual context
        $visualContext = self::getVisualContext($sectionAST);
        if ($visualContext === 'DARK') {
            $attrs['background_color'] = '#1a1a2e';
            $attrs['text_color'] = '#ffffff';
        } elseif ($visualContext === 'ACCENT') {
            $attrs['background_color'] = '#f8fafc';
        }

        return $attrs;
    }

    /**
     * Determine visual context from section AST
     *
     * @param array $sectionAST Section AST
     * @return string LIGHT, DARK, or ACCENT
     */
    private static function getVisualContext(array $sectionAST): string
    {
        $weight = $sectionAST['visual_weight'] ?? 'medium';
        $type = $sectionAST['type'] ?? '';

        // High weight sections (hero, cta) can be dark
        if ($weight === 'high') {
            // First hero is usually dark, final CTA is dark
            if ($type === 'hero' || $type === 'cta') {
                return 'DARK';
            }
        }

        // Low weight sections often have subtle background
        if ($weight === 'low') {
            return 'ACCENT';
        }

        return 'LIGHT';
    }

    // ========================================
    // ROW COMPILATION
    // ========================================

    /**
     * Compile columns to JTB row(s)
     *
     * @param array $columns Columns AST
     * @param array $context Context
     * @return array JTB rows
     */
    private static function compileRows(array $columns, array $context): array
    {
        if (empty($columns)) {
            return [];
        }

        // Group columns into rows
        // If section has more than one set of columns (like header + content),
        // they should be in separate rows
        $rows = [];
        $currentRowColumns = [];
        $currentRowWidth = 0;

        foreach ($columns as $column) {
            $width = $column['width'] ?? 12;

            // Check if this column starts a new row
            if ($currentRowWidth + $width > 12 && !empty($currentRowColumns)) {
                // Create row from current columns
                $rows[] = self::createRow($currentRowColumns, $context);
                $currentRowColumns = [];
                $currentRowWidth = 0;
            }

            $currentRowColumns[] = $column;
            $currentRowWidth += $width;
        }

        // Create final row
        if (!empty($currentRowColumns)) {
            $rows[] = self::createRow($currentRowColumns, $context);
        }

        return $rows;
    }

    /**
     * Create a JTB row from columns
     *
     * @param array $columns Columns AST
     * @param array $context Context
     * @return array JTB row
     */
    private static function createRow(array $columns, array $context): array
    {
        $style = $context['page_style'] ?? 'modern';

        // Build column layout string
        $widths = array_map(fn($c) => $c['width'] ?? 6, $columns);
        $columnLayout = self::widthsToLayoutString($widths);

        // Row attributes
        $rowAttrs = [
            'columns' => $columnLayout,
            'column_gap' => 32,
            'row_gap' => 40,
            'vertical_align' => 'center',
        ];

        // Compile columns to JTB
        $children = [];
        foreach ($columns as $colIndex => $colAST) {
            $colContext = array_merge($context, [
                'column_index' => $colIndex,
                'total_columns' => count($columns),
            ]);

            $column = self::compileColumn($colAST, $colContext);
            $children[] = $column;
        }

        return [
            'id' => self::generateId('row'),
            'type' => 'row',
            'attrs' => $rowAttrs,
            'children' => $children,
        ];
    }

    // ========================================
    // COLUMN COMPILATION
    // ========================================

    /**
     * Compile column AST to JTB column
     *
     * @param array $colAST Column AST
     * @param array $context Context
     * @return array JTB column
     */
    private static function compileColumn(array $colAST, array $context): array
    {
        $style = $context['page_style'] ?? 'modern';

        // Column attributes
        $colAttrs = [
            'vertical_align' => 'top',
        ];

        // Track element type counters for variety (same type elements get incrementing indices)
        $typeCounters = [];

        // Compile elements to modules
        $children = [];
        $globalModuleIndex = 0; // Global counter for ALL modules in this column

        foreach ($colAST['elements'] ?? [] as $elemIndex => $elemAST) {
            $elemType = $elemAST['type'] ?? 'unknown';

            // Initialize counter for this type if not exists
            if (!isset($typeCounters[$elemType])) {
                $typeCounters[$elemType] = 0;
            }

            $elemContext = array_merge($context, [
                'element_index' => $elemIndex,
                'element_role' => $elemAST['role'] ?? null,
            ]);

            // Handle repeated elements (count > 1)
            $count = $elemAST['count'] ?? 1;

            for ($i = 0; $i < $count; $i++) {
                // Use type-specific counter for unique indexing
                $typeSpecificIndex = $typeCounters[$elemType];

                // Calculate truly unique index across entire page
                // Using prime multipliers (7 and 3) ensures good distribution with small pools
                // section * 7 + column * 3 + type_counter avoids modulo collisions
                $sectionIdx = $context['section_index'] ?? 0;
                $columnIdx = $context['column_index'] ?? 0;
                $uniqueGlobalIndex = ($sectionIdx * 7) + ($columnIdx * 3) + $typeSpecificIndex;

                $iterContext = array_merge($elemContext, [
                    'iteration' => $uniqueGlobalIndex, // Truly unique across page
                    'type_iteration' => $typeSpecificIndex, // Type-specific counter
                    'loop_iteration' => $i, // Original loop iteration
                    'total_iterations' => $count,
                    'global_module_index' => $globalModuleIndex,
                ]);

                $module = self::compileElement($elemAST, $iterContext);
                if (!empty($module)) {
                    $children[] = $module;
                }

                // Increment counters
                $typeCounters[$elemType]++;
                $globalModuleIndex++;
            }
        }

        return [
            'id' => self::generateId('column'),
            'type' => 'column',
            'attrs' => $colAttrs,
            'children' => $children,
        ];
    }

    // ========================================
    // ELEMENT COMPILATION
    // ========================================

    /**
     * Compile element AST to JTB module
     *
     * @param array $elemAST Element AST
     * @param array $context Context
     * @return array JTB module
     */
    private static function compileElement(array $elemAST, array $context): array
    {
        $type = $elemAST['type'] ?? 'body_text';
        $role = $elemAST['role'] ?? null;
        $variant = $elemAST['variant'] ?? 'default';

        // Get content from AST (generated by AI)
        $astContent = $elemAST['content'] ?? [];
        $astStyle = $elemAST['style'] ?? [];

        // Map to JTB module type
        $moduleType = self::ELEMENT_TO_MODULE[$type] ?? 'text';

        // PRIORITY: Use content from AST if available, otherwise fallback to pools
        if (!empty($astContent)) {
            // AI provided content - use it directly
            $content = self::mapASTContentToModule($moduleType, $type, $astContent, $astStyle, $context);
        } else {
            // Fallback to generated content (old behavior)
            $content = self::generateModuleContent($moduleType, $type, array_merge($context, [
                'element_role' => $role,
                'element_variant' => $variant,
            ]));
        }

        // Apply variant styling
        $content = self::applyVariantStyling($content, $moduleType, $variant, $context);

        return [
            'id' => self::generateId($moduleType),
            'type' => $moduleType,
            'attrs' => $content,
            'children' => [],
        ];
    }

    /**
     * Map AST content to JTB module attributes
     * This converts AI-generated content to the format expected by JTB modules
     */
    private static function mapASTContentToModule(string $moduleType, string $elementType, array $astContent, array $astStyle, array $context): array
    {
        $attrs = [];

        switch ($moduleType) {
            case 'heading':
                $attrs = [
                    'text' => $astContent['text'] ?? 'Heading',
                    'level' => $elementType === 'headline' ? 'h2' : 'h3',
                    'font_size' => $astStyle['font_size'] ?? ($elementType === 'headline' ? 42 : 24),
                    'font_weight' => $astStyle['font_weight'] ?? '700',
                    'text_color' => $astStyle['text_color'] ?? '#111827',
                ];
                // First headline in first section = h1
                if ($elementType === 'headline' && ($context['section_index'] ?? 0) === 0 && ($context['element_index'] ?? 0) <= 1) {
                    $attrs['level'] = 'h1';
                    $attrs['font_size'] = $astStyle['font_size'] ?? 52;
                }
                break;

            case 'text':
                $text = $astContent['text'] ?? $astContent['description'] ?? 'Text content';
                $attrs = [
                    'content' => "<p>{$text}</p>",
                    'font_size' => $astStyle['font_size'] ?? 16,
                    'text_color' => $astStyle['text_color'] ?? '#4b5563',
                    'line_height' => 1.6,
                ];
                break;

            case 'button':
                $attrs = [
                    'text' => $astContent['text'] ?? 'Click Here',
                    'link_url' => $astContent['url'] ?? '#',
                    'button_style' => $elementType === 'cta_primary' ? 'filled' : 'outlined',
                    'size' => 'large',
                    'background_color' => $astStyle['background_color'] ?? '#4f46e5',
                    'text_color' => $astStyle['text_color'] ?? '#ffffff',
                ];
                break;

            case 'blurb':
                $attrs = [
                    'title' => $astContent['title'] ?? 'Feature',
                    'content' => $astContent['description'] ?? 'Feature description',
                    'font_icon' => $astContent['icon'] ?? 'star',
                    'icon_color' => $astStyle['text_color'] ?? '#4f46e5',
                ];
                break;

            case 'testimonial':
                $attrs = [
                    'content' => $astContent['text'] ?? 'Great product!',
                    'author' => $astContent['author'] ?? 'Customer',
                    'job_title' => $astContent['role'] ?? '',
                    'company' => $astContent['company'] ?? '',
                ];
                break;

            case 'number_counter':
                $attrs = [
                    'number' => $astContent['value'] ?? '100',
                    'title' => $astContent['label'] ?? 'Stat',
                    'percent_sign' => strpos($astContent['value'] ?? '', '%') !== false,
                ];
                break;

            case 'pricing_table':
                $features = $astContent['features'] ?? ['Feature 1', 'Feature 2', 'Feature 3'];
                $attrs = [
                    'title' => $astContent['title'] ?? 'Plan',
                    'price' => $astContent['price'] ?? '$0',
                    'period' => $astContent['period'] ?? '/month',
                    'content' => implode("\n", $features),
                    'button_text' => 'Get Started',
                ];
                break;

            case 'accordion':
                $attrs = [
                    'title' => $astContent['question'] ?? 'Question?',
                    'content' => $astContent['answer'] ?? 'Answer.',
                    'open' => false,
                ];
                break;

            case 'image':
                $attrs = [
                    'src' => 'https://placehold.co/800x600/e2e8f0/64748b?text=' . urlencode($astContent['image_prompt'] ?? 'Image'),
                    'alt' => $astContent['image_prompt'] ?? 'Image',
                    'width' => '100%',
                ];
                break;

            default:
                // Generic fallback
                $attrs = [
                    'content' => $astContent['text'] ?? $astContent['description'] ?? 'Content',
                ];
        }

        // Apply any style overrides
        if (!empty($astStyle['background_color'])) {
            $attrs['background_color'] = $astStyle['background_color'];
        }
        if (!empty($astStyle['text_color']) && !isset($attrs['text_color'])) {
            $attrs['text_color'] = $astStyle['text_color'];
        }
        if (!empty($astStyle['padding'])) {
            $attrs['padding'] = $astStyle['padding'];
        }
        if (!empty($astStyle['border_radius'])) {
            $attrs['border_radius'] = $astStyle['border_radius'];
        }

        return $attrs;
    }

    /**
     * Generate module content using AI Content generator
     *
     * @param string $moduleType JTB module type
     * @param string $elementType AST element type
     * @param array $context Context
     * @return array Module attributes
     */
    private static function generateModuleContent(string $moduleType, string $elementType, array $context): array
    {
        // Map iteration to module-specific index keys expected by JTB_AI_Content
        $iteration = $context['iteration'] ?? 0;
        $sectionIndex = $context['section_index'] ?? 0;

        // Create enriched context with all index variants
        $enrichedContext = array_merge($context, [
            // Generic index (used as fallback)
            'index' => $iteration,
            'module_index' => $iteration,

            // Module-specific indices (for pool selection variety)
            'blurb_index' => ($sectionIndex * 10) + $iteration,
            'testimonial_index' => ($sectionIndex * 5) + $iteration,
            'pricing_index' => $iteration,  // pricing usually 0, 1, 2 for plans
            'team_index' => ($sectionIndex * 6) + $iteration,
            'counter_index' => ($sectionIndex * 4) + $iteration,
            'faq_index' => ($sectionIndex * 6) + $iteration,

            // Purpose/role from element
            'role' => $context['element_role'] ?? $elementType,
            'purpose' => self::intentToPurpose($context['section_intent'] ?? 'explain'),
        ]);

        // Use existing JTB_AI_Content if available
        if (class_exists('JessieThemeBuilder\\JTB_AI_Content')) {
            $content = JTB_AI_Content::generateModuleContent($moduleType, $enrichedContext);
            if (!empty($content)) {
                file_put_contents('/tmp/jtb_source.log', "[JTB_AI_Content] $moduleType sec=" . ($enrichedContext['section_index'] ?? 'N/A') . " elem=" . ($enrichedContext['element_index'] ?? 'N/A') . " iter=" . ($enrichedContext['iteration'] ?? 'N/A') . " blurb_idx=" . ($enrichedContext['blurb_index'] ?? 'N/A') . "\n", FILE_APPEND);
                return $content;
            }
        }

        // Fallback content generation
        file_put_contents('/tmp/jtb_source.log', "[FALLBACK] $moduleType (elem_idx=" . ($enrichedContext['element_index'] ?? 'N/A') . ")\n", FILE_APPEND);
        return self::generateFallbackContent($moduleType, $elementType, $enrichedContext);
    }

    /**
     * Map AST intent to content purpose
     */
    private static function intentToPurpose(string $intent): string
    {
        return match ($intent) {
            'capture' => 'capture',
            'explain' => 'explain',
            'prove' => 'proof',
            'convince' => 'convince',
            'convert' => 'convert',
            'reassure' => 'reassure',
            'connect' => 'connect',
            default => 'explain',
        };
    }

    /**
     * Generate fallback content when AI Content is not available
     *
     * @param string $moduleType Module type
     * @param string $elementType Element type
     * @param array $context Context
     * @return array Module attributes
     */
    private static function generateFallbackContent(string $moduleType, string $elementType, array $context): array
    {
        $role = $context['element_role'] ?? '';
        $sectionType = $context['section_type'] ?? '';
        $intent = $context['section_intent'] ?? '';

        switch ($moduleType) {
            case 'heading':
                return self::generateHeadingContent($elementType, $role, $context);

            case 'text':
                return self::generateTextContent($elementType, $role, $context);

            case 'button':
                return self::generateButtonContent($elementType, $role, $context);

            case 'image':
                return self::generateImageContent($elementType, $role, $context);

            case 'blurb':
                return self::generateBlurbContent($role, $context);

            case 'testimonial':
                return self::generateTestimonialContent($context);

            case 'number_counter':
                return self::generateCounterContent($context);

            case 'pricing_table':
                return self::generatePricingContent($context);

            case 'accordion':
                return self::generateAccordionContent($context);

            case 'team_member':
                return self::generateTeamMemberContent($context);

            case 'contact_form':
                return self::generateFormContent($context);

            case 'gallery':
                return self::generateGalleryContent($context);

            case 'video':
                return self::generateVideoContent($context);

            case 'divider':
                return ['style' => 'solid', 'color' => '#e5e7eb', 'width' => 100];

            default:
                return ['content' => 'Content placeholder'];
        }
    }

    // ========================================
    // CONTENT GENERATORS
    // ========================================

    private static function generateHeadingContent(string $elementType, string $role, array $context): array
    {
        $sectionType = $context['section_type'] ?? '';
        $isFirst = $context['is_first'] ?? false;
        $sectionIndex = $context['section_index'] ?? 0;

        // Use iteration directly - it's already unique per element (calculated in compileColumn)
        // iteration = sectionIndex * 100 + columnIndex * 10 + typeCounter
        $iteration = $context['iteration'] ?? 0;

        // For variety index, we just use iteration directly
        // This ensures each element in different columns gets different content
        $elementIndex = $iteration;

        // Determine heading level
        $level = match (true) {
            $elementType === 'headline' && $isFirst => 'h1',
            $elementType === 'headline' => 'h2',
            $elementType === 'subheadline' => 'h3',
            default => 'h2',
        };

        // Heading pools for variety
        $heroHeadlines = [
            'Transform Your Business Today',
            'Build Something Extraordinary',
            'Innovation Meets Excellence',
            'The Future of Work Is Here',
            'Unlock Your True Potential',
            'Elevate Your Digital Experience',
        ];

        $featureHeadlines = [
            'Powerful Features Built for You',
            'Everything You Need to Succeed',
            'Tools That Drive Results',
            'Capabilities That Scale',
            'Features That Matter Most',
        ];

        $ctaHeadlines = [
            'Ready to Get Started?',
            'Take the Next Step Today',
            'Join Thousands of Happy Users',
            'Start Your Journey Now',
            'Transform Your Workflow Today',
        ];

        // Generate text based on role and section with variety (using elementIndex for uniqueness)
        $text = match ($role) {
            'value_proposition' => $heroHeadlines[$elementIndex % count($heroHeadlines)],
            'section_title' => match ($sectionType) {
                'features' => $featureHeadlines[$elementIndex % count($featureHeadlines)],
                'testimonials' => ['What Our Customers Say', 'Trusted by Thousands', 'Success Stories', 'Real Results'][$elementIndex % 4],
                'pricing' => ['Simple, Transparent Pricing', 'Fair Pricing for Everyone', 'Choose Your Plan', 'Pricing That Scales'][$elementIndex % 4],
                'faq' => ['Frequently Asked Questions', 'Got Questions?', 'Common Questions Answered', 'Everything You Need to Know'][$elementIndex % 4],
                'team' => ['Meet Our Team', 'The People Behind the Product', 'Our Talented Team', 'Leadership Team'][$elementIndex % 4],
                'contact' => ['Get in Touch', 'Let\'s Connect', 'Reach Out Today', 'We\'d Love to Hear From You'][$elementIndex % 4],
                'about' => ['Our Story', 'About Us', 'Who We Are', 'Our Mission'][$elementIndex % 4],
                'services' => ['Our Services', 'What We Offer', 'How We Help', 'Solutions for You'][$elementIndex % 4],
                default => 'Discover More',
            },
            'cta_headline' => $ctaHeadlines[$elementIndex % count($ctaHeadlines)],
            'benefit_title' => ['Why Choose Us', 'The Difference', 'What Sets Us Apart', 'Our Advantages'][$elementIndex % 4],
            'about_title' => ['About Our Company', 'Our Journey', 'The Story Behind Us', 'Our Vision'][$elementIndex % 4],
            'contact_title' => ['Let\'s Connect', 'Get in Touch', 'Contact Us', 'Reach Out'][$elementIndex % 4],
            default => 'Section Heading',
        };

        return [
            'text' => $text,
            'level' => $level,
            'font_size' => $level === 'h1' ? 48 : ($level === 'h2' ? 36 : 24),
            'font_weight' => '700',
            'text_align' => 'left',
        ];
    }

    private static function generateTextContent(string $elementType, string $role, array $context): array
    {
        $sectionType = $context['section_type'] ?? '';
        $sectionIndex = $context['section_index'] ?? 0;

        // Use iteration directly - it's already unique per element
        $iteration = $context['iteration'] ?? 0;
        $elementIndex = $iteration;

        // Text pools for variety
        $benefitSummaries = [
            'Streamline your workflow and achieve your goals faster than ever.',
            'Boost productivity with powerful tools designed for modern teams.',
            'Save time and resources while delivering exceptional results.',
            'Transform how you work with intelligent automation.',
            'Experience the next level of efficiency and collaboration.',
        ];

        $sectionSubtitles = [
            'Everything you need to succeed, all in one platform.',
            'Trusted by industry leaders worldwide.',
            'Built for teams that demand excellence.',
            'Designed to help you achieve more.',
            'The smarter way to get things done.',
        ];

        $ctaSubheadlines = [
            'Join thousands of satisfied customers today.',
            'Start your free trial and see the difference.',
            'No credit card required. Get started in minutes.',
            'Be part of the community that\'s changing the game.',
            'Transform your workflow starting today.',
        ];

        $text = match ($role) {
            'benefit_summary' => $benefitSummaries[$elementIndex % count($benefitSummaries)],
            'section_subtitle' => $sectionSubtitles[$elementIndex % count($sectionSubtitles)],
            'pricing_intro' => ['Choose the plan that fits your needs.', 'Transparent pricing, no hidden fees.', 'Start free, upgrade when ready.', 'Plans that scale with your growth.'][$elementIndex % 4],
            'benefit_description' => $benefitSummaries[($elementIndex + 1) % count($benefitSummaries)],
            'company_story' => ['Founded with a mission to revolutionize the industry.', 'We\'ve helped thousands achieve their goals.', 'Our journey started with a simple idea.', 'Driven by innovation, powered by passion.'][$elementIndex % 4],
            'contact_intro' => ['We\'d love to hear from you.', 'Let\'s start a conversation.', 'Ready to discuss your project?', 'Our team is here to help.'][$elementIndex % 4],
            'cta_subheadline' => $ctaSubheadlines[$elementIndex % count($ctaSubheadlines)],
            'section_intro' => ['Trusted by leading companies worldwide.', 'Join industry leaders who trust us.', 'Proven results across industries.', 'Empowering businesses since day one.'][$elementIndex % 4],
            default => 'Discover how we can help you achieve your goals and transform your business.',
        };

        return [
            'content' => "<p>{$text}</p>",
            'font_size' => $elementType === 'subheadline' ? 18 : 16,
            'line_height' => 1.6,
        ];
    }

    private static function generateButtonContent(string $elementType, string $role, array $context): array
    {
        $isPrimary = $elementType === 'cta_primary';
        $sectionIndex = $context['section_index'] ?? 0;
        $sectionType = $context['section_type'] ?? '';

        // Use iteration directly - it's already unique per element
        $iteration = $context['iteration'] ?? 0;
        $elementIndex = $iteration;

        // Button text pools for variety
        $primaryButtons = [
            'Get Started Free',
            'Start Your Trial',
            'Try It Now',
            'Begin Today',
            'Get Started',
            'Start Building',
        ];

        $secondaryButtons = [
            'Learn More',
            'See How It Works',
            'View Demo',
            'Explore Features',
            'Read More',
            'Watch Video',
        ];

        $ctaButtons = [
            'Start Free Trial',
            'Get Started Now',
            'Join for Free',
            'Claim Your Spot',
            'Unlock Access',
        ];

        $text = match ($role) {
            'main_action' => $primaryButtons[$elementIndex % count($primaryButtons)],
            'alternative_action' => $secondaryButtons[$elementIndex % count($secondaryButtons)],
            'cta_action' => $ctaButtons[$elementIndex % count($ctaButtons)],
            default => $isPrimary
                ? $primaryButtons[$elementIndex % count($primaryButtons)]
                : $secondaryButtons[$elementIndex % count($secondaryButtons)],
        };

        return [
            'text' => $text,
            'link_url' => '#',
            'button_style' => $isPrimary ? 'filled' : 'outlined',
            'size' => 'large',
            'full_width' => false,
        ];
    }

    private static function generateImageContent(string $elementType, string $role, array $context): array
    {
        $industry = $context['industry'] ?? 'technology';

        // Use Pexels if available
        if (class_exists('JessieThemeBuilder\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $image = match ($elementType) {
                'image_hero' => JTB_AI_Pexels::getHeroImage(['industry' => $industry]),
                'image_feature' => JTB_AI_Pexels::getFeatureImage(['industry' => $industry]),
                default => JTB_AI_Pexels::getAboutImage(['industry' => $industry]),
            };

            if ($image && !empty($image['src']['large'])) {
                return [
                    'src' => $image['src']['large'],
                    'alt' => $image['alt'] ?? 'Image',
                    'width' => '100%',
                ];
            }
        }

        // Placeholder
        return [
            'src' => 'https://placehold.co/800x600/e2e8f0/64748b?text=Image',
            'alt' => 'Placeholder image',
            'width' => '100%',
        ];
    }

    private static function generateBlurbContent(string $role, array $context): array
    {
        // Use enriched blurb_index for variety across sections
        $index = $context['blurb_index'] ?? $context['iteration'] ?? 0;

        $features = [
            ['title' => 'Lightning Fast', 'text' => 'Optimized for speed with sub-second response times.', 'icon' => 'zap'],
            ['title' => 'Enterprise Security', 'text' => 'Bank-grade encryption and SOC 2 compliance.', 'icon' => 'shield'],
            ['title' => 'Always Available', 'text' => 'Our team is here to help you succeed 24/7.', 'icon' => 'headphones'],
            ['title' => 'Seamless Integration', 'text' => 'Connect with 100+ tools you already use.', 'icon' => 'link'],
            ['title' => 'Real-time Sync', 'text' => 'Changes sync instantly across all devices.', 'icon' => 'refresh-cw'],
            ['title' => 'Smart Analytics', 'text' => 'Deep insights with custom dashboards.', 'icon' => 'bar-chart-2'],
            ['title' => 'Global Scale', 'text' => 'Infrastructure that grows with your business.', 'icon' => 'globe'],
            ['title' => 'API First', 'text' => 'Full REST API for seamless integrations.', 'icon' => 'code'],
        ];

        $feature = $features[$index % count($features)];

        return [
            'title' => $feature['title'],
            'content' => "<p>{$feature['text']}</p>",
            'font_icon' => $feature['icon'],
            'icon_color' => '#3b82f6',
            'icon_font_size' => 48,
        ];
    }

    private static function generateTestimonialContent(array $context): array
    {
        // Use enriched testimonial_index for variety
        $index = $context['testimonial_index'] ?? $context['iteration'] ?? 0;

        $testimonials = [
            ['name' => 'Sarah Johnson', 'title' => 'CEO, TechStart Inc.', 'content' => 'This platform has completely transformed how we work. Our team productivity increased by 40% in just the first month.'],
            ['name' => 'Michael Chen', 'title' => 'CTO, InnovateCo', 'content' => 'The best decision we made this year. The support team is incredible and the product just works.'],
            ['name' => 'Emily Rodriguez', 'title' => 'Director of Operations, Global Solutions', 'content' => 'We\'ve tried many solutions but nothing comes close. It\'s intuitive, powerful, and our clients love it.'],
            ['name' => 'David Kim', 'title' => 'Founder, StartupX', 'content' => 'Outstanding value for money. We saved thousands in the first quarter alone.'],
            ['name' => 'Amanda Foster', 'title' => 'Marketing Director, BrandWorks', 'content' => 'The analytics features alone are worth the investment. Now we have visibility we never had before.'],
            ['name' => 'James Wilson', 'title' => 'IT Manager, Enterprise Solutions', 'content' => 'Implementation was smooth and the team was incredibly helpful throughout the process.'],
        ];

        $testimonial = $testimonials[$index % count($testimonials)];

        // Different colors for variety
        $colors = ['4F46E5', '7C3AED', '059669', '0891B2', 'DC2626', 'D97706'];
        $color = $colors[$index % count($colors)];
        $initials = substr($testimonial['name'], 0, 1);

        return [
            'content' => "<p>\"{$testimonial['content']}\"</p>",
            'author' => $testimonial['name'],
            'job_title' => $testimonial['title'],
            'portrait_url' => "https://placehold.co/100x100/{$color}/ffffff?text=" . urlencode($initials),
        ];
    }

    private static function generateCounterContent(array $context): array
    {
        // Use enriched counter_index for variety
        $index = $context['counter_index'] ?? $context['iteration'] ?? 0;

        $stats = [
            ['number' => 99, 'suffix' => '%', 'title' => 'Customer Satisfaction'],
            ['number' => 10, 'suffix' => 'K+', 'title' => 'Happy Customers'],
            ['number' => 500, 'suffix' => '+', 'title' => 'Projects Delivered'],
            ['number' => 24, 'suffix' => '/7', 'title' => 'Support Available'],
            ['number' => 150, 'suffix' => '+', 'title' => 'Countries Served'],
            ['number' => 50, 'suffix' => 'M+', 'title' => 'Tasks Completed'],
            ['number' => 4.9, 'suffix' => '★', 'title' => 'Average Rating'],
            ['number' => 98, 'suffix' => '%', 'title' => 'Uptime Guarantee'],
        ];

        $stat = $stats[$index % count($stats)];

        return [
            'number' => $stat['number'],
            'suffix' => $stat['suffix'],
            'title' => $stat['title'],
            'animation' => true,
        ];
    }

    private static function generatePricingContent(array $context): array
    {
        $iteration = $context['iteration'] ?? 0;
        $variant = $context['element_variant'] ?? 'standard';

        $plans = [
            ['name' => 'Starter', 'price' => 19, 'features' => ['5 Projects', '10GB Storage', 'Email Support']],
            ['name' => 'Professional', 'price' => 49, 'features' => ['Unlimited Projects', '100GB Storage', 'Priority Support', 'Advanced Analytics']],
            ['name' => 'Enterprise', 'price' => 99, 'features' => ['Everything in Pro', 'Unlimited Storage', '24/7 Support', 'Custom Integrations', 'Dedicated Manager']],
        ];

        $plan = $plans[$iteration % count($plans)];

        return [
            'title' => $plan['name'],
            'price' => $plan['price'],
            'currency' => '$',
            'period' => '/month',
            'features' => implode("\n", array_map(fn($f) => "• {$f}", $plan['features'])),
            'button_text' => 'Get Started',
            'featured' => $variant === 'featured' || $iteration === 1,
        ];
    }

    private static function generateAccordionContent(array $context): array
    {
        // Use enriched faq_index for variety
        $index = $context['faq_index'] ?? $context['iteration'] ?? 0;
        $iteration = $context['iteration'] ?? 0;

        $faqs = [
            ['q' => 'How do I get started?', 'a' => 'Getting started is easy! Simply sign up for a free account, and you\'ll be guided through our quick setup process. Most users are up and running within minutes.'],
            ['q' => 'Is there a free trial?', 'a' => 'Yes! We offer a 14-day free trial with full access to all features. No credit card required. You can upgrade to a paid plan at any time.'],
            ['q' => 'Can I cancel anytime?', 'a' => 'Absolutely. There are no long-term contracts or commitments. You can cancel your subscription at any time, and you\'ll retain access until the end of your billing period.'],
            ['q' => 'What payment methods do you accept?', 'a' => 'We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers for annual plans.'],
            ['q' => 'Do you offer refunds?', 'a' => 'Yes, we offer a 30-day money-back guarantee. If you\'re not satisfied with our service, contact us within 30 days for a full refund.'],
            ['q' => 'Is my data secure?', 'a' => 'Security is our top priority. We use industry-standard encryption, regular security audits, and are SOC 2 compliant. Your data is safe with us.'],
            ['q' => 'Can I upgrade or downgrade my plan?', 'a' => 'Yes, you can change your plan at any time. When you upgrade, the new features are available immediately. Downgrades take effect at the next billing cycle.'],
            ['q' => 'Do you offer team or enterprise plans?', 'a' => 'Absolutely! We have team plans for growing businesses and custom enterprise solutions for larger organizations. Contact our sales team for details.'],
            ['q' => 'What kind of support do you offer?', 'a' => 'We provide 24/7 email support for all users, priority chat support for Pro plans, and dedicated account managers for Enterprise customers.'],
            ['q' => 'Can I import my existing data?', 'a' => 'Yes, we support importing data from most major platforms. Our migration team can assist with complex migrations for Enterprise customers.'],
        ];

        $faq = $faqs[$index % count($faqs)];

        return [
            'title' => $faq['q'],
            'content' => "<p>{$faq['a']}</p>",
            'open' => $iteration === 0,
        ];
    }

    private static function generateTeamMemberContent(array $context): array
    {
        // Use enriched team_index for variety
        $index = $context['team_index'] ?? $context['iteration'] ?? 0;

        $members = [
            ['name' => 'Alex Morgan', 'role' => 'CEO & Founder', 'bio' => 'Visionary leader with 15+ years in tech.'],
            ['name' => 'Sarah Chen', 'role' => 'CTO', 'bio' => 'Engineering expert passionate about scalable solutions.'],
            ['name' => 'Michael Ross', 'role' => 'Head of Design', 'bio' => 'Award-winning designer focused on user experience.'],
            ['name' => 'Emily Watson', 'role' => 'VP of Marketing', 'bio' => 'Growth strategist with proven track record.'],
            ['name' => 'David Kim', 'role' => 'Head of Product', 'bio' => 'Product visionary obsessed with customer success.'],
            ['name' => 'Lisa Johnson', 'role' => 'Head of Sales', 'bio' => 'Relationship builder who turns prospects into partners.'],
        ];

        $member = $members[$index % count($members)];

        // Different colors for variety
        $colors = ['4F46E5', '7C3AED', '059669', '0891B2', 'DC2626', 'D97706'];
        $color = $colors[$index % count($colors)];
        $initials = substr($member['name'], 0, 1);

        return [
            'name' => $member['name'],
            'position' => $member['role'],
            'description' => "<p>{$member['bio']}</p>",
            'image_url' => "https://placehold.co/300x300/{$color}/ffffff?text=" . urlencode($initials),
        ];
    }

    private static function generateFormContent(array $context): array
    {
        return [
            'title' => 'Send us a message',
            'fields' => [
                ['type' => 'text', 'label' => 'Name', 'required' => true],
                ['type' => 'email', 'label' => 'Email', 'required' => true],
                ['type' => 'textarea', 'label' => 'Message', 'required' => true],
            ],
            'submit_text' => 'Send Message',
            'success_message' => 'Thank you! We\'ll be in touch soon.',
        ];
    }

    private static function generateGalleryContent(array $context): array
    {
        return [
            'images' => [
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+1', 'alt' => 'Partner 1'],
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+2', 'alt' => 'Partner 2'],
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+3', 'alt' => 'Partner 3'],
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+4', 'alt' => 'Partner 4'],
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+5', 'alt' => 'Partner 5'],
                ['src' => 'https://placehold.co/200x80/e2e8f0/64748b?text=Logo+6', 'alt' => 'Partner 6'],
            ],
            'columns' => 6,
            'gap' => 24,
        ];
    }

    private static function generateVideoContent(array $context): array
    {
        return [
            'src' => '',
            'poster' => 'https://placehold.co/1280x720/1a1a2e/ffffff?text=Video+Preview',
            'autoplay' => false,
            'controls' => true,
        ];
    }

    // ========================================
    // VARIANT STYLING
    // ========================================

    /**
     * Apply variant-specific styling to module content
     *
     * @param array $content Module content
     * @param string $moduleType Module type
     * @param string $variant Variant name
     * @param array $context Context
     * @return array Styled content
     */
    private static function applyVariantStyling(array $content, string $moduleType, string $variant, array $context): array
    {
        $style = $context['page_style'] ?? 'modern';

        // Apply style-specific tweaks
        if (class_exists('JessieThemeBuilder\\JTB_AI_Styles')) {
            $styleAttrs = match ($moduleType) {
                'heading' => JTB_AI_Styles::getHeadingStyles($style, $context),
                'text' => JTB_AI_Styles::getTextStyles($style, $context),
                'button' => JTB_AI_Styles::getButtonStyles($style, ['variant' => $variant]),
                'blurb' => JTB_AI_Styles::getBlurbStyles($style, $context),
                'testimonial' => JTB_AI_Styles::getTestimonialStyles($style, $context),
                'pricing_table' => JTB_AI_Styles::getPricingStyles($style, ['featured' => $variant === 'featured']),
                default => [],
            };

            if (!empty($styleAttrs)) {
                $content = array_merge($content, $styleAttrs);
            }
        }

        return $content;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Convert width array to JTB column layout string
     *
     * @param array $widths Array of column widths (1-12)
     * @return string Layout string (e.g., "7_12_5_12")
     */
    private static function widthsToLayoutString(array $widths): string
    {
        $parts = [];
        foreach ($widths as $w) {
            $parts[] = $w . '_12';
        }
        return implode('_', $parts);
    }

    /**
     * Generate unique ID
     *
     * @param string $prefix ID prefix
     * @return string Unique ID
     */
    private static function generateId(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(4));
    }
}
