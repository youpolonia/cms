<?php
/**
 * JTB UNIFIED AI Generation API
 *
 * SINGLE ENDPOINT for all AI generation in JTB:
 * - Page Builder (posts)
 * - Template Builder (header/footer/body templates)
 * - Library (saved layouts)
 *
 * Uses JTB_AI_Schema::getCompactSchemasForAI() for REAL module schemas.
 * NO HARDCODED DOCUMENTATION - everything comes from JTB_Registry!
 *
 * POST /api/jtb/ai/generate
 *
 * @package JessieThemeBuilder
 * @since 2026-02-04
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

// Ensure method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jtb_json_response(false, [], 'Method not allowed', 405);
    exit;
}

// Parse request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    $data = $_POST;
}

if (empty($data)) {
    jtb_json_response(false, [], 'Invalid request data', 400);
    exit;
}

// Get parameters
$action = $data['action'] ?? 'layout';
$prompt = $data['prompt'] ?? '';

// Context determines Page Builder vs Template Builder
$context = $data['context'] ?? [];
$contextType = $context['type'] ?? $data['type'] ?? 'page'; // 'page' or 'template'
$contextId = $context['id'] ?? $data['page_id'] ?? $data['template_id'] ?? null;
$templateType = $context['template_type'] ?? $data['template_type'] ?? 'header'; // header/footer/body

// Styling options
$style = $context['style'] ?? $data['style'] ?? 'modern';
$industry = $context['industry'] ?? $data['industry'] ?? 'general';
$pageType = $context['page_type'] ?? $data['page_type'] ?? null;

// Template-specific options (for header/footer)
$templateOptions = $data['template_options'] ?? [];
$templateStyle = $data['template_style'] ?? 'classic';

// Validate prompt
if (empty($prompt)) {
    jtb_json_response(false, [], 'Prompt is required', 400);
    exit;
}

// Set response header
header('Content-Type: application/json');

// Check if AI Core is available
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Core')) {
    jtb_json_response(false, [], 'AI Core class not loaded', 500);
    exit;
}

// Check if AI Schema is available
if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Schema')) {
    jtb_json_response(false, [], 'AI Schema class not loaded', 500);
    exit;
}

$ai = JTB_AI_Core::getInstance();

if (!$ai->isConfigured()) {
    jtb_json_response(false, [], 'AI is not configured. Please configure an AI provider in settings.', 500);
    exit;
}

try {
    $startTime = microtime(true);

    // Build system prompt with REAL schemas from JTB_Registry
    $systemPrompt = buildUnifiedSystemPrompt($contextType, $templateType);

    // Build user prompt
    $userPrompt = buildUnifiedUserPrompt(
        $prompt,
        $contextType,
        $templateType,
        $style,
        $industry,
        $pageType,
        $templateStyle,
        $templateOptions
    );

    // Call AI
    $response = $ai->query($userPrompt, [
        'system_prompt' => $systemPrompt,
        'temperature' => 0.7,
        'max_tokens' => 4000
    ]);

    if (!$response['ok']) {
        throw new \Exception($response['error'] ?? 'AI query failed');
    }

    // Parse JSON from response
    $content = $response['text'] ?? '';
    $layout = parseJsonFromAIResponse($content);

    if (!$layout || empty($layout['sections'])) {
        throw new \Exception('Failed to parse AI response as valid JSON');
    }

    // Normalize slugs (AI may use hyphens, Registry uses underscores)
    $layout['sections'] = normalizeModuleSlugs($layout['sections']);

    $timeMs = round((microtime(true) - $startTime) * 1000);

    // Unified response format
    jtb_json_response(true, [
        'layout' => $layout,
        'sections' => $layout['sections'], // backward compatibility
        'content' => $layout['sections'],  // backward compatibility for template-editor.js
        'context' => [
            'type' => $contextType,
            'template_type' => $contextType === 'template' ? $templateType : null,
            'id' => $contextId
        ],
        'stats' => [
            'time_ms' => $timeMs,
            'provider' => $response['provider'] ?? 'unknown',
            'sections_count' => count($layout['sections'])
        ]
    ]);

} catch (\Exception $e) {
    error_log('JTB AI generate error: ' . $e->getMessage());
    jtb_json_response(false, [], 'Generation error: ' . $e->getMessage(), 500);
}

/**
 * Build unified system prompt with REAL schemas from JTB_Registry
 */
