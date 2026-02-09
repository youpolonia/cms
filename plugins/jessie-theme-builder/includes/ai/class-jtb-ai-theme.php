<?php
/**
 * JTB AI Theme Generator
 *
 * AI-powered generation for Theme Builder templates (header, footer, body).
 * Uses the SAME AI pipeline and knowledge base as Page Builder (JTB_AI_Knowledge).
 * NO hardcoded prompts - everything comes from the unified knowledge system.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0 - Refactored to use JTB_AI_Knowledge (same as Page Builder)
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Theme
{
    // ========================================
    // CONSTANTS
    // ========================================

    /** @var int Max tokens for template generation */
    private const MAX_TOKENS = 8000;

    /** @var float Temperature for creative generation */
    private const TEMPERATURE = 0.7;

    /** @var bool Enable debug logging */
    private const DEBUG = false;

    // ========================================
    // MAIN GENERATION METHODS
    // ========================================

    /**
     * Generate a complete header template
     *
     * @param string $prompt User description of desired header
     * @param array $options style, industry, brand colors, etc.
     * @return array Result with keys: ok, success, content, error
     */
    public static function generateHeader(string $prompt, array $options = []): array
    {
        return self::generateTemplate('header', $prompt, $options);
    }

    /**
     * Generate a complete footer template
     *
     * @param string $prompt User description of desired footer
     * @param array $options style, industry, brand colors, etc.
     * @return array Result with keys: ok, success, content, error
     */
    public static function generateFooter(string $prompt, array $options = []): array
    {
        return self::generateTemplate('footer', $prompt, $options);
    }

    /**
     * Generate a body template (single post/page layout)
     *
     * @param string $prompt User description of desired body layout
     * @param array $options style, layout type, etc.
     * @return array Result with keys: ok, success, content, error
     */
    public static function generateBody(string $prompt, array $options = []): array
    {
        return self::generateTemplate('body', $prompt, $options);
    }

    /**
     * Generate template of specified type
     * Uses JTB_AI_Knowledge for complete module documentation (same as Page Builder)
     *
     * @param string $type header|footer|body|single|archive|404|search
     * @param string $prompt User description
     * @param array $options Generation options
     * @return array Result
     */
    public static function generateTemplate(string $type, string $prompt, array $options = []): array
    {
        $startTime = microtime(true);

        // Ensure AI Core is loaded
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
            $corePath = __DIR__ . '/class-jtb-ai-core.php';
            if (file_exists($corePath)) {
                require_once $corePath;
            }
        }

        // Ensure JTB_AI_Knowledge is loaded
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Knowledge')) {
            $knowledgePath = __DIR__ . '/class-jtb-ai-knowledge.php';
            if (file_exists($knowledgePath)) {
                require_once $knowledgePath;
            }
        }

        $ai = JTB_AI_Core::getInstance();
        if (!$ai->isConfigured()) {
            return self::errorResponse('AI is not configured. Please set up an AI provider in settings.');
        }

        // Extract options
        $style = $options['style'] ?? 'modern';
        $industry = $options['industry'] ?? 'business';
        $brandName = $options['brand_name'] ?? null;
        $primaryColor = $options['primary_color'] ?? null;
        $secondaryColor = $options['secondary_color'] ?? null;

        // Get color hints for this industry (from JTB_AI_Direct)
        $colorHints = self::getColorHintsForIndustry($industry, $style);

        // =============================================
        // USE JTB_AI_KNOWLEDGE FOR SYSTEM PROMPT
        // This is the SAME system used by Page Builder!
        // =============================================
        $systemPrompt = JTB_AI_Knowledge::getSystemPrompt([
            'industry' => $industry,
            'style' => $style,
            'page_type' => 'template',  // Special type for templates
            'template_type' => $type,   // header, footer, body, etc.
            'color_hints' => $colorHints
        ]);

        // Add template-specific context to system prompt
        $systemPrompt .= self::getTemplateTypeContext($type);

        // Build user prompt
        $userPrompt = self::buildUserPrompt($type, $prompt, [
            'style' => $style,
            'industry' => $industry,
            'brand_name' => $brandName,
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor
        ]);

        self::log("Generating {$type} template with JTB_AI_Knowledge system prompt");
        self::log("Industry: {$industry}, Style: {$style}");

        // Query AI with retry
        $response = $ai->queryWithRetry($userPrompt, 2, [
            'system_prompt' => $systemPrompt,
            'max_tokens' => self::MAX_TOKENS,
            'temperature' => self::TEMPERATURE,
            'json_mode' => true
        ]);

        if (!$response['ok']) {
            self::log("AI query failed: " . ($response['error'] ?? 'Unknown error'));
            return self::errorResponse($response['error'] ?? 'AI query failed');
        }

        // Parse JSON from response
        $content = self::parseJsonFromResponse($response['text'] ?? '');
        if (!$content) {
            self::log("Failed to parse AI response as JSON");
            return self::errorResponse('Failed to parse AI response as valid template JSON');
        }

        // Normalize content structure
        $content = self::normalizeContent($content);

        // Validate structure
        if (!self::validateTemplateStructure($content, $type)) {
            self::log("Generated template has invalid structure");
            return self::errorResponse('Generated template has invalid structure');
        }

        // Apply any missing default styles
        $content = self::applyDefaultStyles($content, $options);

        // Add unique IDs to all elements
        $content = self::addElementIds($content);

        $timeMs = round((microtime(true) - $startTime) * 1000);

        self::log("Template generated successfully in {$timeMs}ms");

        return [
            'ok' => true,
            'success' => true,
            'content' => $content,
            'type' => $type,
            'stats' => [
                'time_ms' => $timeMs,
                'provider' => $ai->getProvider(),
                'model' => $ai->getModel()
            ]
        ];
    }

    // ========================================
    // TEMPLATE TYPE CONTEXT
    // These are ADDITIONAL instructions for specific template types
    // The main module documentation comes from JTB_AI_Knowledge
    // ========================================

    /**
     * Get template-type-specific context to append to system prompt
     * This provides guidance on WHICH modules to use for each template type
     */
    private static function getTemplateTypeContext(string $type): string
    {
        $context = "\n\n## TEMPLATE TYPE GUIDANCE\n\n";

        switch ($type) {
            case 'header':
                $context .= <<<'CONTEXT'
You are generating a HEADER template. Headers appear at the top of every page.

RECOMMENDED MODULES for headers (use these from the module documentation above):
- site_logo: Company logo (always include in headers)
- menu: Main navigation menu (essential for headers)
- header_button: CTA button (e.g., "Get Started", "Contact Us")
- search_form: Search functionality (optional)
- cart_icon: Shopping cart icon for e-commerce (optional)
- social_icons: Social media icons (optional, usually better in footer)

COMMON HEADER LAYOUTS:
1. Classic: Logo left (1/4) + Menu center (1/2) + CTA right (1/4)
2. Modern: Logo left (1/3) + Menu + CTA right (2/3)
3. Centered: Full-width logo above, full-width menu below
4. E-commerce: Logo + Search + Cart + Menu

IMPORTANT HEADER ATTRIBUTES:
- Use section attrs: background_color, padding (smaller for headers, e.g., top: 15, bottom: 15)
- Consider: sticky headers (add data attribute), transparent headers over hero sections
- Keep headers compact - typically 60-100px total height

CONTEXT;
                break;

            case 'footer':
                $context .= <<<'CONTEXT'
You are generating a FOOTER template. Footers appear at the bottom of every page.

RECOMMENDED MODULES for footers (use these from the module documentation above):
- site_logo: Company logo (smaller version)
- footer_menu: Navigation links organized by category
- footer_info: Company info (address, phone, email)
- social_icons: Social media icons (common in footers)
- copyright: Copyright text with year
- text: Additional text content
- heading: Section headings within footer columns

COMMON FOOTER LAYOUTS:
1. 4-column: Logo+Info (1/4) + Links (1/4) + Links (1/4) + Newsletter (1/4)
2. 3-column: Company Info (1/3) + Quick Links (1/3) + Contact (1/3)
3. 2-section: Main footer with columns + Copyright bar below
4. Simple: Centered logo + Social icons + Copyright

FOOTER STYLING:
- Use darker background colors (e.g., #1f2937, #111827)
- Use lighter text colors for contrast
- Section padding: typically 60-80px top/bottom
- Copyright bar: smaller padding (20-30px), may have different background

CONTEXT;
                break;

            case 'body':
            case 'single':
                $context .= <<<'CONTEXT'
You are generating a BODY/SINGLE template. This is the main content area for individual posts/pages.

RECOMMENDED MODULES for body templates (use DYNAMIC modules that pull real content):
- post_title: Displays the post/page title dynamically
- post_content: Displays the main content dynamically
- post_excerpt: Displays post excerpt (for previews)
- featured_image: Displays the featured image dynamically
- post_meta: Shows date, author, categories dynamically
- author_box: Author information with avatar and bio
- related_posts: Grid of related posts
- breadcrumbs: Navigation breadcrumbs
- sidebar: Optional sidebar widget area

COMMON BODY LAYOUTS:
1. Blog post: Featured image (full) → Title + Meta → Content → Author box → Related posts
2. Page: Title → Content (wide)
3. With sidebar: Content (2/3) + Sidebar (1/3)
4. Full-width: Hero → Content sections

DYNAMIC MODULES NOTE:
These modules automatically pull content from the current post/page being viewed.
You don't need to specify actual content - just configure their display options.

CONTEXT;
                break;

            case 'archive':
                $context .= <<<'CONTEXT'
You are generating an ARCHIVE template. This displays lists of posts (blog, categories, tags, etc.).

RECOMMENDED MODULES for archives:
- archive_title: Displays the archive title dynamically (e.g., "Category: News")
- archive_posts: Grid/list of posts with pagination
- breadcrumbs: Navigation path
- sidebar: Optional sidebar with filters, categories, etc.
- search_form: Allow searching within archive

COMMON ARCHIVE LAYOUTS:
1. Grid: Archive title → 3-column post grid → Pagination
2. List: Archive title → Full-width post list → Pagination
3. With sidebar: Posts (2/3) + Sidebar (1/3)

CONTEXT;
                break;

            case '404':
                $context .= <<<'CONTEXT'
You are generating a 404 ERROR template. This is shown when a page is not found.

RECOMMENDED MODULES for 404 pages:
- heading: "404" or "Page Not Found" title
- text: Helpful message explaining the error
- button: Link back to homepage or search
- search_form: Help users find what they're looking for
- image: Optional illustration

COMMON 404 LAYOUTS:
1. Centered: Large "404" + Message + Buttons
2. Illustrated: Image/illustration + Text + Navigation options
3. Minimal: Simple message + Search form + Home button

Keep 404 pages simple but helpful. Don't use dynamic content modules here.

CONTEXT;
                break;

            case 'search':
                $context .= <<<'CONTEXT'
You are generating a SEARCH RESULTS template. This displays search results.

RECOMMENDED MODULES:
- archive_title: Shows "Search Results for: [query]" dynamically
- search_form: Allow refining the search
- archive_posts: Display matching posts
- text: "No results found" message (for empty results)

COMMON SEARCH LAYOUTS:
1. Standard: Search form → Results title → Post list → Pagination
2. With filters: Sidebar with filters + Results area

CONTEXT;
                break;

            default:
                $context .= "Generate a template appropriate for the '{$type}' context.\n";
        }

        return $context;
    }

    // ========================================
    // USER PROMPT BUILDER
    // ========================================

    /**
     * Build user prompt for template generation
     */
    private static function buildUserPrompt(string $type, string $prompt, array $options): string
    {
        $style = $options['style'] ?? 'modern';
        $industry = $options['industry'] ?? 'business';

        $userPrompt = "Generate a {$type} template with these requirements:\n\n";
        $userPrompt .= "USER DESCRIPTION:\n{$prompt}\n\n";
        $userPrompt .= "STYLE: {$style}\n";
        $userPrompt .= "INDUSTRY: {$industry}\n";

        if (!empty($options['brand_name'])) {
            $userPrompt .= "BRAND NAME: {$options['brand_name']}\n";
        }
        if (!empty($options['primary_color'])) {
            $userPrompt .= "PRIMARY COLOR: {$options['primary_color']}\n";
        }
        if (!empty($options['secondary_color'])) {
            $userPrompt .= "SECONDARY COLOR: {$options['secondary_color']}\n";
        }

        $userPrompt .= <<<INSTRUCTIONS

GENERATION INSTRUCTIONS:
1. Use ONLY modules documented in the system prompt above
2. Use EXACT attribute names and value formats from the documentation
3. Follow the JTB JSON structure: sections → rows → columns → modules
4. Include proper styling attributes (padding, colors, typography)
5. Make the design professional and match the {$style} style
6. Generate real, contextual content - NO placeholder text like "Lorem ipsum"

OUTPUT FORMAT:
Return ONLY valid JSON in this structure:
{
  "sections": [
    {
      "type": "section",
      "attrs": {...},
      "children": [rows...]
    }
  ]
}

Generate the JSON template now.
INSTRUCTIONS;

        return $userPrompt;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get color hints for industry - delegates to JTB_AI_Styles (single source of truth)
     */
    private static function getColorHintsForIndustry(string $industry, string $style): array
    {
        return JTB_AI_Styles::getIndustryColors($industry);
    }

    /**
     * Parse JSON from AI response
     */
    private static function parseJsonFromResponse(string $response): ?array
    {
        // Try direct parse
        $data = json_decode($response, true);
        if ($data !== null) {
            return $data;
        }

        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $response, $matches)) {
            $data = json_decode(trim($matches[1]), true);
            if ($data !== null) {
                return $data;
            }
        }

        // Try to find JSON object
        if (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data !== null) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Normalize content structure
     */
    private static function normalizeContent(array $content): array
    {
        // If content has 'content' key with array, use that as sections
        if (isset($content['content']) && is_array($content['content'])) {
            return ['sections' => $content['content']];
        }

        // If content has 'sections' key, return as-is
        if (isset($content['sections'])) {
            return $content;
        }

        // If content is array of sections (each with type: section)
        if (isset($content[0]['type']) && $content[0]['type'] === 'section') {
            return ['sections' => $content];
        }

        // Wrap in sections
        return ['sections' => [$content]];
    }

    /**
     * Validate template structure
     */
    private static function validateTemplateStructure(array $content, string $type): bool
    {
        if (!isset($content['sections']) || !is_array($content['sections'])) {
            return false;
        }

        if (empty($content['sections'])) {
            return false;
        }

        // Check first section has required structure
        $firstSection = $content['sections'][0];
        if (!isset($firstSection['type']) || $firstSection['type'] !== 'section') {
            // Try to fix if type is missing
            if (isset($firstSection['children']) || isset($firstSection['attrs'])) {
                return true; // Probably valid, just missing explicit type
            }
            return false;
        }

        return true;
    }

    /**
     * Apply default styles if missing
     */
    private static function applyDefaultStyles(array $content, array $options): array
    {
        $style = $options['style'] ?? 'modern';

        foreach ($content['sections'] as &$section) {
            // Ensure section has type
            if (!isset($section['type'])) {
                $section['type'] = 'section';
            }

            // Ensure section has attrs
            if (!isset($section['attrs'])) {
                $section['attrs'] = [];
            }

            // Add default padding if missing
            if (!isset($section['attrs']['padding'])) {
                $section['attrs']['padding'] = [
                    'top' => 60,
                    'right' => 0,
                    'bottom' => 60,
                    'left' => 0
                ];
            }
        }

        return $content;
    }

    /**
     * Add unique IDs to all elements
     */
    private static function addElementIds(array $content): array
    {
        $counter = 1;

        foreach ($content['sections'] as &$section) {
            if (!isset($section['id'])) {
                $section['id'] = 'section_' . uniqid() . '_' . $counter++;
            }

            if (isset($section['children'])) {
                foreach ($section['children'] as &$row) {
                    if (!isset($row['id'])) {
                        $row['id'] = 'row_' . uniqid() . '_' . $counter++;
                    }

                    if (isset($row['children'])) {
                        foreach ($row['children'] as &$column) {
                            if (!isset($column['id'])) {
                                $column['id'] = 'column_' . uniqid() . '_' . $counter++;
                            }

                            if (isset($column['children'])) {
                                foreach ($column['children'] as &$module) {
                                    if (!isset($module['id'])) {
                                        $moduleType = $module['type'] ?? 'module';
                                        $module['id'] = $moduleType . '_' . uniqid() . '_' . $counter++;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Create error response
     */
    private static function errorResponse(string $message): array
    {
        return [
            'ok' => false,
            'success' => false,
            'content' => null,
            'error' => $message
        ];
    }

    /**
     * Log message if debug enabled
     */
    private static function log(string $message): void
    {
        if (self::DEBUG) {
            error_log("[JTB_AI_Theme] " . $message);
        }
    }

    // ========================================
    // PRESET TEMPLATES (for quick selection)
    // These are still useful for instant templates without AI
    // ========================================

    /**
     * Get preset header templates
     */
    public static function getPresetHeaders(): array
    {
        return [
            'minimal' => [
                'name' => 'Minimal Header',
                'description' => 'Clean logo + menu layout',
                'content' => self::getMinimalHeaderContent()
            ],
            'centered' => [
                'name' => 'Centered Header',
                'description' => 'Centered logo with menu below',
                'content' => self::getCenteredHeaderContent()
            ],
            'ecommerce' => [
                'name' => 'E-commerce Header',
                'description' => 'Logo + search + cart + menu',
                'content' => self::getEcommerceHeaderContent()
            ]
        ];
    }

    /**
     * Get preset footer templates
     */
    public static function getPresetFooters(): array
    {
        return [
            'simple' => [
                'name' => 'Simple Footer',
                'description' => 'Logo + social + copyright',
                'content' => self::getSimpleFooterContent()
            ],
            'columns' => [
                'name' => 'Multi-Column Footer',
                'description' => '4-column footer with links',
                'content' => self::getColumnsFooterContent()
            ]
        ];
    }

    // Preset content methods (simplified examples)
    private static function getMinimalHeaderContent(): array
    {
        return [
            'sections' => [[
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 15, 'bottom' => 15], 'background_color' => '#ffffff'],
                'children' => [[
                    'type' => 'row',
                    'attrs' => ['columns' => '1_4,3_4'],
                    'children' => [
                        ['type' => 'column', 'children' => [['type' => 'site_logo', 'attrs' => []]]],
                        ['type' => 'column', 'children' => [['type' => 'menu', 'attrs' => ['alignment' => 'right']]]]
                    ]
                ]]
            ]]
        ];
    }

    private static function getCenteredHeaderContent(): array
    {
        return [
            'sections' => [[
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 20, 'bottom' => 20], 'background_color' => '#ffffff'],
                'children' => [
                    ['type' => 'row', 'attrs' => ['columns' => '1_1'], 'children' => [
                        ['type' => 'column', 'children' => [['type' => 'site_logo', 'attrs' => ['alignment' => 'center']]]]
                    ]],
                    ['type' => 'row', 'attrs' => ['columns' => '1_1'], 'children' => [
                        ['type' => 'column', 'children' => [['type' => 'menu', 'attrs' => ['alignment' => 'center']]]]
                    ]]
                ]
            ]]
        ];
    }

    private static function getEcommerceHeaderContent(): array
    {
        return [
            'sections' => [[
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 15, 'bottom' => 15], 'background_color' => '#ffffff'],
                'children' => [[
                    'type' => 'row',
                    'attrs' => ['columns' => '1_4,1_2,1_4'],
                    'children' => [
                        ['type' => 'column', 'children' => [['type' => 'site_logo', 'attrs' => []]]],
                        ['type' => 'column', 'children' => [['type' => 'search_form', 'attrs' => []]]],
                        ['type' => 'column', 'children' => [
                            ['type' => 'cart_icon', 'attrs' => []],
                            ['type' => 'header_button', 'attrs' => ['text' => 'Account']]
                        ]]
                    ]
                ]]
            ]]
        ];
    }

    private static function getSimpleFooterContent(): array
    {
        return [
            'sections' => [[
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 40, 'bottom' => 40], 'background_color' => '#1f2937'],
                'children' => [[
                    'type' => 'row',
                    'attrs' => ['columns' => '1_1'],
                    'children' => [[
                        'type' => 'column',
                        'children' => [
                            ['type' => 'site_logo', 'attrs' => ['alignment' => 'center']],
                            ['type' => 'social_icons', 'attrs' => ['alignment' => 'center']],
                            ['type' => 'copyright', 'attrs' => ['alignment' => 'center', 'text_color' => '#9ca3af']]
                        ]
                    ]]
                ]]
            ]]
        ];
    }

    private static function getColumnsFooterContent(): array
    {
        return [
            'sections' => [[
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 60, 'bottom' => 60], 'background_color' => '#1f2937'],
                'children' => [[
                    'type' => 'row',
                    'attrs' => ['columns' => '1_4,1_4,1_4,1_4'],
                    'children' => [
                        ['type' => 'column', 'children' => [
                            ['type' => 'site_logo', 'attrs' => []],
                            ['type' => 'text', 'attrs' => ['content' => 'Your company description here.', 'text_color' => '#9ca3af']]
                        ]],
                        ['type' => 'column', 'children' => [
                            ['type' => 'heading', 'attrs' => ['text' => 'Quick Links', 'level' => 'h4', 'text_color' => '#ffffff']],
                            ['type' => 'footer_menu', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'children' => [
                            ['type' => 'heading', 'attrs' => ['text' => 'Services', 'level' => 'h4', 'text_color' => '#ffffff']],
                            ['type' => 'footer_menu', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'children' => [
                            ['type' => 'heading', 'attrs' => ['text' => 'Contact', 'level' => 'h4', 'text_color' => '#ffffff']],
                            ['type' => 'footer_info', 'attrs' => ['text_color' => '#9ca3af']]
                        ]]
                    ]
                ]]
            ], [
                'type' => 'section',
                'attrs' => ['padding' => ['top' => 20, 'bottom' => 20], 'background_color' => '#111827'],
                'children' => [[
                    'type' => 'row',
                    'attrs' => ['columns' => '1_1'],
                    'children' => [[
                        'type' => 'column',
                        'children' => [['type' => 'copyright', 'attrs' => ['alignment' => 'center', 'text_color' => '#6b7280']]]
                    ]]
                ]]
            ]]
        ];
    }
}
