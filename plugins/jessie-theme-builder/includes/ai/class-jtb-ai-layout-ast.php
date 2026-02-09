<?php
/**
 * JTB AI Layout AST
 * Abstract Syntax Tree schema for AI-generated layouts
 *
 * This class defines an abstract representation of page layouts
 * that is independent of JTB's module system. AI generates AST,
 * then the Compiler transforms it to JTB JSON.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Layout_AST
{
    // ========================================
    // NODE TYPES
    // ========================================

    const NODE_PAGE = 'page';
    const NODE_SECTION = 'section';
    const NODE_ROW = 'row';
    const NODE_COLUMN = 'column';
    const NODE_ELEMENT = 'element';

    // ========================================
    // SECTION INTENTS (Why the section exists)
    // ========================================

    /** Capture attention - Hero sections */
    const INTENT_CAPTURE = 'capture';

    /** Explain what we do - Features, services */
    const INTENT_EXPLAIN = 'explain';

    /** Build trust - Testimonials, stats, logos */
    const INTENT_PROVE = 'prove';

    /** Convince to act - Benefits, comparisons */
    const INTENT_CONVINCE = 'convince';

    /** Drive conversion - Pricing, CTA */
    const INTENT_CONVERT = 'convert';

    /** Remove objections - FAQ, guarantees */
    const INTENT_REASSURE = 'reassure';

    /** Enable contact - Forms, contact info */
    const INTENT_CONNECT = 'connect';

    /** Provide breathing room - Dividers, spacing */
    const INTENT_BREATHE = 'breathe';

    // ========================================
    // LAYOUT TYPES (Visual arrangement)
    // ========================================

    /** Unequal columns, content emphasis */
    const LAYOUT_ASYMMETRIC = 'asymmetric';

    /** Centered content, full width */
    const LAYOUT_CENTERED = 'centered';

    /** Equal two-column split */
    const LAYOUT_SPLIT = 'split';

    /** Multiple equal columns */
    const LAYOUT_GRID = 'grid';

    /** Left-right alternating content */
    const LAYOUT_ALTERNATING = 'alternating';

    /** Vertical stacked elements */
    const LAYOUT_STACKED = 'stacked';

    /** Masonry/Pinterest style */
    const LAYOUT_MASONRY = 'masonry';

    // ========================================
    // VISUAL WEIGHT (Section importance)
    // ========================================

    const WEIGHT_HIGH = 'high';       // Hero, final CTA (dark backgrounds OK)
    const WEIGHT_MEDIUM = 'medium';   // Features, testimonials
    const WEIGHT_LOW = 'low';         // FAQ, dividers

    // ========================================
    // ABSTRACT ELEMENT TYPES
    // These are NOT JTB modules - they're abstract concepts
    // that the Compiler maps to specific modules
    // ========================================

    // Text elements
    const ELEM_HEADLINE = 'headline';
    const ELEM_SUBHEADLINE = 'subheadline';
    const ELEM_BODY_TEXT = 'body_text';
    const ELEM_LABEL = 'label';

    // Action elements
    const ELEM_CTA_PRIMARY = 'cta_primary';
    const ELEM_CTA_SECONDARY = 'cta_secondary';
    const ELEM_LINK = 'link';

    // Visual elements
    const ELEM_IMAGE_HERO = 'image_hero';
    const ELEM_IMAGE_FEATURE = 'image_feature';
    const ELEM_IMAGE_BACKGROUND = 'image_background';
    const ELEM_VIDEO = 'video';
    const ELEM_ICON = 'icon';
    const ELEM_DIVIDER = 'divider';

    // Data elements
    const ELEM_STAT = 'stat';
    const ELEM_COUNTER = 'counter';
    const ELEM_PROGRESS = 'progress';

    // Social proof elements
    const ELEM_TESTIMONIAL = 'testimonial';
    const ELEM_LOGO = 'logo';
    const ELEM_LOGO_GRID = 'logo_grid';
    const ELEM_RATING = 'rating';

    // Commerce elements
    const ELEM_PRICING_CARD = 'pricing_card';
    const ELEM_FEATURE_LIST = 'feature_list';

    // Interactive elements
    const ELEM_FAQ_ITEM = 'faq_item';
    const ELEM_TAB = 'tab';
    const ELEM_ACCORDION = 'accordion';

    // Form elements
    const ELEM_FORM = 'form';
    const ELEM_INPUT = 'input';
    const ELEM_NEWSLETTER = 'newsletter';

    // Composite elements
    const ELEM_CARD = 'card';
    const ELEM_BLURB = 'blurb';
    const ELEM_TEAM_MEMBER = 'team_member';

    // ========================================
    // SECTION TYPE PRESETS
    // Common section configurations
    // ========================================

    const SECTION_TYPES = [
        'hero' => [
            'default_intent' => self::INTENT_CAPTURE,
            'default_layout' => self::LAYOUT_ASYMMETRIC,
            'default_weight' => self::WEIGHT_HIGH,
            'typical_elements' => ['headline', 'subheadline', 'cta_primary', 'image_hero'],
        ],
        'features' => [
            'default_intent' => self::INTENT_EXPLAIN,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'blurb', 'icon'],
        ],
        'testimonials' => [
            'default_intent' => self::INTENT_PROVE,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['testimonial', 'rating'],
        ],
        'social_proof' => [
            'default_intent' => self::INTENT_PROVE,
            'default_layout' => self::LAYOUT_CENTERED,
            'default_weight' => self::WEIGHT_LOW,
            'typical_elements' => ['headline', 'logo_grid'],
        ],
        'stats' => [
            'default_intent' => self::INTENT_PROVE,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['stat', 'counter'],
        ],
        'pricing' => [
            'default_intent' => self::INTENT_CONVERT,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'pricing_card'],
        ],
        'faq' => [
            'default_intent' => self::INTENT_REASSURE,
            'default_layout' => self::LAYOUT_STACKED,
            'default_weight' => self::WEIGHT_LOW,
            'typical_elements' => ['headline', 'faq_item'],
        ],
        'cta' => [
            'default_intent' => self::INTENT_CONVERT,
            'default_layout' => self::LAYOUT_CENTERED,
            'default_weight' => self::WEIGHT_HIGH,
            'typical_elements' => ['headline', 'subheadline', 'cta_primary'],
        ],
        'contact' => [
            'default_intent' => self::INTENT_CONNECT,
            'default_layout' => self::LAYOUT_SPLIT,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'body_text', 'form'],
        ],
        'about' => [
            'default_intent' => self::INTENT_EXPLAIN,
            'default_layout' => self::LAYOUT_ASYMMETRIC,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'body_text', 'image_feature'],
        ],
        'team' => [
            'default_intent' => self::INTENT_PROVE,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'team_member'],
        ],
        'services' => [
            'default_intent' => self::INTENT_EXPLAIN,
            'default_layout' => self::LAYOUT_GRID,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'card', 'icon'],
        ],
        'benefits' => [
            'default_intent' => self::INTENT_CONVINCE,
            'default_layout' => self::LAYOUT_ALTERNATING,
            'default_weight' => self::WEIGHT_MEDIUM,
            'typical_elements' => ['headline', 'body_text', 'image_feature'],
        ],
        'newsletter' => [
            'default_intent' => self::INTENT_CONVERT,
            'default_layout' => self::LAYOUT_CENTERED,
            'default_weight' => self::WEIGHT_LOW,
            'typical_elements' => ['headline', 'subheadline', 'newsletter'],
        ],
        'divider' => [
            'default_intent' => self::INTENT_BREATHE,
            'default_layout' => self::LAYOUT_CENTERED,
            'default_weight' => self::WEIGHT_LOW,
            'typical_elements' => ['divider'],
        ],
    ];

    // ========================================
    // VALIDATION METHODS
    // ========================================

    /**
     * Validate AST structure
     *
     * @param array $ast The AST to validate
     * @return array Validation result with 'valid', 'errors', 'warnings'
     */
    public static function validate(array $ast): array
    {
        $errors = [];
        $warnings = [];

        // Check root structure
        if (empty($ast)) {
            $errors[] = 'AST is empty';
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Check for goal (optional but recommended)
        if (empty($ast['goal'])) {
            $warnings[] = 'Missing page goal description';
        }

        // Check sections array
        if (!isset($ast['sections']) || !is_array($ast['sections'])) {
            $errors[] = 'Missing or invalid sections array';
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        if (empty($ast['sections'])) {
            $errors[] = 'Sections array is empty';
            return ['valid' => false, 'errors' => $errors, 'warnings' => $warnings];
        }

        // Validate each section
        foreach ($ast['sections'] as $index => $section) {
            $sectionErrors = self::validateSection($section, $index);
            $errors = array_merge($errors, $sectionErrors['errors']);
            $warnings = array_merge($warnings, $sectionErrors['warnings']);
        }

        // Check section count
        $sectionCount = count($ast['sections']);
        if ($sectionCount < 2) {
            $warnings[] = 'Page has only ' . $sectionCount . ' section(s), consider adding more';
        }
        if ($sectionCount > 12) {
            $warnings[] = 'Page has ' . $sectionCount . ' sections, consider reducing for better UX';
        }

        // Check for hero at start
        $firstSection = $ast['sections'][0] ?? null;
        if ($firstSection && ($firstSection['type'] ?? '') !== 'hero') {
            $warnings[] = 'First section is not a hero - consider starting with hero';
        }

        // Check for CTA at end
        $lastSection = end($ast['sections']);
        if ($lastSection && !in_array($lastSection['type'] ?? '', ['cta', 'contact', 'newsletter'])) {
            $warnings[] = 'Last section is not a conversion element (cta/contact/newsletter)';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Validate a single section
     *
     * @param array $section Section to validate
     * @param int $index Section index for error messages
     * @return array Errors and warnings
     */
    private static function validateSection(array $section, int $index): array
    {
        $errors = [];
        $warnings = [];
        $prefix = "Section[$index]";

        // Check required fields
        if (empty($section['type'])) {
            $errors[] = "$prefix: Missing section type";
        }

        // Validate intent if present
        $validIntents = [
            self::INTENT_CAPTURE, self::INTENT_EXPLAIN, self::INTENT_PROVE,
            self::INTENT_CONVINCE, self::INTENT_CONVERT, self::INTENT_REASSURE,
            self::INTENT_CONNECT, self::INTENT_BREATHE
        ];
        if (!empty($section['intent']) && !in_array($section['intent'], $validIntents)) {
            $warnings[] = "$prefix: Unknown intent '{$section['intent']}'";
        }

        // Validate layout if present
        $validLayouts = [
            self::LAYOUT_ASYMMETRIC, self::LAYOUT_CENTERED, self::LAYOUT_SPLIT,
            self::LAYOUT_GRID, self::LAYOUT_ALTERNATING, self::LAYOUT_STACKED,
            self::LAYOUT_MASONRY
        ];
        if (!empty($section['layout']) && !in_array($section['layout'], $validLayouts)) {
            $warnings[] = "$prefix: Unknown layout '{$section['layout']}'";
        }

        // Validate visual_weight if present
        $validWeights = [self::WEIGHT_HIGH, self::WEIGHT_MEDIUM, self::WEIGHT_LOW];
        if (!empty($section['visual_weight']) && !in_array($section['visual_weight'], $validWeights)) {
            $warnings[] = "$prefix: Unknown visual_weight '{$section['visual_weight']}'";
        }

        // Validate columns if present
        if (isset($section['columns'])) {
            if (!is_array($section['columns'])) {
                $errors[] = "$prefix: Columns must be an array";
            } else {
                foreach ($section['columns'] as $colIndex => $column) {
                    $colErrors = self::validateColumn($column, $index, $colIndex);
                    $errors = array_merge($errors, $colErrors['errors']);
                    $warnings = array_merge($warnings, $colErrors['warnings']);
                }

                // Check column widths sum to 12
                $totalWidth = array_sum(array_map(fn($c) => $c['width'] ?? 0, $section['columns']));
                if ($totalWidth !== 12 && $totalWidth > 0) {
                    $warnings[] = "$prefix: Column widths sum to $totalWidth, should be 12";
                }
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /**
     * Validate a single column
     *
     * @param array $column Column to validate
     * @param int $sectionIndex Section index
     * @param int $colIndex Column index
     * @return array Errors and warnings
     */
    private static function validateColumn(array $column, int $sectionIndex, int $colIndex): array
    {
        $errors = [];
        $warnings = [];
        $prefix = "Section[$sectionIndex].Column[$colIndex]";

        // Check width
        if (!isset($column['width'])) {
            $errors[] = "$prefix: Missing column width";
        } elseif (!is_numeric($column['width']) || $column['width'] < 1 || $column['width'] > 12) {
            $errors[] = "$prefix: Invalid width (must be 1-12)";
        }

        // Check elements
        if (!isset($column['elements']) || !is_array($column['elements'])) {
            $warnings[] = "$prefix: Missing or invalid elements array";
        } elseif (empty($column['elements'])) {
            $warnings[] = "$prefix: Empty elements array";
        } else {
            foreach ($column['elements'] as $elemIndex => $element) {
                if (!isset($element['type'])) {
                    $errors[] = "$prefix.Element[$elemIndex]: Missing element type";
                }
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    // ========================================
    // JSON SCHEMA FOR AI
    // ========================================

    /**
     * Get JSON Schema for AI prompt
     * This schema tells AI how to structure the AST output
     *
     * @return array JSON Schema
     */
    public static function getJsonSchema(): array
    {
        return [
            'type' => 'object',
            'required' => ['sections'],
            'properties' => [
                'goal' => [
                    'type' => 'string',
                    'description' => 'Brief description of the page goal (e.g., "SaaS landing page to convert visitors to trial signups")'
                ],
                'style' => [
                    'type' => 'string',
                    'enum' => ['modern', 'minimal', 'bold', 'elegant', 'playful', 'corporate'],
                    'description' => 'Overall visual style of the page'
                ],
                'sections' => [
                    'type' => 'array',
                    'minItems' => 2,
                    'maxItems' => 10,
                    'description' => 'Array of page sections from top to bottom',
                    'items' => [
                        'type' => 'object',
                        'required' => ['type', 'columns'],
                        'properties' => [
                            'type' => [
                                'type' => 'string',
                                'enum' => array_keys(self::SECTION_TYPES),
                                'description' => 'Section type (hero, features, testimonials, etc.)'
                            ],
                            'intent' => [
                                'type' => 'string',
                                'enum' => ['capture', 'explain', 'prove', 'convince', 'convert', 'reassure', 'connect', 'breathe'],
                                'description' => 'Purpose of this section in the user journey'
                            ],
                            'layout' => [
                                'type' => 'string',
                                'enum' => ['asymmetric', 'centered', 'split', 'grid', 'alternating', 'stacked'],
                                'description' => 'Visual arrangement of content'
                            ],
                            'visual_weight' => [
                                'type' => 'string',
                                'enum' => ['high', 'medium', 'low'],
                                'description' => 'Visual importance - high allows dark backgrounds'
                            ],
                            'columns' => [
                                'type' => 'array',
                                'description' => 'Columns in this section (widths must sum to 12)',
                                'items' => [
                                    'type' => 'object',
                                    'required' => ['width', 'elements'],
                                    'properties' => [
                                        'width' => [
                                            'type' => 'integer',
                                            'minimum' => 1,
                                            'maximum' => 12,
                                            'description' => 'Column width in 12-column grid'
                                        ],
                                        'elements' => [
                                            'type' => 'array',
                                            'description' => 'Elements in this column',
                                            'items' => [
                                                'type' => 'object',
                                                'required' => ['type'],
                                                'properties' => [
                                                    'type' => [
                                                        'type' => 'string',
                                                        'enum' => [
                                                            'headline', 'subheadline', 'body_text', 'label',
                                                            'cta_primary', 'cta_secondary', 'link',
                                                            'image_hero', 'image_feature', 'video', 'icon', 'divider',
                                                            'stat', 'counter', 'progress',
                                                            'testimonial', 'logo', 'logo_grid', 'rating',
                                                            'pricing_card', 'feature_list',
                                                            'faq_item', 'tab', 'accordion',
                                                            'form', 'newsletter',
                                                            'card', 'blurb', 'team_member'
                                                        ],
                                                        'description' => 'Element type'
                                                    ],
                                                    'content' => [
                                                        'type' => 'object',
                                                        'description' => 'Content data for this element - REQUIRED for all elements',
                                                        'properties' => [
                                                            'text' => ['type' => 'string', 'description' => 'Main text content (for headlines, body text, buttons)'],
                                                            'title' => ['type' => 'string', 'description' => 'Title (for cards, blurbs, testimonials)'],
                                                            'description' => ['type' => 'string', 'description' => 'Description text'],
                                                            'author' => ['type' => 'string', 'description' => 'Author name (for testimonials)'],
                                                            'role' => ['type' => 'string', 'description' => 'Job title/role (for testimonials, team)'],
                                                            'company' => ['type' => 'string', 'description' => 'Company name'],
                                                            'value' => ['type' => 'string', 'description' => 'Numeric value (for stats, counters)'],
                                                            'label' => ['type' => 'string', 'description' => 'Label text (for stats, form fields)'],
                                                            'price' => ['type' => 'string', 'description' => 'Price (for pricing cards)'],
                                                            'period' => ['type' => 'string', 'description' => 'Billing period (for pricing)'],
                                                            'features' => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'List of features'],
                                                            'question' => ['type' => 'string', 'description' => 'FAQ question'],
                                                            'answer' => ['type' => 'string', 'description' => 'FAQ answer'],
                                                            'icon' => ['type' => 'string', 'description' => 'Icon name (feather icons)'],
                                                            'image_prompt' => ['type' => 'string', 'description' => 'Description for image generation/search']
                                                        ]
                                                    ],
                                                    'style' => [
                                                        'type' => 'object',
                                                        'description' => 'Style overrides for this element',
                                                        'properties' => [
                                                            'background_color' => ['type' => 'string', 'description' => 'Background color (hex)'],
                                                            'text_color' => ['type' => 'string', 'description' => 'Text color (hex)'],
                                                            'font_size' => ['type' => 'integer', 'description' => 'Font size in px'],
                                                            'font_weight' => ['type' => 'string', 'description' => 'Font weight (400, 500, 600, 700)'],
                                                            'padding' => ['type' => 'string', 'description' => 'Padding (e.g., "20px", "10px 20px")'],
                                                            'border_radius' => ['type' => 'integer', 'description' => 'Border radius in px']
                                                        ]
                                                    ],
                                                    'role' => [
                                                        'type' => 'string',
                                                        'description' => 'Semantic role (e.g., "value_proposition", "product_screenshot")'
                                                    ],
                                                    'count' => [
                                                        'type' => 'integer',
                                                        'minimum' => 1,
                                                        'maximum' => 12,
                                                        'description' => 'Number of items (for repeatable elements like testimonials)'
                                                    ],
                                                    'variant' => [
                                                        'type' => 'string',
                                                        'description' => 'Style variant (e.g., "primary", "outlined", "featured")'
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Get JSON Schema as formatted string for prompt
     *
     * @return string Formatted JSON Schema
     */
    public static function getJsonSchemaString(): string
    {
        return json_encode(self::getJsonSchema(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    // ========================================
    // FACTORY METHODS
    // ========================================

    /**
     * Create empty page AST
     *
     * @param string $goal Page goal description
     * @param array $meta Additional metadata
     * @return array Page AST
     */
    public static function createPage(string $goal = '', array $meta = []): array
    {
        return array_merge([
            'goal' => $goal,
            'style' => 'modern',
            'sections' => []
        ], $meta);
    }

    /**
     * Create section AST node
     *
     * @param string $type Section type
     * @param string|null $intent Section intent (defaults from type)
     * @param string|null $layout Layout type (defaults from type)
     * @param array $columns Column definitions
     * @return array Section AST
     */
    public static function createSection(
        string $type,
        ?string $intent = null,
        ?string $layout = null,
        array $columns = []
    ): array {
        // Get defaults from section type
        $defaults = self::SECTION_TYPES[$type] ?? [
            'default_intent' => self::INTENT_EXPLAIN,
            'default_layout' => self::LAYOUT_STACKED,
            'default_weight' => self::WEIGHT_MEDIUM,
        ];

        return [
            'type' => $type,
            'intent' => $intent ?? $defaults['default_intent'],
            'layout' => $layout ?? $defaults['default_layout'],
            'visual_weight' => $defaults['default_weight'],
            'columns' => $columns
        ];
    }

    /**
     * Create column AST node
     *
     * @param int $width Column width (1-12)
     * @param array $elements Elements in column
     * @return array Column AST
     */
    public static function createColumn(int $width, array $elements = []): array
    {
        return [
            'width' => min(12, max(1, $width)),
            'elements' => $elements
        ];
    }

    /**
     * Create element AST node
     *
     * @param string $type Element type
     * @param string|null $role Semantic role
     * @param array $options Additional options (count, variant, etc.)
     * @return array Element AST
     */
    public static function createElement(string $type, ?string $role = null, array $options = []): array
    {
        $element = ['type' => $type];

        if ($role !== null) {
            $element['role'] = $role;
        }

        return array_merge($element, $options);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get section type defaults
     *
     * @param string $type Section type
     * @return array|null Section defaults or null if unknown
     */
    public static function getSectionDefaults(string $type): ?array
    {
        return self::SECTION_TYPES[$type] ?? null;
    }

    /**
     * Get all available section types
     *
     * @return array Section types
     */
    public static function getAvailableSectionTypes(): array
    {
        return array_keys(self::SECTION_TYPES);
    }

    /**
     * Get all available element types
     *
     * @return array Element types
     */
    public static function getAvailableElementTypes(): array
    {
        return [
            // Text
            self::ELEM_HEADLINE, self::ELEM_SUBHEADLINE, self::ELEM_BODY_TEXT, self::ELEM_LABEL,
            // Action
            self::ELEM_CTA_PRIMARY, self::ELEM_CTA_SECONDARY, self::ELEM_LINK,
            // Visual
            self::ELEM_IMAGE_HERO, self::ELEM_IMAGE_FEATURE, self::ELEM_VIDEO, self::ELEM_ICON, self::ELEM_DIVIDER,
            // Data
            self::ELEM_STAT, self::ELEM_COUNTER, self::ELEM_PROGRESS,
            // Social proof
            self::ELEM_TESTIMONIAL, self::ELEM_LOGO, self::ELEM_LOGO_GRID, self::ELEM_RATING,
            // Commerce
            self::ELEM_PRICING_CARD, self::ELEM_FEATURE_LIST,
            // Interactive
            self::ELEM_FAQ_ITEM, self::ELEM_TAB, self::ELEM_ACCORDION,
            // Form
            self::ELEM_FORM, self::ELEM_NEWSLETTER,
            // Composite
            self::ELEM_CARD, self::ELEM_BLURB, self::ELEM_TEAM_MEMBER,
        ];
    }

    /**
     * Check if element type is valid
     *
     * @param string $type Element type
     * @return bool
     */
    public static function isValidElementType(string $type): bool
    {
        return in_array($type, self::getAvailableElementTypes());
    }

    /**
     * Check if section type is valid
     *
     * @param string $type Section type
     * @return bool
     */
    public static function isValidSectionType(string $type): bool
    {
        return isset(self::SECTION_TYPES[$type]);
    }
}