function buildUnifiedSystemPrompt(string $contextType, string $templateType = 'header'): string
{
    // Get REAL schemas from JTB_AI_Schema (NOT hardcoded!)
    $schemas = JTB_AI_Schema::getCompactSchemasForAI();

    // Get column layouts
    $columnLayouts = <<<LAYOUTS
COLUMN LAYOUTS (row "columns" attribute):
- "1" = single full-width column
- "1_2,1_2" = two equal columns (50%, 50%)
- "1_3,2_3" = one third + two thirds
- "2_3,1_3" = two thirds + one third
- "1_4,3_4" = quarter + three quarters
- "3_4,1_4" = three quarters + quarter
- "1_3,1_3,1_3" = three equal columns
- "1_4,1_4,1_4,1_4" = four equal columns
LAYOUTS;

    // Context-specific instructions
    if ($contextType === 'template') {
        $contextInstructions = getTemplateContextInstructions($templateType);
    } else {
        $contextInstructions = getPageContextInstructions();
    }

    return <<<SYSTEM
You are an expert web designer generating JTB (Jessie Theme Builder) layouts.
You MUST output ONLY valid JSON - no explanations, no markdown code blocks.

OUTPUT FORMAT - Return ONLY this JSON structure:
{
  "sections": [
    {
      "type": "section",
      "attrs": {
        "padding": {"top": 80, "right": 0, "bottom": 80, "left": 0},
        "background_color": "#ffffff"
      },
      "children": [
        {
          "type": "row",
          "attrs": {"columns": "1_4,3_4"},
          "children": [
            {
              "type": "column",
              "attrs": {},
              "children": [
                {"type": "MODULE_TYPE", "attrs": {...}}
              ]
            }
          ]
        }
      ]
    }
  ]
}

{$schemas}

{$columnLayouts}

{$contextInstructions}

CRITICAL RULES:
1. Output ONLY JSON - no text before or after, no markdown code blocks
2. Every section must have type, attrs, children
3. Every row must have columns attribute matching number of column children
4. Use ONLY the attributes listed in MODULE SCHEMAS above
5. Module types use UNDERSCORES not hyphens (e.g., "site_logo" not "site-logo")
6. All padding/margin values are objects: {"top": N, "right": N, "bottom": N, "left": N}
7. All border_radius values are objects: {"tl": N, "tr": N, "br": N, "bl": N}
SYSTEM;
}

/**
 * Get template-specific context instructions
 */
