<?php
/**
 * JTB AI Website Generator
 *
 * Advanced AI-powered website generation with Web Designer Knowledge Base.
 * Generates complete websites with proper section counts, professional content,
 * and industry-specific layouts.
 *
 * @package JessieThemeBuilder
 * @since 2026-02-04
 * @updated 2026-02-04 - ETAP 1: AI Prompt Engineering
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Website
{
    // ========================================
    // WEB DESIGNER KNOWLEDGE BASE
    // ========================================

    /**
     * Industry-specific section recommendations
     * Each industry has optimized section types and order
     */
    // NOTE: Industry colors are NOT stored here - use JTB_AI_Styles::getIndustryColors() as single source of truth
    private const INDUSTRY_TEMPLATES = [
        'technology' => [
            'home' => ['hero', 'trust_logos', 'features', 'how_it_works', 'benefits', 'testimonials', 'pricing', 'faq', 'cta'],
            'about' => ['hero_about', 'story', 'values', 'team', 'stats', 'timeline', 'cta'],
            'services' => ['hero_services', 'services_overview', 'service_details', 'process', 'case_studies', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'contact_form', 'locations', 'faq', 'cta'],
        ],
        'healthcare' => [
            'home' => ['hero', 'trust_badges', 'services', 'why_choose', 'doctors', 'testimonials', 'insurance', 'faq', 'cta'],
            'about' => ['hero_about', 'mission', 'values', 'team', 'certifications', 'facilities', 'cta'],
            'services' => ['hero_services', 'services_grid', 'specialties', 'technology', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'appointment_form', 'locations', 'hours', 'emergency', 'cta'],
        ],
        'legal' => [
            'home' => ['hero', 'practice_areas', 'why_choose', 'results', 'attorneys', 'testimonials', 'awards', 'faq', 'cta'],
            'about' => ['hero_about', 'firm_history', 'values', 'team', 'achievements', 'community', 'cta'],
            'services' => ['hero_services', 'practice_areas', 'case_types', 'process', 'results', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'consultation_form', 'locations', 'hours', 'cta'],
        ],
        'restaurant' => [
            'home' => ['hero', 'about_brief', 'menu_highlights', 'specials', 'gallery', 'testimonials', 'reservations', 'location', 'cta'],
            'about' => ['hero_about', 'story', 'chef', 'philosophy', 'sourcing', 'gallery', 'cta'],
            'menu' => ['hero_menu', 'menu_categories', 'specials', 'drinks', 'desserts', 'dietary', 'cta'],
            'contact' => ['hero_contact', 'reservation_form', 'location', 'hours', 'private_events', 'cta'],
        ],
        'real_estate' => [
            'home' => ['hero', 'featured_properties', 'services', 'areas', 'why_choose', 'testimonials', 'stats', 'cta'],
            'about' => ['hero_about', 'story', 'team', 'values', 'awards', 'stats', 'cta'],
            'services' => ['hero_services', 'buying', 'selling', 'renting', 'process', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'contact_form', 'locations', 'agents', 'cta'],
        ],
        'fitness' => [
            'home' => ['hero', 'programs', 'trainers', 'facilities', 'success_stories', 'pricing', 'schedule', 'faq', 'cta'],
            'about' => ['hero_about', 'story', 'values', 'team', 'certifications', 'gallery', 'cta'],
            'services' => ['hero_services', 'programs', 'classes', 'personal_training', 'nutrition', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'trial_form', 'locations', 'hours', 'cta'],
        ],
        'agency' => [
            'home' => ['hero', 'clients', 'services', 'portfolio', 'process', 'testimonials', 'team', 'cta'],
            'about' => ['hero_about', 'story', 'values', 'team', 'culture', 'awards', 'cta'],
            'services' => ['hero_services', 'services_grid', 'case_studies', 'process', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'project_form', 'locations', 'cta'],
        ],
        'ecommerce' => [
            'home' => ['hero', 'categories', 'featured_products', 'benefits', 'testimonials', 'newsletter', 'cta'],
            'about' => ['hero_about', 'story', 'values', 'team', 'sustainability', 'cta'],
            'contact' => ['hero_contact', 'contact_form', 'faq', 'shipping_info', 'cta'],
        ],
        'education' => [
            'home' => ['hero', 'programs', 'why_choose', 'instructors', 'testimonials', 'stats', 'faq', 'cta'],
            'about' => ['hero_about', 'mission', 'values', 'team', 'accreditation', 'facilities', 'cta'],
            'courses' => ['hero_courses', 'course_categories', 'featured_courses', 'instructors', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'enrollment_form', 'locations', 'faq', 'cta'],
        ],
        'general' => [
            'home' => ['hero', 'features', 'about_brief', 'services', 'testimonials', 'stats', 'faq', 'cta'],
            'about' => ['hero_about', 'story', 'values', 'team', 'cta'],
            'services' => ['hero_services', 'services_grid', 'process', 'testimonials', 'cta'],
            'contact' => ['hero_contact', 'contact_form', 'locations', 'cta'],
        ],
    ];

    /**
     * Section type definitions with required modules and layouts
     */
    private const SECTION_BLUEPRINTS = [
        'hero' => [
            'description' => 'Full-width hero with headline, subheadline, CTA button, and optional image',
            'layout' => '1_2,1_2',
            'modules' => ['heading h1', 'text subheadline', 'button primary'],
            'background' => 'gradient or image',
            'padding' => ['top' => 120, 'bottom' => 120],
        ],
        'hero_about' => [
            'description' => 'About page hero with company name and mission statement',
            'layout' => '1',
            'modules' => ['heading h1 centered', 'text centered'],
            'padding' => ['top' => 100, 'bottom' => 100],
        ],
        'hero_services' => [
            'description' => 'Services page hero introducing what you offer',
            'layout' => '1',
            'modules' => ['heading h1 centered', 'text centered', 'button centered'],
            'padding' => ['top' => 100, 'bottom' => 100],
        ],
        'hero_contact' => [
            'description' => 'Contact page hero encouraging connection',
            'layout' => '1',
            'modules' => ['heading h1 centered', 'text centered'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'features' => [
            'description' => '3-4 feature blurbs with icons showcasing key benefits',
            'layout' => '1_3,1_3,1_3',
            'modules' => ['blurb with icon'] ,
            'count' => 3,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'trust_logos' => [
            'description' => 'Row of client/partner logos for social proof',
            'layout' => '1',
            'modules' => ['heading h3 centered "Trusted By"', 'image row of logos'],
            'padding' => ['top' => 60, 'bottom' => 60],
            'background' => 'light gray',
        ],
        'testimonials' => [
            'description' => '2-3 customer testimonials with photos',
            'layout' => '1_3,1_3,1_3',
            'modules' => ['testimonial'],
            'count' => 3,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'pricing' => [
            'description' => '3 pricing tiers with features and CTAs',
            'layout' => '1_3,1_3,1_3',
            'modules' => ['pricing_table'],
            'count' => 3,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'team' => [
            'description' => '3-4 team member cards with photos and bios',
            'layout' => '1_4,1_4,1_4,1_4',
            'modules' => ['team_member'],
            'count' => 4,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'faq' => [
            'description' => 'Frequently asked questions in accordion format',
            'layout' => '2_3,1_3',
            'modules' => ['accordion with 5-6 items', 'blurb with contact info'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'cta' => [
            'description' => 'Final call-to-action section with compelling headline and button',
            'layout' => '1',
            'modules' => ['heading h2 centered', 'text centered', 'button centered primary'],
            'background' => 'primary color',
            'padding' => ['top' => 100, 'bottom' => 100],
        ],
        'stats' => [
            'description' => '3-4 impressive statistics with counters',
            'layout' => '1_4,1_4,1_4,1_4',
            'modules' => ['number_counter'],
            'count' => 4,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'contact_form' => [
            'description' => 'Contact form with company info sidebar',
            'layout' => '2_3,1_3',
            'modules' => ['contact_form', 'blurb with contact details'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'services_grid' => [
            'description' => '3-6 service cards with icons and descriptions',
            'layout' => '1_3,1_3,1_3',
            'modules' => ['blurb with icon'],
            'count' => 6,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'story' => [
            'description' => 'Company story with image and text',
            'layout' => '1_2,1_2',
            'modules' => ['image', 'heading h2 + text'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'values' => [
            'description' => '3-4 company values with icons',
            'layout' => '1_3,1_3,1_3',
            'modules' => ['blurb with icon'],
            'count' => 3,
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'process' => [
            'description' => '3-5 step process with numbers',
            'layout' => '1',
            'modules' => ['heading h2 centered', 'row with numbered steps'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'gallery' => [
            'description' => 'Image gallery showcasing work or facilities',
            'layout' => '1',
            'modules' => ['heading h2 centered', 'gallery 6-8 images'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
        'newsletter' => [
            'description' => 'Newsletter signup with incentive',
            'layout' => '1',
            'modules' => ['heading h3 centered', 'text centered', 'contact_form email only'],
            'background' => 'light gray',
            'padding' => ['top' => 60, 'bottom' => 60],
        ],
        'locations' => [
            'description' => 'Contact locations with map',
            'layout' => '1_2,1_2',
            'modules' => ['map', 'blurb with address and hours'],
            'padding' => ['top' => 80, 'bottom' => 80],
        ],
    ];

    /**
     * Typography scales for different contexts
     */
    private const TYPOGRAPHY_SCALES = [
        'hero' => ['h1' => 56, 'text' => 20],
        'section_heading' => ['h2' => 42, 'text' => 18],
        'card_heading' => ['h3' => 24, 'text' => 16],
        'body' => ['text' => 16, 'small' => 14],
    ];

    /**
     * Spacing system (in pixels)
     */
    private const SPACING = [
        'section_hero' => ['top' => 120, 'bottom' => 120],
        'section_normal' => ['top' => 80, 'bottom' => 80],
        'section_compact' => ['top' => 60, 'bottom' => 60],
        'element_gap' => 24,
        'row_gap' => 40,
    ];

    // ========================================
    // MAIN GENERATION METHOD
    // ========================================

    /**
     * Generate a complete website with professional AI prompts
     *
     * @param string $prompt User's website description
     * @param array $options Generation options
     * @return array Result with website structure or error
     */
    public static function generate(string $prompt, array $options = []): array
    {
        $industry = $options['industry'] ?? self::detectIndustry($prompt);
        $style = $options['style'] ?? 'modern';
        $pages = $options['pages'] ?? ['home', 'about', 'services', 'contact'];

        $ai = JTB_AI_Core::getInstance();

        if (!$ai->isConfigured()) {
            return [
                'ok' => false,
                'error' => 'AI is not configured. Please configure an AI provider in settings.'
            ];
        }

        try {
            $startTime = microtime(true);
            $stats = ['steps' => []];

            // Get industry template for default section layouts
            $industryTemplate = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['general'];
            // Colors from SINGLE SOURCE OF TRUTH (JTB_AI_Styles), NOT from INDUSTRY_TEMPLATES
            $colors = JTB_AI_Styles::getIndustryColors($industry);

            // ==========================================
            // STEP 1: OUTLINE (AI - small JSON, 3000 tokens)
            // Ask AI ONLY for: section types per page + color scheme
            // ==========================================
            $step1Start = microtime(true);
            error_log('JTB_AI_Website: === STEP 1: OUTLINE ===');

            $outlineResult = self::step1_websiteOutline($prompt, $pages, $industry, $style, $ai, $options);

            if (!$outlineResult['ok']) {
                throw new \Exception('Step 1 (Outline) failed: ' . ($outlineResult['error'] ?? 'unknown'));
            }

            $outline = $outlineResult['outline'];
            $stats['steps']['outline'] = [
                'time_ms' => round((microtime(true) - $step1Start) * 1000),
                'pages_planned' => count($outline['pages'] ?? [])
            ];
            error_log('JTB_AI_Website: Outline done - ' . count($outline['pages'] ?? []) . ' pages planned');

            // ==========================================
            // STEP 2: WIREFRAME (AI - ~2000 tokens per page)
            // AI generates full JTB JSON structure per page
            // ==========================================
            $step2Start = microtime(true);
            error_log('JTB_AI_Website: === STEP 2: AI WIREFRAME ===');

            $website = self::step2_aiWireframe($outline, $prompt, $industry, $ai, $options);

            $stats['steps']['wireframe'] = [
                'time_ms' => round((microtime(true) - $step2Start) * 1000),
                'total_sections' => self::countSections($website)
            ];
            error_log('JTB_AI_Website: AI Wireframe done - ' . self::countSections($website) . ' total sections');

            // ==========================================
            // STEP 3: STYLE (AI - ~1000 tokens)
            // AI generates colors, typography, spacing per module
            // ==========================================
            $step3Start = microtime(true);
            error_log('JTB_AI_Website: === STEP 3: AI STYLES ===');

            $website = self::step3_aiStyles($website, $outline, $style, $industry, $prompt, $ai, $options);

            $stats['steps']['style'] = [
                'time_ms' => round((microtime(true) - $step3Start) * 1000)
            ];
            error_log('JTB_AI_Website: AI Styles applied');

            // ==========================================
            // STEP 4: CONTENT (AI - per page, ~4000 tokens each)
            // AI generates text content for all modules
            // ==========================================
            $step4Start = microtime(true);
            error_log('JTB_AI_Website: === STEP 4: AI CONTENT ===');

            // AI-generated content (page by page to avoid truncation)
            $website = self::step4_aiContent($website, $prompt, $outline, $ai, $options);

            $stats['steps']['content'] = [
                'time_ms' => round((microtime(true) - $step4Start) * 1000)
            ];
            error_log('JTB_AI_Website: Content done');

            // ==========================================
            // STEP 5: IMAGES (Pexels API - No AI)
            // ==========================================
            $step5Start = microtime(true);
            error_log('JTB_AI_Website: === STEP 5: IMAGES ===');

            $website = self::step5_websiteImages($website, $industry, $prompt);

            $stats['steps']['images'] = [
                'time_ms' => round((microtime(true) - $step5Start) * 1000)
            ];
            error_log('JTB_AI_Website: Images done');

            // ==========================================
            // FINAL: Add IDs, normalize, validate
            // ==========================================
            $website = self::addUniqueIds($website);
            $website = self::postProcess($website, $options);

            $timeMs = round((microtime(true) - $startTime) * 1000);
            $stats['time_ms'] = $timeMs;
            $stats['provider'] = $ai->getProvider();
            $stats['sections_generated'] = self::countSections($website);

            error_log("JTB_AI_Website: === COMPLETE === Total: {$timeMs}ms");

            return [
                'ok' => true,
                'website' => $website,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::generate error: ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    // ========================================
    // STEP 1: WEBSITE OUTLINE (AI - small JSON)
    // ========================================

    /**
     * Ask AI for website outline: section types per page + color scheme
     * Output is TINY (~500 tokens) - no truncation risk
     */
    private static function step1_websiteOutline(string $prompt, array $pages, string $industry, string $style, JTB_AI_Core $ai, array $options = []): array
    {
        $pagesList = implode(', ', $pages);
        $sectionTypes = implode(', ', array_keys(self::SECTION_BLUEPRINTS));

        $systemPrompt = <<<SYS
You are a web design architect. Generate a website outline as JSON.

AVAILABLE SECTION TYPES: {$sectionTypes}

RULES:
- Each page MUST have 6-10 sections
- Home page should have 8-10 sections
- About page should have 6-8 sections
- Services page should have 7-9 sections
- Contact page should have 5-7 sections
- Header and footer are separate from pages
- Header sections: just "header" type
- Footer sections: just "footer" type
- Choose sections appropriate for the industry and page purpose
- Provide a color_scheme with: primary, secondary, accent, text, heading, background, surface colors

OUTPUT FORMAT (JSON only, no explanation):
{
  "color_scheme": {
    "primary": "#hex", "secondary": "#hex", "accent": "#hex",
    "text": "#hex", "heading": "#hex", "background": "#ffffff", "surface": "#f8f9fa"
  },
  "brand_voice": "professional|friendly|bold|elegant",
  "header": ["header"],
  "footer": ["footer"],
  "pages": {
    "home": ["hero", "trust_logos", "features", "how_it_works", "testimonials", "stats", "pricing", "faq", "cta"],
    "about": ["hero_about", "story", "values", "team", "stats", "cta"]
  }
}
SYS;

        $userPrompt = <<<USER
Create a website outline for this business:

{$prompt}

REQUIREMENTS:
- Industry: {$industry}
- Style: {$style}
- Pages needed: {$pagesList}

Generate the JSON outline now. ONLY output valid JSON.
USER;

        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 3000,
            'temperature' => 0.8
        ];
        if (!empty($options['model'])) {
            $queryOptions['model'] = $options['model'];
        }

        $response = $ai->query($userPrompt, $queryOptions);

        if (!$response['ok']) {
            return ['ok' => false, 'error' => $response['error'] ?? 'AI query failed'];
        }

        $outline = self::parseJsonResponse($response['text'] ?? '');

        if (!$outline || empty($outline['pages'])) {
            // Fallback: use industry template defaults
            error_log('JTB_AI_Website: Outline parse failed, using industry defaults');
            $outline = self::getDefaultWebsiteOutline($pages, $industry);
        }

        // Ensure color_scheme exists - use JTB_AI_Styles as single source
        if (empty($outline['color_scheme'])) {
            $indColors = JTB_AI_Styles::getIndustryColors($industry);
            $outline['color_scheme'] = [
                'primary' => $indColors['primary'] ?? '#3b82f6',
                'secondary' => $indColors['secondary'] ?? '#1e40af',
                'accent' => $indColors['accent'] ?? '#10b981',
                'text' => '#374151',
                'heading' => '#111827',
                'background' => '#ffffff',
                'surface' => '#f8fafc',
            ];
        }

        return ['ok' => true, 'outline' => $outline];
    }

    /**
     * Default website outline from INDUSTRY_TEMPLATES
     */
    private static function getDefaultWebsiteOutline(array $pages, string $industry): array
    {
        $template = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['general'];
        // Colors from SINGLE SOURCE OF TRUTH
        $indColors = JTB_AI_Styles::getIndustryColors($industry);

        $outline = [
            'color_scheme' => [
                'primary' => $indColors['primary'] ?? '#3b82f6',
                'secondary' => $indColors['secondary'] ?? '#1e40af',
                'accent' => $indColors['accent'] ?? '#10b981',
                'text' => '#374151',
                'heading' => '#111827',
                'background' => '#ffffff',
                'surface' => '#f8fafc',
            ],
            'brand_voice' => 'professional',
            'header' => ['header'],
            'footer' => ['footer'],
            'pages' => [],
        ];

        foreach ($pages as $page) {
            $outline['pages'][$page] = $template[$page] ?? $template['home'] ?? ['hero', 'features', 'testimonials', 'cta'];
        }

        return $outline;
    }

    // ========================================
    // STEP 2: WIREFRAME (AI-Generated Structure)
    // ========================================

    /**
     * AI generates the full JTB JSON structure (section/row/column/module)
     * WITHOUT text content - only structure + module types.
     * ~2000 tokens per page, called once for ALL pages.
     *
     * @since 2026-02-05 - Replaced hardcoded wireframe with AI generation
     */
    private static function step2_aiWireframe(array $outline, string $prompt, string $industry, JTB_AI_Core $ai, array $options = []): array
    {
        $pages = array_keys($outline['pages'] ?? []);
        $pagesList = implode(', ', $pages);
        $colors = $outline['color_scheme'] ?? [];

        // Build section requirements from outline
        $pagesOutline = '';
        foreach ($outline['pages'] ?? [] as $pageName => $sectionTypes) {
            $pagesOutline .= "- {$pageName}: " . implode(', ', $sectionTypes) . "\n";
        }

        // Get available module schemas from registry
        $schemas = JTB_AI_Schema::getCompactSchemasForAI();

        $systemPrompt = <<<SYS
You are a web architect. Generate JTB JSON structure for a website.

OUTPUT FORMAT: Valid JSON, NO markdown, NO explanation.
{
  "header": {
    "sections": [/* 1 section: site_logo + menu + button */]
  },
  "footer": {
    "sections": [/* 1-2 sections: multi-column footer + copyright */]
  },
  "pages": {
    "home": {"title": "Home", "sections": [/* 6-10 sections */]},
    "about": {"title": "About", "sections": [/* 6-8 sections */]}
  }
}

STRUCTURE RULES:
1. Every section: {"type": "section", "id": "unique_id", "attrs": {"padding": {"top": N, "right": 0, "bottom": N, "left": 0}}, "_section_type": "hero|features|etc", "children": [rows]}
2. Every row: {"type": "row", "id": "unique_id", "attrs": {"columns": "LAYOUT"}, "children": [columns]}
3. Every column: {"type": "column", "id": "unique_id", "attrs": {}, "children": [modules]}
4. Every module: {"type": "MODULE_TYPE", "id": "unique_id", "attrs": {}}
5. Module attrs should be EMPTY or contain only structural info (level for heading, columns layout)
6. DO NOT put any text content, colors, or styles in attrs - those come in later steps
7. Use UNDERSCORES in module types: site_logo, team_member, pricing_table, number_counter, contact_form, social_icons
8. accordion modules MUST have children array with accordion_item elements

COLUMN LAYOUTS (row "columns" attr):
- "1" = full width
- "1_2,1_2" = two equal columns
- "1_3,1_3,1_3" = three equal columns
- "1_4,1_4,1_4,1_4" = four equal columns
- "2_3,1_3" = two-thirds + one-third
- "1_3,2_3" = one-third + two-thirds
- "1_4,1_2,1_4" = quarter + half + quarter (for headers)

AVAILABLE MODULES:
{$schemas}

HEADER PATTERN: section > row(1_4,1_2,1_4) > [site_logo, menu, button]
FOOTER PATTERN: section > row(1_4,1_4,1_4,1_4) > [logo+text, heading+text, heading+text, heading+text+social_icons] + row(1) > [text copyright]

SECTION PATTERNS:
- hero: row(1_2,1_2) > [heading+text+button, image]
- features: row(1) > [heading+text] + row(1_3,1_3,1_3) > [blurb, blurb, blurb]
- testimonials: row(1) > [heading] + row(1_3,1_3,1_3) > [testimonial, testimonial, testimonial]
- pricing: row(1) > [heading+text] + row(1_3,1_3,1_3) > [pricing_table x3]
- team: row(1) > [heading] + row(1_4,1_4,1_4,1_4) > [team_member x4]
- stats: row(1_4,1_4,1_4,1_4) > [number_counter x4]
- faq: row(1) > [heading] + row(2_3,1_3) > [accordion, blurb]
- cta: row(1) > [heading+text+button]
- contact_form: row(1) > [heading] + row(2_3,1_3) > [contact_form, blurb]
- story: row(1_2,1_2) > [image, heading+text]
- values/benefits: row(1) > [heading] + row(1_3,1_3,1_3) > [blurb x3]
- newsletter: row(1) > [heading+text+contact_form]

Generate IDs like: s1, r1, c1, m1, s2, r2, etc. (short, unique within output)
SYS;

        $userPrompt = <<<USER
Business: {$prompt}
Industry: {$industry}
Pages: {$pagesList}

Section outline per page:
{$pagesOutline}

Generate the COMPLETE JTB JSON structure. Every module attrs should be EMPTY (content and styles added later).
Output ONLY valid JSON.
USER;

        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 8000,
            'temperature' => 0.7
        ];
        if (!empty($options['model'])) {
            $queryOptions['model'] = $options['model'];
        }

        $response = $ai->query($userPrompt, $queryOptions);

        if (!$response['ok']) {
            error_log('JTB_AI_Website: step2_aiWireframe AI failed, using fallback');
            return self::step2_fallbackWireframe($outline);
        }

        $website = self::parseJsonResponse($response['text'] ?? '');

        if (!$website || empty($website['pages'])) {
            error_log('JTB_AI_Website: step2_aiWireframe parse failed, using fallback');
            return self::step2_fallbackWireframe($outline);
        }

        // Ensure required top-level keys
        if (!isset($website['header'])) {
            $website['header'] = ['sections' => []];
        }
        if (!isset($website['footer'])) {
            $website['footer'] = ['sections' => []];
        }

        return $website;
    }

    /**
     * Fallback wireframe when AI is unavailable - minimal skeleton
     * Uses outline section types to create bare structure
     */
    private static function step2_fallbackWireframe(array $outline): array
    {
        $website = [
            'header' => ['sections' => [[
                'type' => 'section', 'id' => 'hdr_s1', 'attrs' => ['padding' => ['top' => 16, 'right' => 0, 'bottom' => 16, 'left' => 0]],
                '_section_type' => 'header',
                'children' => [[
                    'type' => 'row', 'id' => 'hdr_r1', 'attrs' => ['columns' => '1_4,1_2,1_4'],
                    'children' => [
                        ['type' => 'column', 'id' => 'hdr_c1', 'attrs' => [], 'children' => [
                            ['type' => 'site_logo', 'id' => 'hdr_m1', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'id' => 'hdr_c2', 'attrs' => [], 'children' => [
                            ['type' => 'menu', 'id' => 'hdr_m2', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'id' => 'hdr_c3', 'attrs' => [], 'children' => [
                            ['type' => 'button', 'id' => 'hdr_m3', 'attrs' => []]
                        ]]
                    ]
                ]]
            ]]],
            'footer' => ['sections' => [[
                'type' => 'section', 'id' => 'ftr_s1', 'attrs' => ['padding' => ['top' => 60, 'right' => 0, 'bottom' => 20, 'left' => 0]],
                '_section_type' => 'footer',
                'children' => [[
                    'type' => 'row', 'id' => 'ftr_r1', 'attrs' => ['columns' => '1_3,1_3,1_3'],
                    'children' => [
                        ['type' => 'column', 'id' => 'ftr_c1', 'attrs' => [], 'children' => [
                            ['type' => 'heading', 'id' => 'ftr_m1', 'attrs' => []],
                            ['type' => 'text', 'id' => 'ftr_m2', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'id' => 'ftr_c2', 'attrs' => [], 'children' => [
                            ['type' => 'heading', 'id' => 'ftr_m3', 'attrs' => []],
                            ['type' => 'text', 'id' => 'ftr_m4', 'attrs' => []]
                        ]],
                        ['type' => 'column', 'id' => 'ftr_c3', 'attrs' => [], 'children' => [
                            ['type' => 'heading', 'id' => 'ftr_m5', 'attrs' => []],
                            ['type' => 'text', 'id' => 'ftr_m6', 'attrs' => []]
                        ]]
                    ]
                ]]
            ]]],
            'pages' => [],
        ];

        $idCounter = 100;
        foreach ($outline['pages'] ?? [] as $pageName => $sectionTypes) {
            $pageSections = [];
            foreach ($sectionTypes as $sectionType) {
                $sid = 'p' . $idCounter++;
                $section = [
                    'type' => 'section', 'id' => $sid, 'attrs' => ['padding' => ['top' => 80, 'right' => 0, 'bottom' => 80, 'left' => 0]],
                    '_section_type' => $sectionType,
                    'children' => [[
                        'type' => 'row', 'id' => 'r' . $idCounter++, 'attrs' => ['columns' => '1'],
                        'children' => [['type' => 'column', 'id' => 'c' . $idCounter++, 'attrs' => [], 'children' => [
                            ['type' => 'heading', 'id' => 'm' . $idCounter++, 'attrs' => []],
                            ['type' => 'text', 'id' => 'm' . $idCounter++, 'attrs' => []]
                        ]]]
                    ]]
                ];
                $pageSections[] = $section;
            }
            $website['pages'][$pageName] = [
                'title' => ucfirst(str_replace('_', ' ', $pageName)),
                'sections' => $pageSections,
            ];
        }

        return $website;
    }

    // ========================================
    // STEP 3: STYLES (AI-Generated)
    // ========================================

    /**
     * AI generates visual styles for the website structure.
     * Applies colors, typography, spacing to all modules.
     * ~1000-2000 tokens output.
     *
     * @since 2026-02-05 - Replaced hardcoded styles with AI generation
     */
    private static function step3_aiStyles(array $website, array $outline, string $style, string $industry, string $prompt, JTB_AI_Core $ai, array $options = []): array
    {
        $colors = $outline['color_scheme'] ?? [];
        $primary = $colors['primary'] ?? '#3b82f6';
        $secondary = $colors['secondary'] ?? '#1e40af';
        $accent = $colors['accent'] ?? '#10b981';
        $textColor = $colors['text'] ?? '#374151';
        $headingColor = $colors['heading'] ?? '#111827';

        // Collect all module IDs with their types and section contexts
        $moduleMap = [];
        $allParts = [];
        if (!empty($website['header']['sections'])) $allParts['header'] = $website['header']['sections'];
        if (!empty($website['footer']['sections'])) $allParts['footer'] = $website['footer']['sections'];
        foreach ($website['pages'] ?? [] as $pageName => $page) {
            if (!empty($page['sections'])) $allParts["page:{$pageName}"] = $page['sections'];
        }

        foreach ($allParts as $context => $sections) {
            foreach ($sections as $section) {
                $sType = $section['_section_type'] ?? 'unknown';
                self::collectModuleMapRecursive($section['children'] ?? [], $moduleMap, $context, $sType);
            }
        }

        // Build compact module list for AI
        $moduleList = '';
        foreach ($moduleMap as $id => $info) {
            $moduleList .= "- {$id}: {$info['type']} in {$info['section_type']} ({$info['context']})\n";
        }

        $systemPrompt = <<<SYS
You are a visual designer. Apply professional styles to website modules.

COLOR SCHEME:
- primary: {$primary}
- secondary: {$secondary}
- accent: {$accent}
- text: {$textColor}
- heading: {$headingColor}
- background: #ffffff
- surface: #f8fafc
- dark (footer bg): #1f2937

STYLE: {$style}

OUTPUT: JSON object where keys are module/section IDs, values are style attributes to SET.

RULES:
1. Section backgrounds: hero/cta = primary color, trust/stats = surface, footer = #1f2937, alternate white/surface for others
2. Section padding: hero = {"top":120,"right":0,"bottom":120,"left":0}, normal = {"top":80,"right":0,"bottom":80,"left":0}, compact = {"top":60,"right":0,"bottom":60,"left":0}, header = {"top":16,"right":0,"bottom":16,"left":0}
3. Headings on dark bg: text_color=#ffffff. On light bg: text_color=heading color
4. h1: font_size=56, level="h1". h2: font_size=42, level="h2". h3: font_size=24, level="h3". h4: font_size=16
5. Text on dark bg: text_color=#ffffff or #e2e8f0. On light bg: text_color=text color
6. Buttons on dark bg: background_color=#ffffff, text_color=primary. On light bg: background_color=primary, text_color=#ffffff
7. Button: border_radius={"top_left":8,"top_right":8,"bottom_right":8,"bottom_left":8}
8. Blurb: icon_color=primary, title_color=heading color, text_color=text color
9. Footer headings: text_color=#ffffff, level="h4", font_size=16
10. Footer text: text_color=#9ca3af, font_size=14
11. Header box_shadow: "0 1px 3px rgba(0,0,0,0.1)"
12. font_weight for headings: "700", for body: "400"

For SECTIONS, include: background_color, padding
For MODULES, include: relevant style attrs (text_color, font_size, level, background_color, etc.)

Output ONLY valid JSON with IDs as keys and style objects as values.
SYS;

        $userPrompt = <<<USER
Business: {$prompt}
Industry: {$industry}

Apply styles to these elements:
{$moduleList}

Output JSON: {"element_id": {"attr": "value", ...}, ...}
ONLY valid JSON.
USER;

        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 4000,
            'temperature' => 0.5
        ];
        if (!empty($options['model'])) {
            $queryOptions['model'] = $options['model'];
        }

        $response = $ai->query($userPrompt, $queryOptions);

        $styleMap = null;
        if ($response['ok']) {
            $styleMap = self::parseJsonResponse($response['text'] ?? '');
        }

        if ($styleMap) {
            // Apply styles from AI response
            self::applyStyleMapToWebsite($website, $styleMap);
            error_log('JTB_AI_Website: AI styles applied (' . count($styleMap) . ' elements styled)');
        } else {
            // Fallback: apply basic styles deterministically
            error_log('JTB_AI_Website: AI styles failed, applying fallback styles');
            self::applyFallbackStyles($website, $colors);
        }

        // Always build theme_settings from color_scheme (needed by preview UI)
        $headingFont = $outline['heading_font'] ?? 'Inter';
        $bodyFont = $outline['body_font'] ?? 'Inter';

        $website['theme_settings'] = [
            'colors' => [
                'primary' => $primary,
                'secondary' => $secondary,
                'accent' => $accent,
                'text' => $textColor,
                'heading' => $headingColor,
                'background' => '#ffffff',
                'surface' => '#f8fafc',
            ],
            'typography' => [
                'heading_font' => $headingFont,
                'body_font' => $bodyFont,
                'h1_size' => '56px',
                'h2_size' => '42px',
                'h3_size' => '24px',
                'body_size' => '16px',
                'heading_weight' => '700',
                'body_weight' => '400',
            ],
            'spacing' => [
                'section_padding' => '80px',
                'hero_padding' => '120px',
                'element_gap' => '24px',
            ],
        ];

        return $website;
    }

    /**
     * Collect module IDs with their types and context for AI styling
     */
    private static function collectModuleMapRecursive(array $children, array &$map, string $context, string $sectionType): void
    {
        foreach ($children as $child) {
            $type = $child['type'] ?? '';
            $id = $child['id'] ?? '';

            if (in_array($type, ['section'])) {
                $sType = $child['_section_type'] ?? $sectionType;
                if ($id) {
                    $map[$id] = ['type' => 'section', 'context' => $context, 'section_type' => $sType];
                }
                self::collectModuleMapRecursive($child['children'] ?? [], $map, $context, $sType);
            } elseif (in_array($type, ['row', 'column'])) {
                self::collectModuleMapRecursive($child['children'] ?? [], $map, $context, $sectionType);
            } else {
                if ($id) {
                    $map[$id] = ['type' => $type, 'context' => $context, 'section_type' => $sectionType];
                }
            }
        }
    }

    /**
     * Apply AI-generated style map to entire website structure
     */
    private static function applyStyleMapToWebsite(array &$website, array $styleMap): void
    {
        // Apply to header
        if (!empty($website['header']['sections'])) {
            self::applyStyleMapRecursive($website['header']['sections'], $styleMap);
        }
        // Apply to footer
        if (!empty($website['footer']['sections'])) {
            self::applyStyleMapRecursive($website['footer']['sections'], $styleMap);
        }
        // Apply to pages
        foreach ($website['pages'] as &$page) {
            if (!empty($page['sections'])) {
                self::applyStyleMapRecursive($page['sections'], $styleMap);
            }
        }
    }

    /**
     * Recursively apply style map by matching element IDs
     */
    private static function applyStyleMapRecursive(array &$elements, array $styleMap): void
    {
        foreach ($elements as &$el) {
            $id = $el['id'] ?? '';
            if ($id && isset($styleMap[$id]) && is_array($styleMap[$id])) {
                foreach ($styleMap[$id] as $attr => $value) {
                    $el['attrs'][$attr] = $value;
                }
            }
            if (!empty($el['children'])) {
                self::applyStyleMapRecursive($el['children'], $styleMap);
            }
        }
    }

    /**
     * Fallback: apply basic styles when AI styling fails
     */
    private static function applyFallbackStyles(array &$website, array $colors): void
    {
        $primary = $colors['primary'] ?? '#3b82f6';
        $textColor = $colors['text'] ?? '#374151';
        $headingColor = $colors['heading'] ?? '#111827';

        // Header
        if (!empty($website['header']['sections'])) {
            foreach ($website['header']['sections'] as &$s) {
                $s['attrs']['background_color'] = '#ffffff';
                $s['attrs']['box_shadow'] = '0 1px 3px rgba(0,0,0,0.1)';
            }
        }

        // Footer
        if (!empty($website['footer']['sections'])) {
            foreach ($website['footer']['sections'] as &$s) {
                $s['attrs']['background_color'] = '#1f2937';
            }
        }

        // Pages
        $isAlt = false;
        foreach ($website['pages'] as &$page) {
            foreach ($page['sections'] as &$s) {
                $sType = $s['_section_type'] ?? '';
                if (in_array($sType, ['hero', 'cta'])) {
                    $s['attrs']['background_color'] = $primary;
                } elseif ($isAlt) {
                    $s['attrs']['background_color'] = '#f8fafc';
                } else {
                    $s['attrs']['background_color'] = '#ffffff';
                }
                $isAlt = !$isAlt;
            }
        }
    }

    // ========================================
    // STEP 4: CONTENT (AI-Generated per page)
    // ========================================

    /**
     * Enhance content with AI (page by page to avoid truncation)
     */
    private static function step4_aiContent(array $website, string $prompt, array $outline, JTB_AI_Core $ai, array $options = []): array
    {
        // Generate content for header and footer via AI
        self::generateHeaderFooterContent($website, $prompt, $ai, $options);

        // Build content field reference DYNAMICALLY from module schemas
        $contentFieldsRef = self::buildContentFieldsReference();

        // Generate content for each page separately (4000 tokens each)
        foreach ($website['pages'] as $pageName => &$page) {
            $modules = self::collectModulesForContent($page['sections']);

            if (empty($modules)) continue;

            $modulesList = '';
            foreach ($modules as $mod) {
                $modulesList .= "- ID: {$mod['id']}, Type: {$mod['type']}, Role: {$mod['role']}\n";
            }

            $systemPrompt = <<<SYS
You are a professional copywriter. Generate content for website modules.
Output JSON only. Each key is a module ID, value is an object with the module's content fields.

MODULE CONTENT FIELDS REFERENCE:
{$contentFieldsRef}

RULES:
- Content must be specific to the business described
- Use professional, persuasive language
- No placeholder or lorem ipsum text
- Headlines should be compelling and concise (5-10 words)
- Body text should be 1-3 sentences
- For accordion: generate children array with accordion_item objects
- For tabs: generate children array with tabs_item objects
- Leave image src fields empty (images are added in a later step)
- Output ONLY valid JSON
SYS;

            $brandVoice = $outline['brand_voice'] ?? 'professional';
            $userPrompt = <<<USER
Business: {$prompt}
Industry: {$brandVoice}
Page: {$pageName}

Generate content for these modules:
{$modulesList}

Output JSON with module IDs as keys. ONLY valid JSON.
USER;

            $queryOptions = [
                'system_prompt' => $systemPrompt,
                'max_tokens' => 4000,
                'temperature' => 0.7
            ];
            if (!empty($options['model'])) {
                $queryOptions['model'] = $options['model'];
            }

            try {
                $response = $ai->query($userPrompt, $queryOptions);

                if ($response['ok']) {
                    $contentMap = self::parseJsonResponse($response['text'] ?? '');
                    if ($contentMap) {
                        self::mergeContentIntoSections($page['sections'], $contentMap);
                        error_log("JTB_AI_Website: AI content applied for page '{$pageName}'");
                    }
                }
            } catch (\Exception $e) {
                error_log("JTB_AI_Website: AI content failed for page '{$pageName}': " . $e->getMessage());
                // Continue with placeholder content
            }
        }

        return $website;
    }

    /**
     * Generate AI content for header and footer
     */
    private static function generateHeaderFooterContent(array &$website, string $prompt, JTB_AI_Core $ai, array $options = []): void
    {
        $currentYear = date('Y');

        // Collect modules from header and footer
        $headerModules = !empty($website['header']['sections'])
            ? self::collectModulesForContent($website['header']['sections'])
            : [];
        $footerModules = !empty($website['footer']['sections'])
            ? self::collectModulesForContent($website['footer']['sections'])
            : [];

        $allModules = array_merge($headerModules, $footerModules);
        if (empty($allModules)) return;

        $modulesList = '';
        foreach ($allModules as $mod) {
            $modulesList .= "- ID: {$mod['id']}, Type: {$mod['type']}, Role: {$mod['role']}, Location: " .
                (in_array($mod, $headerModules) ? 'header' : 'footer') . "\n";
        }

        // Build content field reference DYNAMICALLY
        $contentFieldsRef = self::buildContentFieldsReference();

        $systemPrompt = <<<SYS
You are a professional copywriter. Generate content for website header and footer modules.
Output JSON only. Each key is a module ID, value is an object with the module's content fields.

MODULE CONTENT FIELDS REFERENCE:
{$contentFieldsRef}

RULES:
- Header CTA button should be short and action-oriented (2-3 words max)
- Footer headings should be category names like "Company", "Services", "Resources", "Get in Touch"
- Footer text should include relevant info (company description, links, contact details)
- Footer copyright should include the business name and current year ({$currentYear})
- Content must be specific to the business described
- All text must be professional, NOT generic placeholders
- Output ONLY valid JSON, no markdown
SYS;

        $userPrompt = <<<USER
Business: {$prompt}

Generate content for these header/footer modules:
{$modulesList}

Output JSON with module IDs as keys. ONLY valid JSON.
USER;

        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 2000,
            'temperature' => 0.7
        ];
        if (!empty($options['model'])) {
            $queryOptions['model'] = $options['model'];
        }

        try {
            $response = $ai->query($userPrompt, $queryOptions);

            if ($response['ok']) {
                $contentMap = self::parseJsonResponse($response['text'] ?? '');
                if ($contentMap) {
                    if (!empty($website['header']['sections'])) {
                        self::mergeContentIntoSections($website['header']['sections'], $contentMap);
                    }
                    if (!empty($website['footer']['sections'])) {
                        self::mergeContentIntoSections($website['footer']['sections'], $contentMap);
                    }
                    error_log("JTB_AI_Website: AI content applied for header/footer");
                }
            }
        } catch (\Exception $e) {
            error_log("JTB_AI_Website: AI content failed for header/footer: " . $e->getMessage());
            // Continue with placeholder content
        }
    }

    /**
     * Collect modules that need content generation
     */
    private static function collectModulesForContent(array $sections): array
    {
        $modules = [];
        foreach ($sections as $section) {
            self::collectModulesRecursive($section['children'] ?? [], $modules);
        }
        return $modules;
    }

    private static function collectModulesRecursive(array $children, array &$modules): void
    {
        $structureTypes = ['row', 'column', 'section'];

        foreach ($children as $child) {
            $type = $child['type'] ?? '';
            if (in_array($type, $structureTypes)) {
                self::collectModulesRecursive($child['children'] ?? [], $modules);
                continue;
            }
            // Collect ALL non-structure modules for AI content generation
            if (!empty($type)) {
                $modules[] = [
                    'id' => $child['id'] ?? '',
                    'type' => $type,
                    'role' => $child['attrs']['_role'] ?? ''
                ];
            }
            // Recurse into children (accordion_item, tabs_item, slider_item, etc.)
            if (!empty($child['children'])) {
                self::collectModulesRecursive($child['children'], $modules);
            }
        }
    }

    /**
     * Build DYNAMIC content fields reference for AI prompts.
     * Reads ALL modules from JTB_Registry and extracts their content fields.
     * No hardcoded module lists - everything comes from Registry.
     *
     * @since 2026-02-05 - Replaced hardcoded 8-module list
     */
    private static function buildContentFieldsReference(): string
    {
        $structureTypes = ['section', 'row', 'column'];
        $lines = [];

        try {
            $registry = JTB_Registry::all();

            foreach ($registry as $slug => $className) {
                // Skip structure modules
                if (in_array($slug, $structureTypes)) continue;

                $instance = JTB_Registry::get($slug);
                if (!$instance) continue;

                $fields = $instance->getContentFields();
                if (empty($fields)) continue;

                // Extract field names with their types for AI reference
                $fieldParts = [];
                foreach ($fields as $fieldName => $fieldDef) {
                    $type = $fieldDef['type'] ?? 'text';
                    $desc = '';

                    // Add value hints based on field type
                    if ($type === 'select' && !empty($fieldDef['options'])) {
                        $opts = array_keys($fieldDef['options']);
                        $desc = '(' . implode('|', array_slice($opts, 0, 6)) . ')';
                    } elseif ($type === 'toggle') {
                        $desc = '(true|false)';
                    } elseif ($type === 'richtext' || $type === 'textarea') {
                        $desc = '(HTML text)';
                    } elseif ($type === 'url' || $type === 'upload') {
                        $desc = '(URL)';
                    } elseif ($type === 'number' || $type === 'range') {
                        $min = $fieldDef['min'] ?? '';
                        $max = $fieldDef['max'] ?? '';
                        if ($min !== '' || $max !== '') {
                            $desc = "({$min}-{$max})";
                        }
                    }

                    $fieldParts[] = $fieldName . ($desc ? $desc : '');
                }

                // Add child info
                $childNote = '';
                if ($instance->child_slug) {
                    $childNote = " [children: {$instance->child_slug}]";
                }

                $lines[] = "{$slug}: " . implode(', ', $fieldParts) . $childNote;
            }
        } catch (\Exception $e) {
            error_log('JTB_AI_Website: buildContentFieldsReference failed: ' . $e->getMessage());
            // Return minimal fallback
            return "heading: text, level\ntext: content\nbutton: text, link_url\nblurb: title, content, font_icon\nimage: src, alt";
        }

        return implode("\n", $lines);
    }

    /**
     * Merge AI-generated content back into sections
     */
    private static function mergeContentIntoSections(array &$sections, array $contentMap): void
    {
        foreach ($sections as &$section) {
            if (!empty($section['children'])) {
                self::mergeContentRecursive($section['children'], $contentMap);
            }
        }
    }

    private static function mergeContentRecursive(array &$children, array $contentMap): void
    {
        foreach ($children as &$child) {
            $type = $child['type'] ?? '';
            $id = $child['id'] ?? '';

            if (in_array($type, ['row', 'column', 'section'])) {
                if (!empty($child['children'])) {
                    self::mergeContentRecursive($child['children'], $contentMap);
                }
                continue;
            }

            if (isset($contentMap[$id]) && is_array($contentMap[$id])) {
                // Merge AI content into module attrs (without overwriting styles)
                foreach ($contentMap[$id] as $key => $value) {
                    if (!in_array($key, ['background_color', 'text_color', 'font_size', 'padding', 'border_radius'])) {
                        $child['attrs'][$key] = $value;
                    }
                }
            }
        }
    }

    // ========================================
    // STEP 5: IMAGES (Pexels API)
    // ========================================

    /**
     * Enrich image modules with Pexels stock photos
     */
    private static function step5_websiteImages(array $website, string $industry, string $prompt): array
    {
        if (!class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') || !JTB_AI_Pexels::isConfigured()) {
            error_log('JTB_AI_Website: Pexels not configured, skipping image enrichment');
            return $website;
        }

        // Enrich page images
        foreach ($website['pages'] as &$page) {
            foreach ($page['sections'] as &$section) {
                $sType = $section['_section_type'] ?? '';
                if (!empty($section['children'])) {
                    self::enrichImagesRecursive($section['children'], $sType, $industry, $prompt);
                }
            }
        }

        return $website;
    }

    /**
     * Recursively enrich image modules with Pexels photos
     */
    private static function enrichImagesRecursive(array &$children, string $sectionType, string $industry, string $prompt): void
    {
        foreach ($children as &$child) {
            $type = $child['type'] ?? '';

            if (in_array($type, ['row', 'column'])) {
                if (!empty($child['children'])) {
                    self::enrichImagesRecursive($child['children'], $sectionType, $industry, $prompt);
                }
                continue;
            }

            if ($type === 'image' && empty($child['attrs']['src'])) {
                try {
                    $role = $child['attrs']['_role'] ?? '';
                    if (str_contains($role, 'hero')) {
                        $result = JTB_AI_Pexels::getHeroImage(['industry' => $industry]);
                    } elseif (str_contains($role, 'story') || str_contains($role, 'about')) {
                        $result = JTB_AI_Pexels::getAboutImage(['industry' => $industry]);
                    } else {
                        $result = JTB_AI_Pexels::searchPhotos($industry . ' business', ['per_page' => 1, 'orientation' => 'landscape']);
                    }
                    if (!empty($result['photos'][0]['src']['large'])) {
                        $child['attrs']['src'] = $result['photos'][0]['src']['large'];
                    }
                } catch (\Exception $e) {
                    // Skip image enrichment on error
                }
            }

            if ($type === 'team_member' && empty($child['attrs']['portrait_url'])) {
                try {
                    $result = JTB_AI_Pexels::getPersonPhoto(['role' => 'professional']);
                    if (!empty($result['photos'][0]['src']['medium'])) {
                        $child['attrs']['portrait_url'] = $result['photos'][0]['src']['medium'];
                    }
                } catch (\Exception $e) {
                    // Skip
                }
            }

            if ($type === 'testimonial' && empty($child['attrs']['portrait_url'])) {
                try {
                    $result = JTB_AI_Pexels::getPersonPhoto([]);
                    if (!empty($result['photos'][0]['src']['medium'])) {
                        $child['attrs']['portrait_url'] = $result['photos'][0]['src']['medium'];
                    }
                } catch (\Exception $e) {
                    // Skip
                }
            }
        }
    }

    /**
     * Parse JSON from AI response (handles markdown fences, preamble text)
     */
    private static function parseJsonResponse(?string $text): ?array
    {
        if (empty($text)) return null;

        $text = trim($text);

        // Remove markdown code fences
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)(?:\n?```|$)/', $text, $matches)) {
            $text = trim($matches[1]);
        }

        // Try direct parse
        $decoded = json_decode($text, true);
        if ($decoded) return $decoded;

        // Try extracting JSON from text
        $jsonStart = strpos($text, '{');
        if ($jsonStart !== false) {
            $jsonText = substr($text, $jsonStart);
            $decoded = json_decode($jsonText, true);
            if ($decoded) return $decoded;

            // Try truncated JSON repair
            $repaired = self::repairTruncatedJson($jsonText);
            if ($repaired) {
                $decoded = json_decode($repaired, true);
                if ($decoded) return $decoded;
            }
        }

        return null;
    }

    // ========================================
    // ADVANCED PROMPT BUILDERS
    // ========================================

    /**
     * Build advanced system prompt with Web Designer Knowledge Base
     * Enhanced 2026-02-04: Integrates JTB_AI_Knowledge for comprehensive prompts
     */
    private static function buildAdvancedSystemPrompt(array $pages, string $style, string $industry, array $colors): string
    {
        // Get module schemas from registry
        $schemas = JTB_AI_Schema::getCompactSchemasForAI();

        // Get advanced knowledge from JTB_AI_Knowledge if available
        $knowledgePrompt = '';
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Knowledge')) {
            $knowledgePrompt = JTB_AI_Knowledge::getSystemPrompt([
                'industry' => $industry,
                'style' => $style,
                'colors' => $colors
            ]);
        }

        $pagesList = implode(', ', $pages);
        $industryTemplate = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['general'];

        // Build section requirements per page
        $pageRequirements = self::buildPageRequirements($pages, $industryTemplate);

        // Build section blueprints reference for AI
        $sectionBlueprints = self::buildSectionBlueprintsReference($pages, $industryTemplate);

        return <<<SYSTEM
You are an EXPERT WEB DESIGNER with 15+ years of experience creating high-converting websites.
You generate COMPLETE, PROFESSIONAL websites in JTB (Jessie Theme Builder) JSON format.

═══════════════════════════════════════════════════════════════════
CRITICAL OUTPUT RULES (FAILURE = REJECTION)
═══════════════════════════════════════════════════════════════════
1. Output ONLY valid JSON - NO markdown, NO code blocks, NO explanations
2. Every page MUST have 6-10 sections (MINIMUM 6, MAXIMUM 10)
3. Every section MUST have proper structure: section → row → column → module
4. All module types use UNDERSCORES (site_logo NOT site-logo)
5. NEVER use placeholder URLs like example.com - leave image fields empty
6. Generate REALISTIC, PROFESSIONAL content - no Lorem Ipsum

═══════════════════════════════════════════════════════════════════
WEBSITE STRUCTURE
═══════════════════════════════════════════════════════════════════
{
  "header": {
    "sections": [{
      "id": "header_section",
      "type": "section",
      "attrs": {
        "background_color": "#ffffff",
        "padding": {"top": 20, "right": 0, "bottom": 20, "left": 0}
      },
      "children": [{
        "id": "header_row",
        "type": "row",
        "attrs": {"columns": "1_4,1_2,1_4"},
        "children": [
          {"id": "col_logo", "type": "column", "attrs": {"width": "1_4"}, "children": [
            {"id": "logo", "type": "site_logo", "attrs": {"logo": "", "logo_alt": "Company Logo"}}
          ]},
          {"id": "col_menu", "type": "column", "attrs": {"width": "1_2"}, "children": [
            {"id": "nav", "type": "menu", "attrs": {}}
          ]},
          {"id": "col_cta", "type": "column", "attrs": {"width": "1_4"}, "children": [
            {"id": "header_btn", "type": "button", "attrs": {"text": "Get Started", "link_url": "#contact", "style": "solid"}}
          ]}
        ]
      }]
    }]
  },
  "footer": {
    "sections": [/* 2-3 sections: main footer with columns, copyright bar */]
  },
  "pages": {
    "home": {"title": "Home", "sections": [/* 6-10 sections */]},
    "about": {"title": "About Us", "sections": [/* 6-10 sections */]}
  },
  "theme_settings": {
    "colors": {
      "primary": "{$colors['primary']}",
      "secondary": "{$colors['secondary']}",
      "accent": "{$colors['accent']}",
      "text": "#111827",
      "text_light": "#6b7280",
      "background": "#ffffff"
    },
    "typography": {
      "heading_font": "Inter",
      "body_font": "Inter",
      "h1_size": 56,
      "h2_size": 42,
      "h3_size": 24,
      "body_size": 16
    }
  }
}

═══════════════════════════════════════════════════════════════════
PAGE REQUIREMENTS (MANDATORY)
═══════════════════════════════════════════════════════════════════
{$pageRequirements}

═══════════════════════════════════════════════════════════════════
MODULE REFERENCE (USE EXACT ATTRIBUTE NAMES)
═══════════════════════════════════════════════════════════════════
{$schemas}

═══════════════════════════════════════════════════════════════════
COLUMN LAYOUTS (row "columns" attribute)
═══════════════════════════════════════════════════════════════════
- "1" = full width (100%)
- "1_2,1_2" = two equal columns (50% + 50%)
- "1_3,2_3" = one-third + two-thirds
- "2_3,1_3" = two-thirds + one-third
- "1_4,3_4" = quarter + three-quarters
- "1_3,1_3,1_3" = three equal columns
- "1_4,1_4,1_4,1_4" = four equal columns

═══════════════════════════════════════════════════════════════════
DESIGN PRINCIPLES
═══════════════════════════════════════════════════════════════════
1. VISUAL HIERARCHY: Hero h1=56px, Section h2=42px, Card h3=24px
2. SPACING: Hero padding 120px, Normal sections 80px, Compact 60px
3. CONTRAST: Alternate light/dark backgrounds every 2-3 sections
4. CTA PLACEMENT: Clear CTA at hero AND final section
5. SOCIAL PROOF: Include testimonials, stats, or logos on every page
6. MOBILE FIRST: All layouts must work on mobile (single column fallback)

═══════════════════════════════════════════════════════════════════
MODULES BY CONTEXT
═══════════════════════════════════════════════════════════════════
HEADER: site_logo, menu, button, search
FOOTER: heading, text, menu, social_icons, blurb
HERO: heading (h1), text, button, image
FEATURES: blurb (with icon), heading (h3)
TESTIMONIALS: testimonial (with portrait_url, author, job_title, content)
PRICING: pricing_table (with title, price, period, features array, button_text)
TEAM: team_member (with image_url, name, position, content)
STATS: number_counter (with number, title, suffix)
FAQ: accordion with accordion_item children
CONTACT: contact_form, map, blurb
CTA: heading (h2), text, button

═══════════════════════════════════════════════════════════════════
SECTION BLUEPRINTS (use these as patterns for each section type)
═══════════════════════════════════════════════════════════════════
{$sectionBlueprints}

═══════════════════════════════════════════════════════════════════
CONTENT QUALITY REQUIREMENTS
═══════════════════════════════════════════════════════════════════
- Headlines: 5-10 words, benefit-focused, power words
- Subheadlines: 10-20 words, expand on headline value
- Body text: 2-3 sentences per paragraph, scannable
- CTAs: Action verbs, specific value ("Get Your Free Quote" not "Submit")
- Testimonials: Specific results, named people with titles
- Features: Benefit-focused, not feature-focused

═══════════════════════════════════════════════════════════════════
ADVANCED DESIGN KNOWLEDGE
═══════════════════════════════════════════════════════════════════
{$knowledgePrompt}

INDUSTRY: {$industry}
STYLE: {$style}
PAGES TO GENERATE: {$pagesList}

Generate the complete website JSON now.
SYSTEM;
    }

    /**
     * Build page requirements section for prompt
     */
    private static function buildPageRequirements(array $pages, array $industryTemplate): string
    {
        $requirements = [];

        foreach ($pages as $page) {
            $sections = $industryTemplate[$page] ?? $industryTemplate['home'] ?? ['hero', 'features', 'testimonials', 'cta'];
            $sectionCount = count($sections);

            // Ensure minimum 6 sections
            if ($sectionCount < 6) {
                $additionalSections = ['about_brief', 'stats', 'faq', 'newsletter'];
                while (count($sections) < 6 && !empty($additionalSections)) {
                    $sections[] = array_shift($additionalSections);
                }
            }

            $sectionList = implode(' → ', array_slice($sections, 0, 10));
            $requirements[] = "- {$page}: MINIMUM 6 sections. Recommended flow: {$sectionList}";
        }

        return implode("\n", $requirements);
    }

    /**
     * Build section blueprints reference for AI prompt
     * Only includes blueprints for sections that are actually used in the requested pages
     */
    private static function buildSectionBlueprintsReference(array $pages, array $industryTemplate): string
    {
        // Collect all unique section types used across requested pages
        $usedSectionTypes = [];
        foreach ($pages as $page) {
            $sections = $industryTemplate[$page] ?? [];
            foreach ($sections as $section) {
                if ($section !== 'colors') {
                    $usedSectionTypes[$section] = true;
                }
            }
        }

        $lines = [];
        foreach (self::SECTION_BLUEPRINTS as $type => $blueprint) {
            // Only include blueprints that are relevant to this website
            if (!isset($usedSectionTypes[$type]) && !in_array($type, ['hero', 'cta', 'features', 'testimonials'])) {
                continue; // Skip irrelevant blueprints, but always include core types
            }

            $desc = $blueprint['description'] ?? '';
            $layout = $blueprint['layout'] ?? '1';
            $modules = implode(', ', $blueprint['modules'] ?? []);
            $padding = isset($blueprint['padding'])
                ? "padding-top: {$blueprint['padding']['top']}px, padding-bottom: {$blueprint['padding']['bottom']}px"
                : '';
            $bg = isset($blueprint['background']) ? "background: {$blueprint['background']}" : '';
            $count = isset($blueprint['count']) ? "repeat: {$blueprint['count']}" : '';

            $details = array_filter([$layout, $modules, $count, $padding, $bg]);
            $lines[] = "- {$type}: {$desc}\n  Layout: " . implode(' | ', $details);
        }

        return implode("\n", $lines);
    }

    /**
     * Build industry-specific page requirements for user prompt
     * Uses INDUSTRY_TEMPLATES to provide exact section flow per page per industry
     */
    private static function buildIndustryPageRequirements(array $pages, array $industryTemplate, string $industry): string
    {
        // Human-readable section names
        $sectionLabels = [
            'hero' => 'Hero with powerful headline and CTA',
            'hero_about' => 'Hero with mission statement',
            'hero_services' => 'Hero introducing services',
            'hero_contact' => 'Hero encouraging contact',
            'hero_courses' => 'Hero showcasing courses/programs',
            'features' => 'Features/Benefits (3-4 items with icons)',
            'trust_logos' => 'Trust indicators (client logos or badges)',
            'testimonials' => 'Testimonials (3 items with photos)',
            'pricing' => 'Pricing tiers (3 plans with features)',
            'team' => 'Team members (4 members with photos and bios)',
            'faq' => 'FAQ (5-6 questions in accordion format)',
            'cta' => 'Final call-to-action with compelling headline',
            'stats' => 'Stats/Numbers (4 impressive counters)',
            'contact_form' => 'Contact form with info sidebar',
            'services_grid' => 'Services grid (6 services with icons)',
            'story' => 'Company story with image and text',
            'values' => 'Core values (3-4 values with icons)',
            'process' => 'How it works / Process (3-5 steps)',
            'gallery' => 'Image gallery showcasing work',
            'newsletter' => 'Newsletter signup section',
            'locations' => 'Locations/Map with address',
            'about_brief' => 'Brief about section with image',
            'how_it_works' => 'How it works (3-5 steps)',
            'benefits' => 'Benefits overview (3-4 items)',
            'why_choose' => 'Why choose us (differentiators)',
            'case_studies' => 'Case studies / Success stories',
            'specializations' => 'Areas of specialization',
            'menu_preview' => 'Menu preview / Featured dishes',
            'reservations' => 'Reservation/Booking section',
            'properties' => 'Featured properties grid',
            'search_properties' => 'Property search section',
            'programs' => 'Programs / Course categories',
            'instructors' => 'Instructors / Faculty',
            'enrollment_form' => 'Enrollment/Application form',
            'mission' => 'Mission and vision statement',
            'accreditation' => 'Accreditation and certifications',
            'facilities' => 'Facilities and campus',
            'portfolio' => 'Portfolio / Work showcase',
            'clients' => 'Client logos and partnerships',
            'services' => 'Services overview',
            'sustainability' => 'Sustainability / Social responsibility',
            'shipping_info' => 'Shipping and delivery info',
            'featured_courses' => 'Featured courses',
            'course_categories' => 'Course categories',
        ];

        $output = "PAGE REQUIREMENTS (INDUSTRY: " . strtoupper($industry) . "):\n";

        foreach ($pages as $page) {
            $sections = $industryTemplate[$page] ?? ['hero', 'features', 'about_brief', 'testimonials', 'stats', 'cta'];

            // Ensure minimum 6 sections
            if (count($sections) < 6) {
                $fallback = ['stats', 'faq', 'newsletter', 'about_brief'];
                foreach ($fallback as $extra) {
                    if (!in_array($extra, $sections) && count($sections) < 6) {
                        $sections[] = $extra;
                    }
                }
            }

            $pageName = strtoupper(str_replace('_', ' ', $page));
            $sectionCount = count($sections);
            $output .= "\n{$pageName} PAGE ({$sectionCount}-" . min($sectionCount + 2, 10) . " sections):\n";

            foreach ($sections as $i => $section) {
                $label = $sectionLabels[$section] ?? ucfirst(str_replace('_', ' ', $section));
                $output .= ($i + 1) . ". {$label}\n";
            }
        }

        return $output;
    }

    /**
     * Build detailed user prompt with specific requirements
     */
    private static function buildDetailedUserPrompt(string $prompt, string $industry, string $style, array $pages, array $industryTemplate): string
    {
        $pagesList = implode(', ', $pages);
        // Colors from SINGLE SOURCE OF TRUTH
        $colors = JTB_AI_Styles::getIndustryColors($industry);

        // Build specific section counts
        $sectionCounts = [];
        foreach ($pages as $page) {
            $recommendedSections = $industryTemplate[$page] ?? ['hero', 'features', 'about', 'testimonials', 'cta', 'contact'];
            $count = max(6, min(10, count($recommendedSections)));
            $sectionCounts[] = "{$page}: {$count} sections";
        }
        $sectionCountsStr = implode(', ', $sectionCounts);

        // Build industry-specific page requirements
        $pageReqs = self::buildIndustryPageRequirements($pages, $industryTemplate, $industry);

        return <<<USER
Generate a COMPLETE, PROFESSIONAL website for: "{$prompt}"

REQUIREMENTS:
1. Industry: {$industry}
2. Style: {$style}
3. Pages: {$pagesList}
4. Section counts: {$sectionCountsStr}

HEADER REQUIREMENTS:
- Logo on left (use empty string for logo attr, will be filled later)
- Navigation menu in center
- CTA button on right ("Get Started", "Contact Us", or similar)
- Clean, professional design with 20px vertical padding

FOOTER REQUIREMENTS:
- 4-column layout with: About, Quick Links, Services, Contact
- Each column has heading + content
- Social icons row
- Copyright bar at bottom

{$pageReqs}

COLORS TO USE:
- Primary: {$colors['primary']}
- Secondary: {$colors['secondary']}
- Accent: {$colors['accent']}
- Text: #111827
- Light text: #6b7280
- Background: #ffffff
- Light background: #f9fafb

Generate the complete JSON now with ALL pages and ALL sections.
USER;
    }

    // ========================================
    // PARSING AND VALIDATION
    // ========================================

    /**
     * Parse website JSON from AI response
     */
    private static function parseWebsiteJson(string $content): ?array
    {
        // Clean up common issues
        $content = trim($content);

        // Remove markdown code blocks if present (greedy to handle truncated blocks)
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)(?:\n?```|$)/', $content, $matches)) {
            $content = trim($matches[1]);
        }

        // Try direct parse
        $decoded = json_decode($content, true);
        if ($decoded && (isset($decoded['header']) || isset($decoded['pages']))) {
            return $decoded;
        }

        // Try to extract just the JSON part (skip any preamble text)
        $jsonStart = strpos($content, '{');
        if ($jsonStart !== false) {
            $jsonContent = substr($content, $jsonStart);
            $decoded = json_decode($jsonContent, true);
            if ($decoded && (isset($decoded['header']) || isset($decoded['pages']))) {
                return $decoded;
            }

            // If JSON is truncated, try to repair by closing open brackets
            $repaired = self::repairTruncatedJson($jsonContent);
            if ($repaired) {
                $decoded = json_decode($repaired, true);
                if ($decoded && (isset($decoded['header']) || isset($decoded['pages']))) {
                    error_log('JTB_AI_Website: Parsed truncated JSON after repair');
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * Attempt to repair truncated JSON by closing open brackets/braces
     */
    private static function repairTruncatedJson(string $json): ?string
    {
        // Count open/close brackets
        $openBraces = substr_count($json, '{');
        $closeBraces = substr_count($json, '}');
        $openBrackets = substr_count($json, '[');
        $closeBrackets = substr_count($json, ']');

        // If roughly balanced, not truncated
        if ($openBraces === $closeBraces && $openBrackets === $closeBrackets) {
            return $json;
        }

        // Remove any trailing incomplete key-value pair
        // Look for last complete value (ends with }, ], number, "string", true, false, null)
        $json = preg_replace('/,\s*"[^"]*"\s*:\s*(?:"[^"]*)?$/', '', $json);
        $json = preg_replace('/,\s*"[^"]*"?\s*$/', '', $json);
        $json = preg_replace('/,\s*$/', '', $json);

        // Re-count after cleanup
        $openBraces = substr_count($json, '{');
        $closeBraces = substr_count($json, '}');
        $openBrackets = substr_count($json, '[');
        $closeBrackets = substr_count($json, ']');

        // Close remaining open structures
        $needBrackets = $openBrackets - $closeBrackets;
        $needBraces = $openBraces - $closeBraces;

        if ($needBrackets < 0 || $needBraces < 0) {
            return null; // Malformed, can't repair
        }

        // Build closing sequence (brackets first, then braces, alternating as needed)
        $closing = str_repeat(']', $needBrackets) . str_repeat('}', $needBraces);

        // More intelligent closing: track the stack of open structures
        $stack = [];
        $inString = false;
        $escaped = false;
        for ($i = 0; $i < strlen($json); $i++) {
            $ch = $json[$i];
            if ($escaped) { $escaped = false; continue; }
            if ($ch === '\\') { $escaped = true; continue; }
            if ($ch === '"') { $inString = !$inString; continue; }
            if ($inString) continue;
            if ($ch === '{' || $ch === '[') { $stack[] = $ch; }
            if ($ch === '}') { if (end($stack) === '{') array_pop($stack); }
            if ($ch === ']') { if (end($stack) === '[') array_pop($stack); }
        }

        // Close in reverse order
        $closing = '';
        while (!empty($stack)) {
            $open = array_pop($stack);
            $closing .= ($open === '{') ? '}' : ']';
        }

        return $json . $closing;
    }

    /**
     * Validate section counts meet requirements
     */
    private static function validateSectionCounts(array $website, array $expectedPages): array
    {
        $valid = true;
        $warnings = [];

        // Check pages exist
        if (!isset($website['pages']) || empty($website['pages'])) {
            return ['valid' => false, 'warnings' => ['No pages generated']];
        }

        foreach ($expectedPages as $page) {
            if (!isset($website['pages'][$page])) {
                $warnings[] = "Missing page: {$page}";
                $valid = false;
                continue;
            }

            $sections = $website['pages'][$page]['sections'] ?? [];
            $count = count($sections);

            if ($count < 4) {
                $warnings[] = "{$page} has critically low section count: {$count} (minimum 6 required)";
                $valid = false;
            } elseif ($count < 6) {
                $warnings[] = "{$page} has only {$count} sections (minimum 6 recommended)";
                $valid = false;
            }
        }

        // Check header
        if (!isset($website['header']['sections']) || empty($website['header']['sections'])) {
            $warnings[] = "No header generated";
            $valid = false;
        }

        // Check footer
        if (!isset($website['footer']['sections']) || empty($website['footer']['sections'])) {
            $warnings[] = "No footer generated";
            $valid = false;
        }

        return ['valid' => $valid, 'warnings' => $warnings];
    }

    /**
     * Count total sections in website
     */
    private static function countSections(array $website): int
    {
        $count = 0;

        if (isset($website['header']['sections'])) {
            $count += count($website['header']['sections']);
        }

        if (isset($website['footer']['sections'])) {
            $count += count($website['footer']['sections']);
        }

        if (isset($website['pages'])) {
            foreach ($website['pages'] as $page) {
                if (isset($page['sections'])) {
                    $count += count($page['sections']);
                }
            }
        }

        return $count;
    }

    // ========================================
    // POST-PROCESSING
    // ========================================

    /**
     * Post-process website: normalize slugs, add IDs, enrich with images
     */
    private static function postProcess(array $website, array $options): array
    {
        // Add unique IDs to all elements
        $website = self::addUniqueIds($website);

        // Normalize slugs (hyphens to underscores)
        if (isset($website['header']['sections'])) {
            $website['header']['sections'] = self::normalizeSlugs($website['header']['sections']);
        }
        if (isset($website['footer']['sections'])) {
            $website['footer']['sections'] = self::normalizeSlugs($website['footer']['sections']);
        }
        if (isset($website['pages'])) {
            foreach ($website['pages'] as $key => $page) {
                if (isset($page['sections'])) {
                    $website['pages'][$key]['sections'] = self::normalizeSlugs($page['sections']);
                }
            }
        }

        // Enrich with Pexels images if available
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
            $website = self::enrichWithImages($website, $options);
        }

        return $website;
    }

    /**
     * Add unique IDs to all elements recursively
     */
    private static function addUniqueIds(array $website, string $prefix = 'jtb'): array
    {
        $counter = 0;

        $addId = function(&$element) use (&$addId, &$counter, $prefix) {
            if (is_array($element)) {
                if (isset($element['type']) && empty($element['id'])) {
                    $element['id'] = $prefix . '_' . $element['type'] . '_' . (++$counter);
                }
                if (isset($element['children']) && is_array($element['children'])) {
                    foreach ($element['children'] as &$child) {
                        $addId($child);
                    }
                }
                if (isset($element['sections']) && is_array($element['sections'])) {
                    foreach ($element['sections'] as &$section) {
                        $addId($section);
                    }
                }
            }
        };

        // Process header
        if (isset($website['header'])) {
            $addId($website['header']);
        }

        // Process footer
        if (isset($website['footer'])) {
            $addId($website['footer']);
        }

        // Process pages
        if (isset($website['pages'])) {
            foreach ($website['pages'] as &$page) {
                $addId($page);
            }
        }

        return $website;
    }

    /**
     * Normalize module slugs (hyphens to underscores)
     */
    private static function normalizeSlugs(array $items): array
    {
        foreach ($items as &$item) {
            if (isset($item['type'])) {
                $item['type'] = str_replace('-', '_', $item['type']);
            }
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::normalizeSlugs($item['children']);
            }
        }
        return $items;
    }

    /**
     * Detect industry from prompt - delegates to JTB_AI_Generator (single source of truth)
     */
    private static function detectIndustry(string $prompt): string
    {
        $result = JTB_AI_Generator::detectIndustry($prompt);
        return !empty($result) ? $result : 'general';
    }

    /**
     * Enrich website with Pexels images
     */
    private static function enrichWithImages(array $website, array $options): array
    {
        $industry = $options['industry'] ?? 'business';

        // Enrich pages
        if (isset($website['pages'])) {
            foreach ($website['pages'] as $pageKey => &$page) {
                if (isset($page['sections'])) {
                    $page['sections'] = self::enrichSectionsWithImages($page['sections'], $industry, $pageKey);
                }
            }
        }

        return $website;
    }

    /**
     * Enrich sections with images recursively
     */
    private static function enrichSectionsWithImages(array $sections, string $industry, string $pageType): array
    {
        foreach ($sections as &$section) {
            $section = self::enrichNodeWithImages($section, $industry, $pageType);
        }
        return $sections;
    }

    /**
     * Recursively enrich nodes with images from Pexels
     */
    private static function enrichNodeWithImages(array $node, string $industry, string $context): array
    {
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        // Add images to image modules without src or with placeholder
        if ($type === 'image') {
            $src = $attrs['src'] ?? '';
            if (empty($src) || strpos($src, 'example.com') !== false || strpos($src, 'placeholder') !== false) {
                $image = JTB_AI_Pexels::getFeatureImage(['industry' => $industry, 'context' => $context]);
                if ($image && !empty($image['url'])) {
                    $node['attrs']['src'] = $image['url_large'] ?? $image['url'];
                    $node['attrs']['alt'] = $image['alt'] ?? ucfirst($context) . ' image';
                }
            }
        }

        // Add portraits to testimonials
        if ($type === 'testimonial') {
            $portrait = $attrs['portrait_url'] ?? '';
            if (empty($portrait) || strpos($portrait, 'example.com') !== false) {
                $photo = JTB_AI_Pexels::getPersonPhoto(['context' => 'testimonial']);
                if ($photo && !empty($photo['url'])) {
                    $node['attrs']['portrait_url'] = $photo['url'];
                }
            }
        }

        // Add photos to team members
        if ($type === 'team_member') {
            $image = $attrs['image'] ?? '';
            if (empty($image) || strpos($image, 'example.com') !== false) {
                $photo = JTB_AI_Pexels::getPersonPhoto(['context' => 'professional']);
                if ($photo && !empty($photo['url'])) {
                    $node['attrs']['image'] = $photo['url'];
                }
            }
        }

        // Add hero background images
        if ($type === 'section') {
            $bgImage = $attrs['background_image'] ?? '';
            if (!empty($bgImage) && (strpos($bgImage, 'example.com') !== false || strpos($bgImage, 'placeholder') !== false)) {
                $heroImage = JTB_AI_Pexels::getHeroImage(['industry' => $industry]);
                if ($heroImage && !empty($heroImage['url'])) {
                    $node['attrs']['background_image'] = $heroImage['url'];
                }
            }
        }

        // Add gallery images
        if ($type === 'gallery') {
            $images = $attrs['images'] ?? [];
            if (empty($images)) {
                $galleryResult = JTB_AI_Pexels::getGalleryImages(['industry' => $industry], 6);
                if ($galleryResult && !empty($galleryResult['images'])) {
                    $node['attrs']['images'] = $galleryResult['images'];
                }
            }
        }

        // Process children recursively
        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as &$child) {
                $child = self::enrichNodeWithImages($child, $industry, $context);
            }
        }

        return $node;
    }

    // ========================================
    // DATABASE OPERATIONS
    // ========================================

    /**
     * Save generated website to database
     *
     * @param array $website Generated website structure
     * @param array $options Save options
     * @return array Result with IDs of created templates/pages
     */
    public static function saveToDatabase(array $website, array $options = []): array
    {
        $results = [
            'header_id' => null,
            'footer_id' => null,
            'page_ids' => []
        ];

        try {
            // Save header template
            if (isset($website['header']['sections'])) {
                $headerContent = [
                    'version' => '1.0',
                    'content' => $website['header']['sections']
                ];

                $headerData = [
                    'name' => ($options['site_name'] ?? 'Website') . ' - Header',
                    'type' => 'header',
                    'content' => $headerContent,
                    'is_active' => true,
                    'priority' => 10
                ];

                $results['header_id'] = JTB_Templates::create($headerData);

                if ($results['header_id']) {
                    JTB_Templates::setDefault($results['header_id']);
                }
            }

            // Save footer template
            if (isset($website['footer']['sections'])) {
                $footerContent = [
                    'version' => '1.0',
                    'content' => $website['footer']['sections']
                ];

                $footerData = [
                    'name' => ($options['site_name'] ?? 'Website') . ' - Footer',
                    'type' => 'footer',
                    'content' => $footerContent,
                    'is_active' => true,
                    'priority' => 10
                ];

                $results['footer_id'] = JTB_Templates::create($footerData);

                if ($results['footer_id']) {
                    JTB_Templates::setDefault($results['footer_id']);
                }
            }

            // Save pages
            if (isset($website['pages'])) {
                $db = \core\Database::connection();

                foreach ($website['pages'] as $slug => $pageData) {
                    $title = $pageData['title'] ?? ucfirst($slug);

                    // Check if page exists
                    $existingPage = $db->query(
                        "SELECT id FROM pages WHERE slug = ?",
                        [$slug]
                    )->fetch(\PDO::FETCH_ASSOC);

                    if ($existingPage) {
                        $pageId = $existingPage['id'];
                        $jtbContent = [
                            'version' => '1.0',
                            'content' => $pageData['sections']
                        ];
                        JTB_Builder::save($pageId, $jtbContent);
                    } else {
                        $db->query(
                            "INSERT INTO pages (title, slug, status, created_at) VALUES (?, ?, 'published', NOW())",
                            [$title, $slug]
                        );

                        $pageId = $db->lastInsertId();

                        $jtbContent = [
                            'version' => '1.0',
                            'content' => $pageData['sections']
                        ];
                        JTB_Builder::save($pageId, $jtbContent);
                    }

                    $results['page_ids'][$slug] = $pageId;
                }
            }

            // Apply theme settings
            if (isset($website['theme_settings'])) {
                self::applyThemeSettings($website['theme_settings']);
            }

            return [
                'ok' => true,
                'results' => $results
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::saveToDatabase error: ' . $e->getMessage());
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'results' => $results
            ];
        }
    }

    /**
     * Apply theme settings from generated website
     */
    private static function applyThemeSettings(array $themeSettings): void
    {
        if (isset($themeSettings['colors'])) {
            foreach ($themeSettings['colors'] as $key => $value) {
                JTB_Theme_Settings::set($key . '_color', $value);
            }
        }

        if (isset($themeSettings['typography'])) {
            foreach ($themeSettings['typography'] as $key => $value) {
                JTB_Theme_Settings::set($key, $value);
            }
        }

        // Regenerate CSS
        JTB_Theme_Settings::regenerateCss();
    }

    // ========================================
    // SEO DATA GENERATION
    // ========================================

    /**
     * Generate SEO metadata for each page
     *
     * @param array $website Generated website structure
     * @param string $prompt Original user prompt
     * @param string $industry Detected industry
     * @param array $options Generation options
     * @return array SEO data for all pages
     */
    private static function generateSeoData(array $website, string $prompt, string $industry, array $options): array
    {
        $siteName = $options['site_name'] ?? self::extractSiteName($prompt);
        $siteUrl = $options['site_url'] ?? '';

        $seoData = [
            'global' => [
                'site_name' => $siteName,
                'site_url' => $siteUrl,
                'default_image' => '', // Will be filled with first hero image
                'twitter_handle' => $options['twitter_handle'] ?? '',
                'locale' => $options['locale'] ?? 'en_US',
            ],
            'pages' => []
        ];

        // Industry-specific SEO keywords
        $industryKeywords = self::getIndustryKeywords($industry);

        // Generate SEO for each page
        if (isset($website['pages'])) {
            foreach ($website['pages'] as $slug => $pageData) {
                $pageTitle = $pageData['title'] ?? ucfirst($slug);
                $sections = $pageData['sections'] ?? [];

                // Extract content from sections for description
                $description = self::extractPageDescription($sections, $pageTitle, $industry);
                $keywords = self::extractPageKeywords($sections, $industryKeywords);
                $heroImage = self::findHeroImage($sections);

                // Store first hero image as default
                if (empty($seoData['global']['default_image']) && !empty($heroImage)) {
                    $seoData['global']['default_image'] = $heroImage;
                }

                $seoData['pages'][$slug] = [
                    'title' => self::generateSeoTitle($pageTitle, $siteName, $slug),
                    'description' => $description,
                    'keywords' => $keywords,
                    'og' => [
                        'title' => self::generateOgTitle($pageTitle, $siteName),
                        'description' => $description,
                        'type' => $slug === 'home' ? 'website' : 'article',
                        'image' => $heroImage ?: $seoData['global']['default_image'],
                        'url' => $siteUrl . ($slug === 'home' ? '' : '/' . $slug),
                    ],
                    'twitter' => [
                        'card' => 'summary_large_image',
                        'title' => self::generateOgTitle($pageTitle, $siteName),
                        'description' => $description,
                        'image' => $heroImage ?: $seoData['global']['default_image'],
                    ],
                    'schema' => self::generatePageSchema($slug, $pageTitle, $description, $siteName, $siteUrl, $sections, $industry),
                    'canonical' => $siteUrl . ($slug === 'home' ? '' : '/' . $slug),
                ];
            }
        }

        return $seoData;
    }

    /**
     * Extract site name from prompt
     */
    private static function extractSiteName(string $prompt): string
    {
        // Try to extract company/business name from prompt
        // Common patterns: "for [Company Name]", "[Company Name] website"
        $patterns = [
            '/(?:for|create|build)\s+([A-Z][A-Za-z\s&]+?)(?:\s+website|\s+landing|\s+page|$)/i',
            '/^([A-Z][A-Za-z\s&]+?)\s+(?:website|landing|page)/i',
            '/(?:called|named)\s+([A-Z][A-Za-z\s&]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $prompt, $matches)) {
                return trim($matches[1]);
            }
        }

        return 'Our Company';
    }

    /**
     * Get industry-specific SEO keywords
     */
    private static function getIndustryKeywords(string $industry): array
    {
        $keywords = [
            'technology' => ['software', 'technology', 'digital solutions', 'innovation', 'tech company', 'SaaS'],
            'healthcare' => ['healthcare', 'medical', 'health services', 'patient care', 'medical professionals'],
            'legal' => ['law firm', 'legal services', 'attorneys', 'lawyers', 'legal counsel', 'litigation'],
            'restaurant' => ['restaurant', 'dining', 'cuisine', 'food', 'reservations', 'menu'],
            'real_estate' => ['real estate', 'properties', 'homes', 'buying', 'selling', 'realtor'],
            'fitness' => ['fitness', 'gym', 'workout', 'personal training', 'health', 'exercise'],
            'agency' => ['agency', 'marketing', 'creative services', 'branding', 'design', 'digital marketing'],
            'ecommerce' => ['online store', 'shopping', 'products', 'ecommerce', 'buy online'],
            'education' => ['education', 'learning', 'courses', 'training', 'school', 'academy'],
            'general' => ['professional services', 'business', 'solutions', 'expertise'],
        ];

        return $keywords[$industry] ?? $keywords['general'];
    }

    /**
     * Extract page description from sections content
     */
    private static function extractPageDescription(array $sections, string $pageTitle, string $industry): string
    {
        $descriptions = [];

        foreach ($sections as $section) {
            // Look for text content in first few sections
            if (isset($section['children'])) {
                $text = self::extractTextFromNode($section);
                if (!empty($text)) {
                    $descriptions[] = $text;
                }
            }

            // Stop after collecting enough content
            if (count($descriptions) >= 2) {
                break;
            }
        }

        if (!empty($descriptions)) {
            $description = implode(' ', $descriptions);
            // Truncate to 155 characters (optimal for meta description)
            if (strlen($description) > 155) {
                $description = substr($description, 0, 152) . '...';
            }
            return $description;
        }

        // Fallback description based on page type and industry
        $fallbacks = [
            'home' => "Welcome to our {$industry} services. Discover how we can help you achieve your goals.",
            'about' => "Learn about our company, our mission, values, and the team behind our success.",
            'services' => "Explore our comprehensive range of {$industry} services designed to meet your needs.",
            'contact' => "Get in touch with us. We're here to help and answer any questions you may have.",
        ];

        return $fallbacks[strtolower($pageTitle)] ?? "Explore our {$pageTitle} page for more information about our services.";
    }

    /**
     * Extract text content from node recursively
     */
    private static function extractTextFromNode(array $node): string
    {
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        // Get text from text/heading modules
        if ($type === 'text' && isset($attrs['content'])) {
            return strip_tags($attrs['content']);
        }
        if ($type === 'heading' && isset($attrs['text'])) {
            return strip_tags($attrs['text']);
        }

        // Recurse into children
        $texts = [];
        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $text = self::extractTextFromNode($child);
                if (!empty($text)) {
                    $texts[] = $text;
                }
            }
        }

        return implode(' ', $texts);
    }

    /**
     * Extract keywords from page content
     */
    private static function extractPageKeywords(array $sections, array $industryKeywords): array
    {
        $keywords = $industryKeywords;

        // Extract keywords from headings
        foreach ($sections as $section) {
            $headings = self::extractHeadingsFromNode($section);
            foreach ($headings as $heading) {
                // Extract significant words (longer than 4 chars)
                $words = preg_split('/\s+/', strtolower($heading));
                foreach ($words as $word) {
                    $word = preg_replace('/[^a-z]/', '', $word);
                    if (strlen($word) > 4 && !in_array($word, $keywords)) {
                        $keywords[] = $word;
                    }
                }
            }

            // Limit to 10 keywords
            if (count($keywords) >= 10) {
                break;
            }
        }

        return array_slice($keywords, 0, 10);
    }

    /**
     * Extract headings from node recursively
     */
    private static function extractHeadingsFromNode(array $node): array
    {
        $headings = [];
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        if ($type === 'heading' && isset($attrs['text'])) {
            $headings[] = strip_tags($attrs['text']);
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $headings = array_merge($headings, self::extractHeadingsFromNode($child));
            }
        }

        return $headings;
    }

    /**
     * Find hero image from sections
     */
    private static function findHeroImage(array $sections): string
    {
        foreach ($sections as $section) {
            // Check section background image
            if (!empty($section['attrs']['background_image'])) {
                return $section['attrs']['background_image'];
            }

            // Look for image modules in first section (hero)
            $image = self::findImageInNode($section);
            if (!empty($image)) {
                return $image;
            }
        }

        return '';
    }

    /**
     * Find first image in node recursively
     */
    private static function findImageInNode(array $node): string
    {
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        if ($type === 'image' && !empty($attrs['src'])) {
            return $attrs['src'];
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $image = self::findImageInNode($child);
                if (!empty($image)) {
                    return $image;
                }
            }
        }

        return '';
    }

    /**
     * Generate SEO title for page
     */
    private static function generateSeoTitle(string $pageTitle, string $siteName, string $slug): string
    {
        if ($slug === 'home') {
            return "{$siteName} | {$pageTitle}";
        }
        return "{$pageTitle} | {$siteName}";
    }

    /**
     * Generate Open Graph title
     */
    private static function generateOgTitle(string $pageTitle, string $siteName): string
    {
        return "{$pageTitle} - {$siteName}";
    }

    /**
     * Generate Schema.org structured data for page
     */
    private static function generatePageSchema(string $slug, string $pageTitle, string $description, string $siteName, string $siteUrl, array $sections, string $industry): array
    {
        $schemas = [];

        // Website schema (for home page)
        if ($slug === 'home') {
            $schemas[] = [
                '@type' => 'WebSite',
                'name' => $siteName,
                'description' => $description,
                'url' => $siteUrl,
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => $siteUrl . '/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ];

            // Organization schema
            $schemas[] = [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $siteUrl,
                'description' => $description,
            ];
        }

        // About page - Organization details
        if ($slug === 'about') {
            $schemas[] = [
                '@type' => 'AboutPage',
                'name' => $pageTitle,
                'description' => $description,
                'url' => $siteUrl . '/about',
                'mainEntity' => [
                    '@type' => 'Organization',
                    'name' => $siteName,
                ]
            ];
        }

        // Contact page - ContactPage schema
        if ($slug === 'contact') {
            $schemas[] = [
                '@type' => 'ContactPage',
                'name' => $pageTitle,
                'description' => $description,
                'url' => $siteUrl . '/contact',
            ];
        }

        // Services page - Service schema
        if ($slug === 'services') {
            $services = self::extractServicesFromSections($sections);
            if (!empty($services)) {
                $schemas[] = [
                    '@type' => 'Service',
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => $siteName,
                    ],
                    'serviceType' => $industry,
                    'description' => $description,
                ];
            }
        }

        // FAQ schema if FAQ section exists
        $faqItems = self::extractFaqFromSections($sections);
        if (!empty($faqItems)) {
            $schemas[] = [
                '@type' => 'FAQPage',
                'mainEntity' => array_map(function($faq) {
                    return [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer']
                        ]
                    ];
                }, $faqItems)
            ];
        }

        // BreadcrumbList schema
        $schemas[] = [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => $siteUrl
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $pageTitle,
                    'item' => $siteUrl . ($slug === 'home' ? '' : '/' . $slug)
                ]
            ]
        ];

        return $schemas;
    }

    /**
     * Extract services from sections for schema
     */
    private static function extractServicesFromSections(array $sections): array
    {
        $services = [];

        foreach ($sections as $section) {
            $extracted = self::extractServicesFromNode($section);
            $services = array_merge($services, $extracted);
        }

        return array_slice($services, 0, 10);
    }

    /**
     * Extract services from node recursively
     */
    private static function extractServicesFromNode(array $node): array
    {
        $services = [];
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        // Blurbs often represent services
        if ($type === 'blurb' && isset($attrs['title'])) {
            $services[] = [
                'name' => $attrs['title'],
                'description' => $attrs['content'] ?? ''
            ];
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $services = array_merge($services, self::extractServicesFromNode($child));
            }
        }

        return $services;
    }

    /**
     * Extract FAQ items from sections for schema
     */
    private static function extractFaqFromSections(array $sections): array
    {
        $faqItems = [];

        foreach ($sections as $section) {
            $extracted = self::extractFaqFromNode($section);
            $faqItems = array_merge($faqItems, $extracted);
        }

        return $faqItems;
    }

    /**
     * Extract FAQ from node recursively
     */
    private static function extractFaqFromNode(array $node): array
    {
        $faqItems = [];
        $type = $node['type'] ?? '';
        $attrs = $node['attrs'] ?? [];

        // Accordion items are FAQ
        if ($type === 'accordion_item') {
            if (isset($attrs['title']) && isset($attrs['content'])) {
                $faqItems[] = [
                    'question' => strip_tags($attrs['title']),
                    'answer' => strip_tags($attrs['content'])
                ];
            }
        }

        // Toggle items can also be FAQ
        if ($type === 'toggle') {
            if (isset($attrs['title']) && isset($attrs['content'])) {
                $faqItems[] = [
                    'question' => strip_tags($attrs['title']),
                    'answer' => strip_tags($attrs['content'])
                ];
            }
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $faqItems = array_merge($faqItems, self::extractFaqFromNode($child));
            }
        }

        return $faqItems;
    }

    // ========================================
    // FEATURE 8.1: BRAND KIT EXTRACTION
    // ========================================

    /**
     * Extract brand kit from URL (colors, fonts, logo, style)
     * User provides a URL or logo image → AI analyzes colors, fonts, style
     *
     * @param string $url URL of existing website or logo image
     * @return array Brand kit data
     * @since 2026-02-05
     */
    public static function extractBrandKit(string $url): array
    {
        try {
            $startTime = microtime(true);

            // Determine if URL is an image
            $isImage = preg_match('/\.(png|jpg|jpeg|gif|svg|webp)(\?.*)?$/i', $url);

            if ($isImage) {
                return self::extractBrandKitFromLogo($url);
            }

            // Fetch the webpage
            $html = self::fetchUrl($url);
            if (!$html) {
                return ['ok' => false, 'error' => 'Failed to fetch URL: ' . $url];
            }

            // Extract colors from CSS
            $colors = self::extractColorsFromHtml($html);

            // Extract fonts from CSS
            $fonts = self::extractFontsFromHtml($html);

            // Extract logo
            $logoUrl = self::extractLogoFromHtml($html, $url);

            // Use AI to determine style
            $ai = JTB_AI_Core::getInstance();
            $styleAnalysis = '';

            if ($ai->isConfigured()) {
                $colorsList = implode(', ', array_slice($colors, 0, 10));
                $fontsList = implode(', ', array_slice($fonts, 0, 5));
                $analysisPrompt = "Analyze this website's design style based on these extracted elements:\n"
                    . "Colors: {$colorsList}\n"
                    . "Fonts: {$fontsList}\n"
                    . "URL: {$url}\n\n"
                    . "Respond with ONLY a JSON object: {\"style\": \"modern|minimal|bold|elegant|playful|corporate|dark\", \"description\": \"brief description\", \"primary_color\": \"#hex\", \"secondary_color\": \"#hex\", \"accent_color\": \"#hex\", \"heading_font\": \"font name\", \"body_font\": \"font name\"}";

                $aiResponse = $ai->query($analysisPrompt, [
                    'system_prompt' => 'You are a web design analyzer. Return only valid JSON.',
                    'temperature' => 0.3,
                    'max_tokens' => 500
                ]);

                if ($aiResponse['ok'] && !empty($aiResponse['text'])) {
                    $parsed = json_decode($aiResponse['text'], true);
                    if ($parsed) {
                        $styleAnalysis = $parsed;
                    }
                }
            }

            // Build organized brand kit
            $brandKit = [
                'colors' => [
                    'primary' => $styleAnalysis['primary_color'] ?? ($colors[0] ?? '#3b82f6'),
                    'secondary' => $styleAnalysis['secondary_color'] ?? ($colors[1] ?? '#1e40af'),
                    'accent' => $styleAnalysis['accent_color'] ?? ($colors[2] ?? '#10b981'),
                    'all_extracted' => array_unique(array_slice($colors, 0, 20))
                ],
                'fonts' => [
                    'heading' => $styleAnalysis['heading_font'] ?? ($fonts[0] ?? 'Inter'),
                    'body' => $styleAnalysis['body_font'] ?? ($fonts[1] ?? $fonts[0] ?? 'Inter'),
                    'all_extracted' => array_unique($fonts)
                ],
                'logo_url' => $logoUrl,
                'style' => $styleAnalysis['style'] ?? 'modern',
                'description' => $styleAnalysis['description'] ?? 'Extracted brand identity',
                'source_url' => $url
            ];

            $timeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'brand_kit' => $brandKit,
                'stats' => ['time_ms' => $timeMs]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::extractBrandKit error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract brand kit from a logo image URL
     */
    private static function extractBrandKitFromLogo(string $imageUrl): array
    {
        $startTime = microtime(true);
        $ai = JTB_AI_Core::getInstance();

        if (!$ai->isConfigured()) {
            return ['ok' => false, 'error' => 'AI not configured for logo analysis'];
        }

        $prompt = "I have a logo image at this URL: {$imageUrl}\n\n"
            . "Based on the typical brand elements associated with this type of logo, suggest a professional brand kit.\n"
            . "Return ONLY a JSON object:\n"
            . "{\"primary_color\": \"#hex\", \"secondary_color\": \"#hex\", \"accent_color\": \"#hex\", "
            . "\"heading_font\": \"font\", \"body_font\": \"font\", \"style\": \"modern|minimal|bold|elegant|playful|corporate|dark\", "
            . "\"description\": \"brief brand description\"}";

        $response = $ai->query($prompt, [
            'system_prompt' => 'You are a brand identity expert. Analyze logos and suggest matching brand kits. Return only JSON.',
            'temperature' => 0.5,
            'max_tokens' => 500
        ]);

        if (!$response['ok']) {
            return ['ok' => false, 'error' => 'AI analysis failed'];
        }

        $parsed = json_decode($response['text'] ?? '', true);
        if (!$parsed) {
            // Try extracting JSON from response
            if (preg_match('/\{[^{}]*\}/', $response['text'], $m)) {
                $parsed = json_decode($m[0], true);
            }
        }

        if (!$parsed) {
            return ['ok' => false, 'error' => 'Failed to parse AI response'];
        }

        $timeMs = round((microtime(true) - $startTime) * 1000);

        return [
            'ok' => true,
            'brand_kit' => [
                'colors' => [
                    'primary' => $parsed['primary_color'] ?? '#3b82f6',
                    'secondary' => $parsed['secondary_color'] ?? '#1e40af',
                    'accent' => $parsed['accent_color'] ?? '#10b981',
                    'all_extracted' => array_values(array_unique(array_filter([
                        $parsed['primary_color'] ?? '#3b82f6',
                        $parsed['secondary_color'] ?? '#1e40af',
                        $parsed['accent_color'] ?? '#10b981'
                    ])))
                ],
                'fonts' => [
                    'heading' => $parsed['heading_font'] ?? 'Inter',
                    'body' => $parsed['body_font'] ?? 'Inter',
                    'all_extracted' => array_values(array_unique(array_filter([
                        $parsed['heading_font'] ?? 'Inter',
                        $parsed['body_font'] ?? 'Inter'
                    ])))
                ],
                'logo_url' => $imageUrl,
                'style' => $parsed['style'] ?? 'modern',
                'description' => $parsed['description'] ?? 'Brand identity from logo',
                'source_url' => $imageUrl
            ],
            'stats' => [
                'time_ms' => $timeMs,
                'source' => 'logo_analysis'
            ]
        ];
    }

    /**
     * Fetch URL content with timeout and user agent
     */
    private static function fetchUrl(string $url): ?string
    {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 15,
                'header' => "User-Agent: Mozilla/5.0 (compatible; JTB Bot/1.0)\r\n"
                    . "Accept: text/html,application/xhtml+xml\r\n",
                'follow_location' => true,
                'max_redirects' => 3
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $html = @file_get_contents($url, false, $ctx);
        if ($html === false) {
            // Try with curl as fallback
            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_TIMEOUT => 15,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; JTB Bot/1.0)'
                ]);
                $html = curl_exec($ch);
                curl_close($ch);
            }
        }

        return $html ?: null;
    }

    /**
     * Extract colors from HTML/CSS
     */
    private static function extractColorsFromHtml(string $html): array
    {
        $colors = [];

        // Match hex colors
        preg_match_all('/#([0-9a-fA-F]{3,8})\b/', $html, $hexMatches);
        foreach ($hexMatches[0] as $hex) {
            $hex = strtolower($hex);
            // Normalize 3-char to 6-char
            if (strlen($hex) === 4) {
                $hex = '#' . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
            }
            // Skip near-white, near-black, and common utility values
            if (!in_array($hex, ['#ffffff', '#fff', '#000000', '#000', '#333333', '#333', '#666666', '#666', '#999999', '#999', '#cccccc', '#ccc', '#f5f5f5', '#e5e5e5', '#d4d4d4'])) {
                $colors[] = $hex;
            }
        }

        // Match rgb/rgba colors
        preg_match_all('/rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $html, $rgbMatches, PREG_SET_ORDER);
        foreach ($rgbMatches as $match) {
            $hex = sprintf('#%02x%02x%02x', (int)$match[1], (int)$match[2], (int)$match[3]);
            if (!in_array($hex, ['#ffffff', '#000000', '#333333', '#666666', '#999999', '#cccccc'])) {
                $colors[] = $hex;
            }
        }

        // Count occurrences and sort by frequency
        $colorCounts = array_count_values($colors);
        arsort($colorCounts);

        return array_keys($colorCounts);
    }

    /**
     * Extract fonts from HTML/CSS
     */
    private static function extractFontsFromHtml(string $html): array
    {
        $fonts = [];

        // Match font-family declarations
        preg_match_all('/font-family\s*:\s*["\']?([^;"\'}\r\n]+)/i', $html, $fontMatches);
        foreach ($fontMatches[1] as $fontDecl) {
            // Split by comma and take first font
            $parts = explode(',', $fontDecl);
            $font = trim($parts[0], " \t\n\r\0\x0B\"'");
            // Skip generic fonts
            if (!in_array(strtolower($font), ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui', 'inherit', 'initial', '-apple-system', 'blinkmacsystemfont', 'segoe ui'])) {
                $fonts[] = $font;
            }
        }

        // Match Google Fonts links
        preg_match_all('/fonts\.googleapis\.com\/css2?\?family=([^"&]+)/', $html, $googleMatches);
        foreach ($googleMatches[1] as $fontParam) {
            $fontName = urldecode(explode(':', $fontParam)[0]);
            $fontName = str_replace('+', ' ', $fontName);
            if (!empty($fontName)) {
                array_unshift($fonts, $fontName); // Google Fonts have priority
            }
        }

        return array_values(array_unique($fonts));
    }

    /**
     * Extract logo from HTML
     */
    private static function extractLogoFromHtml(string $html, string $baseUrl): string
    {
        // Look for common logo patterns
        $patterns = [
            '/<(?:img|source)[^>]*class\s*=\s*["\'][^"\']*logo[^"\']*["\'][^>]*src\s*=\s*["\']([^"\']+)/i',
            '/<(?:img|source)[^>]*id\s*=\s*["\'][^"\']*logo[^"\']*["\'][^>]*src\s*=\s*["\']([^"\']+)/i',
            '/<a[^>]*class\s*=\s*["\'][^"\']*logo[^"\']*["\'][^>]*>.*?<img[^>]*src\s*=\s*["\']([^"\']+)/is',
            '/<header[^>]*>.*?<img[^>]*src\s*=\s*["\']([^"\']+)/is',
            '/class\s*=\s*["\'][^"\']*brand[^"\']*["\'][^>]*>.*?<img[^>]*src\s*=\s*["\']([^"\']+)/is',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $m)) {
                $logoSrc = $m[1];
                // Make absolute URL
                if (strpos($logoSrc, 'http') !== 0) {
                    $parsedBase = parse_url($baseUrl);
                    $base = $parsedBase['scheme'] . '://' . $parsedBase['host'];
                    if (strpos($logoSrc, '/') === 0) {
                        $logoSrc = $base . $logoSrc;
                    } else {
                        $logoSrc = $base . '/' . $logoSrc;
                    }
                }
                return $logoSrc;
            }
        }

        return '';
    }

    // ========================================
    // FEATURE 8.2: COMPETITOR ANALYSIS
    // ========================================

    /**
     * Analyze competitor website and generate a similar but unique site
     * "Generate website like [competitor URL] but for my business"
     *
     * @param string $url Competitor website URL
     * @param string $prompt User's business description
     * @param array $options Additional options (industry, style, pages)
     * @return array Generated website (same format as generate())
     * @since 2026-02-05
     */
    public static function analyzeCompetitor(string $url, string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            // Step 1: Fetch and analyze competitor
            $html = self::fetchUrl($url);
            if (!$html) {
                return ['ok' => false, 'error' => 'Failed to fetch competitor URL: ' . $url];
            }

            // Extract competitor data
            $competitorData = self::analyzeCompetitorStructure($html);
            $competitorBrand = self::extractColorsFromHtml($html);
            $competitorFonts = self::extractFontsFromHtml($html);

            // Step 2: Use AI to generate a unique website inspired by competitor
            $ai = JTB_AI_Core::getInstance();
            if (!$ai->isConfigured()) {
                return ['ok' => false, 'error' => 'AI is not configured'];
            }

            $industry = $options['industry'] ?? self::detectIndustry($prompt);
            $style = $options['style'] ?? 'modern';
            $pages = $options['pages'] ?? ['home', 'about', 'services', 'contact'];

            $competitorColors = implode(', ', array_slice($competitorBrand, 0, 5));
            $competitorFontsList = implode(', ', array_slice($competitorFonts, 0, 3));

            // Build enhanced prompt with competitor context
            $competitorContext = "COMPETITOR ANALYSIS (use as INSPIRATION only, create UNIQUE design):\n"
                . "- Competitor URL: {$url}\n"
                . "- Section types found: " . implode(', ', $competitorData['section_types']) . "\n"
                . "- Number of sections: " . $competitorData['section_count'] . "\n"
                . "- Color scheme: {$competitorColors}\n"
                . "- Fonts used: {$competitorFontsList}\n"
                . "- Text excerpts: " . implode(' | ', array_slice($competitorData['headings'], 0, 5)) . "\n"
                . "- Layout patterns: " . implode(', ', $competitorData['layout_patterns']) . "\n\n"
                . "IMPORTANT: Create a UNIQUE website that is INSPIRED by (but NOT copying) the competitor.\n"
                . "Use DIFFERENT colors, DIFFERENT copy, and YOUR OWN structure.\n"
                . "The competitor's structure is just a reference for what works in this industry.\n";

            // Modify existing generation with competitor context
            $industryTemplate = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['general'];
            // Colors from SINGLE SOURCE OF TRUTH
            $colors = JTB_AI_Styles::getIndustryColors($industry);

            $systemPrompt = self::buildAdvancedSystemPrompt($pages, $style, $industry, $colors);
            $systemPrompt .= "\n\n" . $competitorContext;

            $userPrompt = self::buildDetailedUserPrompt($prompt, $industry, $style, $pages, $industryTemplate);
            $userPrompt .= "\n\nRemember: Create something BETTER than the competitor at {$url}, not a copy.";

            $response = $ai->query($userPrompt, [
                'system_prompt' => $systemPrompt,
                'temperature' => 0.7,
                'max_tokens' => 8000  // DeepSeek limit is 8192
            ]);

            if (!$response['ok']) {
                throw new \Exception($response['error'] ?? 'AI query failed');
            }

            $website = self::parseWebsiteJson($response['text'] ?? '');
            if (!$website) {
                throw new \Exception('Failed to parse AI response');
            }

            $validation = self::validateSectionCounts($website, $pages);
            $website = self::postProcess($website, array_merge($options, ['industry' => $industry]));
            $seoData = self::generateSeoData($website, $prompt, $industry, $options);
            $website['seo'] = $seoData;

            $timeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'website' => $website,
                'competitor_analysis' => $competitorData,
                'stats' => [
                    'time_ms' => $timeMs,
                    'provider' => $response['provider'] ?? 'unknown',
                    'sections_generated' => self::countSections($website),
                    'competitor_url' => $url,
                    'validation' => $validation
                ]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::analyzeCompetitor error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Analyze competitor website structure
     */
    private static function analyzeCompetitorStructure(string $html): array
    {
        $data = [
            'section_types' => [],
            'section_count' => 0,
            'headings' => [],
            'layout_patterns' => [],
            'has_hero' => false,
            'has_testimonials' => false,
            'has_pricing' => false,
            'has_faq' => false,
            'has_contact' => false
        ];

        // Count sections (common section-like elements)
        preg_match_all('/<(?:section|div)[^>]*class\s*=\s*["\'][^"\']*(?:section|block|area|wrapper|container|hero|features|testimonial|pricing|faq|contact|cta|about|team|footer)[^"\']*["\']/', $html, $sectionMatches);
        $data['section_count'] = max(count($sectionMatches[0]), 1);

        // Detect section types from classes
        $sectionKeywords = [
            'hero' => ['hero', 'banner', 'jumbotron', 'masthead', 'splash'],
            'features' => ['features', 'benefits', 'services', 'capabilities'],
            'testimonials' => ['testimonial', 'review', 'quote', 'feedback', 'social-proof'],
            'pricing' => ['pricing', 'plans', 'packages', 'tiers'],
            'faq' => ['faq', 'questions', 'accordion'],
            'contact' => ['contact', 'get-in-touch', 'reach-us'],
            'cta' => ['cta', 'call-to-action', 'sign-up', 'get-started'],
            'team' => ['team', 'staff', 'people', 'members'],
            'stats' => ['stats', 'numbers', 'counter', 'metrics'],
            'gallery' => ['gallery', 'portfolio', 'showcase', 'work'],
            'about' => ['about', 'story', 'mission', 'history'],
            'blog' => ['blog', 'articles', 'news', 'posts']
        ];

        $htmlLower = strtolower($html);
        foreach ($sectionKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($htmlLower, $keyword) !== false) {
                    $data['section_types'][] = $type;
                    $data['has_' . $type] = true;
                    break;
                }
            }
        }
        $data['section_types'] = array_unique($data['section_types']);

        // Extract headings
        preg_match_all('/<h[1-3][^>]*>(.*?)<\/h[1-3]>/is', $html, $headingMatches);
        foreach ($headingMatches[1] as $heading) {
            $cleaned = trim(strip_tags($heading));
            if (strlen($cleaned) > 3 && strlen($cleaned) < 200) {
                $data['headings'][] = $cleaned;
            }
        }
        $data['headings'] = array_slice($data['headings'], 0, 10);

        // Detect layout patterns
        if (preg_match_all('/grid-template-columns|display:\s*grid/i', $html)) {
            $data['layout_patterns'][] = 'grid';
        }
        if (preg_match_all('/display:\s*flex/i', $html)) {
            $data['layout_patterns'][] = 'flexbox';
        }
        if (preg_match_all('/col-(?:xs|sm|md|lg|xl)-\d+/i', $html)) {
            $data['layout_patterns'][] = 'bootstrap-columns';
        }
        if (empty($data['layout_patterns'])) {
            $data['layout_patterns'][] = 'standard';
        }

        return $data;
    }

    // ========================================
    // FEATURE 8.3: A/B VARIANTS
    // ========================================

    /**
     * Generate 2-3 variants of a specific section type
     * Each variant has different layout, content angle, and visual approach
     *
     * @param string $sectionType Section type (hero, features, cta, etc.)
     * @param array $context Context (industry, style, prompt, etc.)
     * @param int $count Number of variants (2-3)
     * @return array Variants
     * @since 2026-02-05
     */
    public static function generateVariants(string $sectionType, array $context, int $count = 3): array
    {
        try {
            $startTime = microtime(true);
            $count = max(2, min(4, $count)); // Clamp to 2-4

            $ai = JTB_AI_Core::getInstance();
            if (!$ai->isConfigured()) {
                return ['ok' => false, 'error' => 'AI is not configured'];
            }

            $industry = $context['industry'] ?? 'general';
            $style = $context['style'] ?? 'modern';
            $prompt = $context['prompt'] ?? '';
            $schemas = JTB_AI_Schema::getCompactSchemasForAI();

            $variantStyles = [
                ['label' => 'Bold & Impactful', 'desc' => 'Large typography, strong contrast, commanding presence', 'layout' => '1'],
                ['label' => 'Split Layout', 'desc' => 'Two-column with text and image/media side by side', 'layout' => '1_2,1_2'],
                ['label' => 'Centered & Clean', 'desc' => 'Centered content, minimal design, elegant spacing', 'layout' => '1'],
                ['label' => 'Asymmetric', 'desc' => 'Uneven columns, dynamic layout, modern feel', 'layout' => '2_3,1_3'],
            ];

            $variants = [];

            for ($i = 0; $i < $count; $i++) {
                $variantStyle = $variantStyles[$i % count($variantStyles)];

                $variantPrompt = "Generate a SINGLE \"{$sectionType}\" section for a {$industry} website.\n"
                    . "Style: {$variantStyle['desc']}\n"
                    . "Layout: {$variantStyle['layout']}\n"
                    . "Business: {$prompt}\n\n"
                    . "This is variant " . ($i + 1) . " of {$count}. Make it DISTINCTLY DIFFERENT from other variants.\n"
                    . "Return ONLY a JSON object with this exact structure:\n"
                    . "{\"type\": \"section\", \"attrs\": {...}, \"children\": [{\"type\": \"row\", \"attrs\": {\"columns\": \"{$variantStyle['layout']}\"}, \"children\": [...]}]}\n\n"
                    . "Use ONLY these module types and attributes:\n{$schemas}";

                $response = $ai->query($variantPrompt, [
                    'system_prompt' => "You are a web designer creating section variants. Return ONLY valid JSON for a single section. No markdown, no explanation. Industry: {$industry}, Style: {$style}.",
                    'temperature' => 0.8 + ($i * 0.05), // Slightly increase temperature for more variety
                    'max_tokens' => 4000
                ]);

                if ($response['ok'] && !empty($response['text'])) {
                    $section = self::parseVariantJson($response['text']);
                    if ($section) {
                        // Normalize slugs and add IDs
                        $section = self::normalizeSlugs([$section])[0] ?? $section;
                        $counter = $i * 100;
                        $addId = function(&$node) use (&$addId, &$counter, $i) {
                            if (is_array($node)) {
                                if (isset($node['type']) && empty($node['id'])) {
                                    $node['id'] = 'var' . ($i + 1) . '_' . $node['type'] . '_' . (++$counter);
                                }
                                if (isset($node['children'])) {
                                    foreach ($node['children'] as &$child) {
                                        $addId($child);
                                    }
                                }
                            }
                        };
                        $addId($section);

                        $variants[] = [
                            'id' => 'v' . ($i + 1),
                            'label' => $variantStyle['label'],
                            'description' => $variantStyle['desc'],
                            'layout' => $variantStyle['layout'],
                            'sections' => [$section]
                        ];
                    }
                }
            }

            if (empty($variants)) {
                return ['ok' => false, 'error' => 'Failed to generate any variants'];
            }

            $timeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'variants' => $variants,
                'section_type' => $sectionType,
                'stats' => [
                    'time_ms' => $timeMs,
                    'variants_generated' => count($variants),
                    'requested' => $count
                ]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::generateVariants error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse variant JSON from AI response
     */
    private static function parseVariantJson(string $content): ?array
    {
        $content = trim($content);

        // Remove markdown code blocks
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)\n?```/', $content, $matches)) {
            $content = $matches[1];
        }

        $decoded = json_decode($content, true);
        if ($decoded && isset($decoded['type'])) {
            return $decoded;
        }

        // Try finding JSON object
        $start = strpos($content, '{');
        if ($start !== false) {
            $decoded = json_decode(substr($content, $start), true);
            if ($decoded && isset($decoded['type'])) {
                return $decoded;
            }
        }

        return null;
    }

    // ========================================
    // FEATURE 8.4: CONTENT REGENERATION
    // ========================================

    /**
     * Regenerate content for a specific module based on instruction
     * "Make this more professional", "Shorten this text", "Add more emotion"
     *
     * @param string $moduleType Module type (heading, text, blurb, etc.)
     * @param array $attrs Current module attributes
     * @param string $instruction User instruction for regeneration
     * @param array $context Additional context
     * @return array Updated attributes
     * @since 2026-02-05
     */
    public static function regenerateContent(string $moduleType, array $attrs, string $instruction, array $context = []): array
    {
        try {
            $startTime = microtime(true);

            $ai = JTB_AI_Core::getInstance();
            if (!$ai->isConfigured()) {
                return ['ok' => false, 'error' => 'AI is not configured'];
            }

            // Identify text fields for this module type
            $textFields = self::getTextFieldsForModule($moduleType);
            if (empty($textFields)) {
                return ['ok' => false, 'error' => "Module type '{$moduleType}' has no text fields to regenerate"];
            }

            // Build current content summary
            $currentContent = [];
            foreach ($textFields as $field) {
                if (!empty($attrs[$field])) {
                    $currentContent[$field] = strip_tags($attrs[$field]);
                }
            }

            if (empty($currentContent)) {
                return ['ok' => false, 'error' => 'No text content found to regenerate'];
            }

            $currentJson = json_encode($currentContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $industry = $context['industry'] ?? 'general';

            $prompt = "I have a \"{$moduleType}\" module with this text content:\n{$currentJson}\n\n"
                . "User instruction: \"{$instruction}\"\n\n"
                . "Industry: {$industry}\n\n"
                . "Apply the user's instruction to regenerate the text content.\n"
                . "Return ONLY a JSON object with the updated text fields.\n"
                . "Keep the SAME keys, only change values.\n"
                . "Preserve any HTML formatting tags (<strong>, <em>, <br>) if present.\n"
                . "Do NOT change non-text attributes (colors, sizes, URLs, images).";

            $response = $ai->query($prompt, [
                'system_prompt' => 'You are a professional copywriter. Apply the instruction to improve the text. Return ONLY valid JSON with updated text fields. No explanation.',
                'temperature' => 0.6,
                'max_tokens' => 2000
            ]);

            if (!$response['ok']) {
                return ['ok' => false, 'error' => 'AI query failed: ' . ($response['error'] ?? 'unknown')];
            }

            $updatedText = json_decode($response['text'] ?? '', true);
            if (!$updatedText) {
                // Try extracting JSON
                if (preg_match('/\{[\s\S]*\}/', $response['text'], $m)) {
                    $updatedText = json_decode($m[0], true);
                }
            }

            if (!$updatedText) {
                return ['ok' => false, 'error' => 'Failed to parse AI response'];
            }

            // Merge updated text fields into original attrs (preserving non-text attrs)
            $updatedAttrs = $attrs;
            foreach ($textFields as $field) {
                if (isset($updatedText[$field])) {
                    $updatedAttrs[$field] = $updatedText[$field];
                }
            }

            $timeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'attrs' => $updatedAttrs,
                'changed_fields' => array_keys($updatedText),
                'stats' => [
                    'time_ms' => $timeMs,
                    'module_type' => $moduleType,
                    'instruction' => $instruction
                ]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::regenerateContent error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get text fields for a given module type
     */
    private static function getTextFieldsForModule(string $moduleType): array
    {
        // Dynamically get text fields from Registry
        try {
            $instance = JTB_Registry::get($moduleType);
            if ($instance) {
                $fields = $instance->getFields();
                $textFields = [];
                foreach ($fields as $fieldName => $fieldDef) {
                    $type = $fieldDef['type'] ?? 'text';
                    if (in_array($type, ['text', 'textarea', 'richtext'])) {
                        $textFields[] = $fieldName;
                    }
                }
                return $textFields;
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        return ['text', 'content', 'title'];
    }

    // ========================================
    // FEATURE 8.5: MULTI-LANGUAGE
    // ========================================

    /**
     * Translate entire website to target language
     * Preserves structure, styling, images - only translates text
     *
     * @param array $website Complete website structure
     * @param string $targetLanguage Target language code or name (e.g., 'pl', 'Polish', 'de', 'German')
     * @param array $options Translation options
     * @return array Translated website
     * @since 2026-02-05
     */
    public static function translateWebsite(array $website, string $targetLanguage, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $ai = JTB_AI_Core::getInstance();
            if (!$ai->isConfigured()) {
                return ['ok' => false, 'error' => 'AI is not configured'];
            }

            // Normalize language
            $langName = self::normalizeLanguageName($targetLanguage);

            // Collect all text content from website
            $textMap = [];
            self::collectTexts($website, $textMap, '');

            if (empty($textMap)) {
                return ['ok' => false, 'error' => 'No text content found to translate'];
            }

            // Split into chunks for translation (max ~50 items per chunk to avoid token limits)
            $chunks = array_chunk($textMap, 50, true);
            $translatedMap = [];

            foreach ($chunks as $chunkIndex => $chunk) {
                $textsJson = json_encode($chunk, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                $prompt = "Translate ALL the following text strings to {$langName}.\n"
                    . "Return ONLY a JSON object with the same keys and translated values.\n"
                    . "Preserve HTML tags (<strong>, <em>, <br>, <a href>).\n"
                    . "Translate naturally and professionally - not word-by-word.\n"
                    . "Keep brand names, URLs, email addresses unchanged.\n"
                    . "SEO-optimize the translated text.\n\n"
                    . "Text to translate:\n{$textsJson}";

                $response = $ai->query($prompt, [
                    'system_prompt' => "You are a professional translator specializing in website localization to {$langName}. "
                        . "Translate all text naturally while preserving HTML structure. Return ONLY valid JSON.",
                    'temperature' => 0.3,
                    'max_tokens' => 8000
                ]);

                if ($response['ok'] && !empty($response['text'])) {
                    $translated = json_decode($response['text'], true);
                    if (!$translated) {
                        // Try extracting JSON
                        if (preg_match('/\{[\s\S]*\}/', $response['text'], $m)) {
                            $translated = json_decode($m[0], true);
                        }
                    }
                    if ($translated) {
                        $translatedMap = array_merge($translatedMap, $translated);
                    }
                }
            }

            if (empty($translatedMap)) {
                return ['ok' => false, 'error' => 'Translation failed - no translated content returned'];
            }

            // Apply translations back to website structure
            $translatedWebsite = $website;
            self::applyTranslations($translatedWebsite, $translatedMap, '');

            // Update SEO data for translated version
            if (isset($translatedWebsite['seo'])) {
                $translatedWebsite['seo']['global']['locale'] = self::getLocaleCode($targetLanguage);
            }

            $timeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'website' => $translatedWebsite,
                'language' => $langName,
                'stats' => [
                    'time_ms' => $timeMs,
                    'texts_translated' => count($translatedMap),
                    'total_texts' => count($textMap),
                    'chunks_processed' => count($chunks)
                ]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::translateWebsite error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Recursively collect all text values from website structure
     */
    private static function collectTexts(array $data, array &$textMap, string $path): void
    {
        $textKeys = ['text', 'content', 'title', 'subtitle', 'name', 'position', 'bio',
            'author', 'job_title', 'company', 'button_text', 'price', 'period',
            'features', 'placeholder', 'success_message', 'copyright_text',
            'read_more_text', 'logo_alt', 'alt', 'description', 'label', 'suffix'];

        foreach ($data as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : (string)$key;

            if (is_string($value) && in_array($key, $textKeys) && strlen(trim(strip_tags($value))) > 0) {
                // Skip URLs, hex colors, CSS values
                if (preg_match('/^(https?:\/\/|#[0-9a-fA-F]{3,8}$|[\d.]+(px|em|rem|%)|rgb)/', $value)) {
                    continue;
                }
                $textMap[$currentPath] = $value;
            } elseif (is_array($value)) {
                // Handle features arrays (array of strings)
                if ($key === 'features' && isset($value[0]) && is_string($value[0])) {
                    foreach ($value as $i => $feature) {
                        if (is_string($feature) && strlen(trim($feature)) > 0) {
                            $textMap["{$currentPath}.{$i}"] = $feature;
                        }
                    }
                } else {
                    self::collectTexts($value, $textMap, $currentPath);
                }
            }
        }
    }

    /**
     * Apply translated values back to website structure
     */
    private static function applyTranslations(array &$data, array $translatedMap, string $path): void
    {
        foreach ($data as $key => &$value) {
            $currentPath = $path ? "{$path}.{$key}" : (string)$key;

            if (is_string($value) && isset($translatedMap[$currentPath])) {
                $value = $translatedMap[$currentPath];
            } elseif (is_array($value)) {
                if ($key === 'features' && isset($value[0]) && is_string($value[0])) {
                    foreach ($value as $i => &$feature) {
                        $featurePath = "{$currentPath}.{$i}";
                        if (isset($translatedMap[$featurePath])) {
                            $feature = $translatedMap[$featurePath];
                        }
                    }
                } else {
                    self::applyTranslations($value, $translatedMap, $currentPath);
                }
            }
        }
    }

    /**
     * Normalize language name from code or name
     */
    private static function normalizeLanguageName(string $input): string
    {
        $languages = [
            'en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French',
            'es' => 'Spanish', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch',
            'sv' => 'Swedish', 'da' => 'Danish', 'no' => 'Norwegian', 'fi' => 'Finnish',
            'ru' => 'Russian', 'uk' => 'Ukrainian', 'cs' => 'Czech', 'sk' => 'Slovak',
            'hu' => 'Hungarian', 'ro' => 'Romanian', 'bg' => 'Bulgarian', 'hr' => 'Croatian',
            'sl' => 'Slovenian', 'sr' => 'Serbian', 'ja' => 'Japanese', 'ko' => 'Korean',
            'zh' => 'Chinese', 'ar' => 'Arabic', 'hi' => 'Hindi', 'th' => 'Thai',
            'vi' => 'Vietnamese', 'tr' => 'Turkish', 'el' => 'Greek', 'he' => 'Hebrew'
        ];

        $lower = strtolower(trim($input));

        // Direct code match
        if (isset($languages[$lower])) {
            return $languages[$lower];
        }

        // Already a language name
        foreach ($languages as $name) {
            if (strtolower($name) === $lower) {
                return $name;
            }
        }

        // Return as-is (user might have typed full name)
        return ucfirst($input);
    }

    /**
     * Get locale code from language
     */
    private static function getLocaleCode(string $input): string
    {
        $locales = [
            'en' => 'en_US', 'pl' => 'pl_PL', 'de' => 'de_DE', 'fr' => 'fr_FR',
            'es' => 'es_ES', 'it' => 'it_IT', 'pt' => 'pt_BR', 'nl' => 'nl_NL',
            'ru' => 'ru_RU', 'ja' => 'ja_JP', 'ko' => 'ko_KR', 'zh' => 'zh_CN',
            'ar' => 'ar_SA', 'tr' => 'tr_TR', 'cs' => 'cs_CZ', 'sv' => 'sv_SE',
            'polish' => 'pl_PL', 'german' => 'de_DE', 'french' => 'fr_FR',
            'spanish' => 'es_ES', 'italian' => 'it_IT', 'japanese' => 'ja_JP',
            'korean' => 'ko_KR', 'chinese' => 'zh_CN', 'russian' => 'ru_RU',
            'turkish' => 'tr_TR', 'czech' => 'cs_CZ'
        ];

        return $locales[strtolower(trim($input))] ?? 'en_US';
    }

    // ========================================
    // FEATURE 8.6: PROGRESSIVE ENHANCEMENT
    // ========================================

    /**
     * Generate website progressively in stages
     * Stage 1 (quick): Basic structure with hero + 2-3 sections
     * Stage 2: Expand to full 6-10 sections per page
     * Stage 3: Enrich with images from Pexels
     * Stage 4: Improve content quality, add micro-copy
     *
     * @param string $prompt User's business description
     * @param array $options Generation options
     * @return array Stages with progressive results
     * @since 2026-02-05
     */
    public static function progressiveGenerate(string $prompt, array $options = []): array
    {
        try {
            $startTime = microtime(true);

            $ai = JTB_AI_Core::getInstance();
            if (!$ai->isConfigured()) {
                return ['ok' => false, 'error' => 'AI is not configured'];
            }

            $industry = $options['industry'] ?? self::detectIndustry($prompt);
            $style = $options['style'] ?? 'modern';
            $pages = $options['pages'] ?? ['home', 'about', 'services', 'contact'];
            $industryTemplate = self::INDUSTRY_TEMPLATES[$industry] ?? self::INDUSTRY_TEMPLATES['general'];
            // Colors from SINGLE SOURCE OF TRUTH
            $colors = JTB_AI_Styles::getIndustryColors($industry);
            $schemas = JTB_AI_Schema::getCompactSchemasForAI();

            $stages = [];

            // ---- STAGE 1: Quick skeleton (hero + 2-3 sections per page) ----
            $stage1Prompt = "Generate a QUICK SKELETON website for: \"{$prompt}\"\n"
                . "Industry: {$industry}, Style: {$style}\n"
                . "Pages: " . implode(', ', $pages) . "\n\n"
                . "IMPORTANT: This is a SPEED run. Each page should have ONLY 3 sections:\n"
                . "1. Hero with headline and CTA\n"
                . "2. Main content section (features/services/about)\n"
                . "3. Final CTA\n\n"
                . "Header: simple with logo + menu + button\n"
                . "Footer: simple 2-column with links + copyright\n\n"
                . "Use these colors: primary={$colors['primary']}, secondary={$colors['secondary']}, accent={$colors['accent']}\n\n"
                . "Return ONLY valid JSON. Structure: {\"header\":{\"sections\":[...]}, \"footer\":{\"sections\":[...]}, \"pages\":{\"home\":{\"title\":\"...\",\"sections\":[...]}}}\n\n"
                . "Module reference:\n{$schemas}";

            $response1 = $ai->query($stage1Prompt, [
                'system_prompt' => "You are a fast web designer. Create a minimal website skeleton quickly. Return ONLY valid JSON, no explanation. Each page: exactly 3 sections.",
                'temperature' => 0.5,
                'max_tokens' => 8000
            ]);

            if ($response1['ok'] && !empty($response1['text'])) {
                $skeleton = self::parseWebsiteJson($response1['text']);
                if ($skeleton) {
                    $skeleton = self::addUniqueIds($skeleton);
                    $skeleton = self::postProcess($skeleton, ['industry' => $industry]);
                    $stages[] = [
                        'stage' => 1,
                        'label' => 'Quick Skeleton',
                        'description' => 'Basic structure with hero and key sections',
                        'website' => $skeleton,
                        'time_ms' => round((microtime(true) - $startTime) * 1000)
                    ];
                }
            }

            // ---- STAGE 2: Expand sections (add more sections to each page) ----
            $stage2Start = microtime(true);

            if (!empty($stages)) {
                $currentWebsite = $stages[0]['website'];
                $expandedWebsite = $currentWebsite;

                // For each page, ask AI to add more sections
                foreach ($pages as $pageSlug) {
                    $existingSections = $expandedWebsite['pages'][$pageSlug]['sections'] ?? [];
                    $existingTypes = [];
                    foreach ($existingSections as $s) {
                        if (isset($s['attrs']['_pattern'])) {
                            $existingTypes[] = $s['attrs']['_pattern'];
                        }
                    }

                    $recommendedSections = $industryTemplate[$pageSlug] ?? ['hero', 'features', 'about', 'testimonials', 'cta'];
                    $missingSections = array_diff($recommendedSections, $existingTypes);
                    $missingSections = array_slice($missingSections, 0, 5); // Max 5 additional sections

                    if (empty($missingSections)) continue;

                    $expandPrompt = "Add " . count($missingSections) . " MORE sections to the \"{$pageSlug}\" page.\n"
                        . "The page currently has " . count($existingSections) . " sections.\n"
                        . "Add these section types: " . implode(', ', $missingSections) . "\n"
                        . "Business: {$prompt}\n"
                        . "Industry: {$industry}, Style: {$style}\n\n"
                        . "Return ONLY a JSON array of section objects to APPEND.\n"
                        . "Each section: {\"type\":\"section\",\"attrs\":{...},\"children\":[{\"type\":\"row\",...}]}\n\n"
                        . "Module reference:\n{$schemas}";

                    $response2 = $ai->query($expandPrompt, [
                        'system_prompt' => "You are a web designer. Generate additional page sections. Return ONLY a JSON array of sections. No explanation.",
                        'temperature' => 0.6,
                        'max_tokens' => 8000
                    ]);

                    if ($response2['ok'] && !empty($response2['text'])) {
                        $newSections = json_decode($response2['text'], true);
                        if (!$newSections && preg_match('/\[[\s\S]*\]/', $response2['text'], $m)) {
                            $newSections = json_decode($m[0], true);
                        }
                        if (is_array($newSections)) {
                            // If it's wrapped in an object, extract sections
                            if (isset($newSections['sections'])) {
                                $newSections = $newSections['sections'];
                            }
                            // Ensure it's a flat array of sections
                            if (isset($newSections[0]) && isset($newSections[0]['type'])) {
                                $expandedWebsite['pages'][$pageSlug]['sections'] = array_merge(
                                    $existingSections,
                                    self::normalizeSlugs($newSections)
                                );
                            }
                        }
                    }
                }

                $expandedWebsite = self::addUniqueIds($expandedWebsite, 'stg2');

                $stages[] = [
                    'stage' => 2,
                    'label' => 'Expanded Content',
                    'description' => 'Full page sections with all content areas',
                    'website' => $expandedWebsite,
                    'time_ms' => round((microtime(true) - $stage2Start) * 1000)
                ];
            }

            // ---- STAGE 3: Enrich with Pexels images ----
            $stage3Start = microtime(true);

            if (count($stages) >= 2) {
                $imageWebsite = $stages[1]['website'];

                if (class_exists(__NAMESPACE__ . '\\JTB_AI_Pexels') && JTB_AI_Pexels::isConfigured()) {
                    $imageWebsite = self::enrichWithImages($imageWebsite, ['industry' => $industry]);
                }

                $stages[] = [
                    'stage' => 3,
                    'label' => 'Image Enrichment',
                    'description' => 'Professional stock images added',
                    'website' => $imageWebsite,
                    'time_ms' => round((microtime(true) - $stage3Start) * 1000)
                ];
            }

            // ---- STAGE 4: Quality polish (better copy, micro-copy) ----
            $stage4Start = microtime(true);

            if (count($stages) >= 3) {
                $polishedWebsite = $stages[2]['website'];

                // Collect all text for polishing
                $textMap = [];
                self::collectTexts($polishedWebsite, $textMap, '');

                // Only polish short texts (headlines, button texts, CTAs)
                $toPolish = [];
                foreach ($textMap as $path => $text) {
                    $cleanText = strip_tags($text);
                    if (strlen($cleanText) > 3 && strlen($cleanText) < 100) {
                        $toPolish[$path] = $text;
                    }
                }

                if (!empty($toPolish)) {
                    $toPolishJson = json_encode(array_slice($toPolish, 0, 40), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                    $polishPrompt = "Polish these website texts to be MORE professional, compelling, and conversion-optimized.\n"
                        . "Industry: {$industry}\n"
                        . "Return ONLY a JSON object with same keys and improved values.\n"
                        . "Rules:\n"
                        . "- Headlines: Use power words, be benefit-focused\n"
                        . "- CTAs: Use action verbs, create urgency\n"
                        . "- Descriptions: Be concise and specific\n"
                        . "- Preserve HTML tags if present\n\n"
                        . "Texts:\n{$toPolishJson}";

                    $response4 = $ai->query($polishPrompt, [
                        'system_prompt' => 'You are an expert copywriter. Polish text for maximum impact. Return ONLY JSON.',
                        'temperature' => 0.5,
                        'max_tokens' => 4000
                    ]);

                    if ($response4['ok'] && !empty($response4['text'])) {
                        $polished = json_decode($response4['text'], true);
                        if (!$polished && preg_match('/\{[\s\S]*\}/', $response4['text'], $m)) {
                            $polished = json_decode($m[0], true);
                        }
                        if ($polished) {
                            self::applyTranslations($polishedWebsite, $polished, '');
                        }
                    }
                }

                // Generate SEO data for final version
                $seoData = self::generateSeoData($polishedWebsite, $prompt, $industry, $options);
                $polishedWebsite['seo'] = $seoData;

                $stages[] = [
                    'stage' => 4,
                    'label' => 'Quality Polish',
                    'description' => 'Improved copy, SEO data, and micro-copy',
                    'website' => $polishedWebsite,
                    'time_ms' => round((microtime(true) - $stage4Start) * 1000)
                ];
            }

            $totalTimeMs = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'stages' => $stages,
                'final_website' => !empty($stages) ? end($stages)['website'] : null,
                'stats' => [
                    'total_time_ms' => $totalTimeMs,
                    'stages_completed' => count($stages),
                    'industry' => $industry,
                    'style' => $style
                ]
            ];

        } catch (\Exception $e) {
            error_log('JTB_AI_Website::progressiveGenerate error: ' . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }
}