function getTemplateContextInstructions(string $templateType): string
{
    $instructions = [
        'header' => <<<'HEADER_DOC'
HEADER TEMPLATE CONTEXT:
You are generating a website HEADER. Headers contain:
- site_logo module (company logo)
- menu module (navigation - gets items from database dynamically)
- button module (CTA button like "Contact Us")
- social_icons module (social media links)
- search module (search form)

IMPORTANT FOR HEADERS:
- site_logo: Use "logo" attr for image URL, "logo_url" for link destination
- menu: Do NOT include menu_items - menu reads from database dynamically
- button: Use "text" and "link_url", button styles are "solid", "outline", "ghost"
- social_icons: Use individual URL attrs: facebook_url, twitter_url, etc.

HEADER STRUCTURE:
- Usually 1-2 sections (topbar optional + main header)
- Classic: row with "1_4,3_4" - logo left, menu right
- Centered: row with "1_3,1_3,1_3" - menu, logo, actions
- Split: row with "1_3,1_3,1_3" - menu, logo, menu/CTA
HEADER_DOC,

        'footer' => <<<'FOOTER_DOC'
FOOTER TEMPLATE CONTEXT:
You are generating a website FOOTER. Footers contain:
- site_logo module (smaller logo)
- menu module (footer links - vertical orientation)
- text module (descriptions, copyright)
- heading module (column titles like "Quick Links")
- social_icons module
- blurb module (contact info with icons)

FOOTER STRUCTURE:
- Usually 2 sections (main footer + copyright bar)
- Main footer: row with "1_4,1_4,1_4,1_4" or "1_3,1_3,1_3" columns
- Copyright: row with "1" column, centered text
- Dark backgrounds are common (#1f2937, #111827)
FOOTER_DOC,

        'body' => <<<'BODY_DOC'
BODY TEMPLATE CONTEXT:
You are generating a BODY template for posts/pages. Body templates contain:
- post_title module (dynamic post title)
- post_content module (dynamic post content)
- post_meta module (date, author, categories)
- featured_image module (post featured image)
- author_box module (author bio)
- related_posts module (related content grid)
- breadcrumbs module (navigation path)
- sidebar module (widget area)
- comments module (comment section)

BODY STRUCTURE:
- Full width: row with "1" column
- With sidebar: row with "2_3,1_3" or "1_3,2_3"
- All post_* modules get data dynamically from the current post
BODY_DOC
    ];

    return $instructions[$templateType] ?? $instructions['body'];
}

/**
 * Get page builder context instructions
 */
function getPageContextInstructions(): string
{
    return <<<'PAGE_DOC'
PAGE BUILDER CONTEXT:
You are generating a page layout for a website. Use appropriate modules:

CONTENT MODULES:
- heading: For titles and section headers
- text: For body content and descriptions
- button: For CTAs and links
- blurb: For features with icon + title + description
- image: For images with optional captions
- cta: For call-to-action sections
- testimonial: For customer reviews
- team_member: For team profiles
- pricing_table: For pricing plans
- accordion/tabs: For organized content
- gallery/slider: For image showcases
- video: For embedded videos
- contact_form: For contact sections
- number_counter: For statistics

SECTION PATTERNS:
- Hero: fullwidth_header OR section with large padding, heading, text, button
- Features: section with row of blurbs (3-4 columns)
- Testimonials: section with testimonial modules
- CTA: section with cta module or heading + button
- Contact: section with contact_form + map or blurbs
PAGE_DOC;
}

/**
 * Build user prompt with all context
 */
function buildUnifiedUserPrompt(
    string $prompt,
    string $contextType,
    string $templateType,
    string $style,
    string $industry,
    ?string $pageType,
    string $templateStyle,
    array $templateOptions
): string {
    $userPrompt = "";

    // Context type
    if ($contextType === 'template') {
        $userPrompt .= "Generate a {$templateType} template.\n\n";
    } else {
        $userPrompt .= "Generate a page layout.\n\n";
    }

    // User request
    $userPrompt .= "USER REQUEST: {$prompt}\n\n";

    // Styling
    $userPrompt .= "VISUAL STYLE: {$style}\n";
    $userPrompt .= "INDUSTRY: {$industry}\n";
    if ($pageType) {
        $userPrompt .= "PAGE TYPE: {$pageType}\n";
    }
    $userPrompt .= "\n";

    // Template-specific options
    if ($contextType === 'template' && !empty($templateOptions)) {
        $userPrompt .= buildTemplateOptionsPrompt($templateType, $templateStyle, $templateOptions);
    }

    $userPrompt .= "\nGenerate the complete JSON layout now. Output ONLY valid JSON.";

    return $userPrompt;
}

/**
 * Build template options section of prompt
 */
function buildTemplateOptionsPrompt(string $templateType, string $templateStyle, array $options): string
{
    $prompt = "TEMPLATE SPECIFICATIONS:\n";
    $prompt .= "- Template style: {$templateStyle}\n";

    if ($templateType === 'header') {
        $prompt .= "- Logo position: " . ($options['logoPosition'] ?? 'left') . "\n";
        $prompt .= "- Navigation style: " . ($options['navStyle'] ?? 'horizontal') . "\n";

        $features = [];
        if (!empty($options['sticky'])) $features[] = 'sticky header';
        if (!empty($options['search'])) $features[] = 'search icon';
        if (!empty($options['cta'])) $features[] = 'CTA button';
        if (!empty($options['social'])) $features[] = 'social icons';
        if (!empty($options['topbar'])) $features[] = 'top bar with contact info';

        if ($features) {
            $prompt .= "- Features: " . implode(', ', $features) . "\n";
        }

        // Structure guidance
        $prompt .= "\nSTRUCTURE GUIDANCE:\n";

        if (!empty($options['topbar'])) {
            $prompt .= "- Add a top bar section (dark background, small padding)\n";
        }

        switch ($templateStyle) {
            case 'centered':
                $prompt .= "- Main header: use '1_3,1_3,1_3' columns (menu | logo | actions)\n";
                break;
            case 'split':
                $prompt .= "- Main header: use '1_3,1_3,1_3' columns (menu left | logo center | menu/CTA right)\n";
                break;
            case 'minimal':
                $prompt .= "- Main header: use '1_2,1_2' columns, minimal items\n";
                break;
            default: // classic
                $logoPos = $options['logoPosition'] ?? 'left';
                if ($logoPos === 'left') {
                    $prompt .= "- Main header: use '1_4,3_4' columns (logo | menu + actions)\n";
                } elseif ($logoPos === 'right') {
                    $prompt .= "- Main header: use '3_4,1_4' columns (menu + actions | logo)\n";
                } else {
                    $prompt .= "- Main header: use '1_3,1_3,1_3' columns\n";
                }
        }
    }

    if ($templateType === 'footer') {
        $columns = $options['columns'] ?? 4;
        $prompt .= "- Footer columns: {$columns}\n";

        $elements = [];
        if (!empty($options['logo'])) $elements[] = 'company logo';
        if (!empty($options['menu'])) $elements[] = 'navigation links';
        if (!empty($options['social'])) $elements[] = 'social icons';
        if (!empty($options['newsletter'])) $elements[] = 'newsletter form';
        if (!empty($options['contact'])) $elements[] = 'contact info';
        if (!empty($options['copyright'])) $elements[] = 'copyright section';

        if ($elements) {
            $prompt .= "- Elements: " . implode(', ', $elements) . "\n";
        }
    }

    if ($templateType === 'body') {
        $layout = $options['layout'] ?? 'full';
        $prompt .= "- Layout: {$layout}\n";

        $elements = [];
        if (!empty($options['title'])) $elements[] = 'post_title';
        if (!empty($options['meta'])) $elements[] = 'post_meta';
        if (!empty($options['content'])) $elements[] = 'post_content';
        if (!empty($options['author'])) $elements[] = 'author_box';
        if (!empty($options['related'])) $elements[] = 'related_posts';
        if (!empty($options['comments'])) $elements[] = 'comments';

        if ($elements) {
            $prompt .= "- Modules: " . implode(', ', $elements) . "\n";
        }
    }

    return $prompt;
}

/**
 * Parse JSON from AI response (handles markdown code blocks)
 */
function parseJsonFromAIResponse(string $content): ?array
{
    // Try direct JSON parse first
    $decoded = json_decode($content, true);
    if ($decoded && isset($decoded['sections'])) {
        return $decoded;
    }

    // Try to extract from markdown code block
    if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)\n?```/', $content, $matches)) {
        $decoded = json_decode($matches[1], true);
        if ($decoded && isset($decoded['sections'])) {
            return $decoded;
        }
    }

    // Try to find JSON object in response
    if (preg_match('/\{[\s\S]*"sections"[\s\S]*\}/', $content, $matches)) {
        $decoded = json_decode($matches[0], true);
        if ($decoded && isset($decoded['sections'])) {
            return $decoded;
        }
    }

    return null;
}

/**
 * Normalize module slugs (convert hyphens to underscores)
 * AI may generate "site-logo" but Registry uses "site_logo"
 */
function normalizeModuleSlugs(array $sections): array
{
    foreach ($sections as &$section) {
        if (isset($section['type'])) {
            $section['type'] = str_replace('-', '_', $section['type']);
        }

        if (isset($section['children']) && is_array($section['children'])) {
            $section['children'] = normalizeModuleSlugs($section['children']);
        }
    }

    return $sections;
}
