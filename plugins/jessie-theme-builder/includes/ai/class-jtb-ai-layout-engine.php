<?php
/**
 * JTB AI Layout Engine
 * Generates Layout AST using actual AI calls
 *
 * This is the CRITICAL class that makes AI actually design layouts
 * instead of selecting from hardcoded patterns.
 *
 * @package JessieThemeBuilder
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Layout_Engine
{
    // ========================================
    // MAIN GENERATION METHOD
    // ========================================

    // ========================================
    // DEFAULT AI PROVIDER AND MODEL
    // ========================================

    /** Default AI provider for layout generation */
    const DEFAULT_PROVIDER = 'anthropic';

    /** Default model for layout generation (Claude Opus 4.5) */
    const DEFAULT_MODEL = 'claude-opus-4-5-20251101';

    /** Available providers and their recommended models for layout generation */
    const PROVIDER_MODELS = [
        'anthropic' => [
            'name' => 'Anthropic Claude',
            'default' => 'claude-opus-4-5-20251101',
            'models' => [
                'claude-opus-4-5-20251101' => 'Claude Opus 4.5 (Best for Themes)',
                'claude-sonnet-4-5-20250929' => 'Claude Sonnet 4.5 (Best Balance)',
                'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (Fastest)',
            ]
        ],
        'openai' => [
            'name' => 'OpenAI',
            'default' => 'gpt-4o',
            'models' => [
                'gpt-5.2' => 'GPT-5.2 (Flagship Thinking)',
                'gpt-5' => 'GPT-5 (General/Agentic)',
                'gpt-4o' => 'GPT-4o (Legacy)',
                'gpt-4o-mini' => 'GPT-4o Mini (Fast)',
            ]
        ],
        'google' => [
            'name' => 'Google Gemini',
            'default' => 'gemini-2.0-flash',
            'models' => [
                'gemini-2.5-pro' => 'Gemini 2.5 Pro (Best Quality)',
                'gemini-2.5-flash' => 'Gemini 2.5 Flash (Latest)',
                'gemini-2.0-flash' => 'Gemini 2.0 Flash (Free Tier)',
            ]
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'default' => 'deepseek-chat',
            'models' => [
                'deepseek-r1' => 'DeepSeek R1 (Reasoning)',
                'deepseek-v3' => 'DeepSeek V3',
            ]
        ],
    ];

    /**
     * Generate Layout AST from user prompt using AI
     *
     * THIS IS WHERE AI IS ACTUALLY CALLED FOR LAYOUT DECISIONS
     *
     * @param string $prompt User's description of desired page
     * @param array $context Additional context (style, industry, provider, model, etc.)
     * @return array Result with 'ok', 'ast', 'source', 'provider', 'model'
     */
    public static function generateLayoutAST(string $prompt, array $context = []): array
    {
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] generateLayoutAST ENTRY\n", FILE_APPEND);

        $ai = JTB_AI_Core::getInstance();

        // Check if AI is configured
        $isConfigured = $ai->isConfigured();
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] AI isConfigured: " . ($isConfigured ? 'YES' : 'NO') . "\n", FILE_APPEND);

        if (!$isConfigured) {
            file_put_contents('/tmp/jtb_engine.log', "[ENGINE] USING FALLBACK - AI not configured!\n", FILE_APPEND);
            return self::generateFallbackAST($prompt, $context);
        }

        // Determine provider and model
        $requestedProvider = $context['ai_provider'] ?? self::DEFAULT_PROVIDER;
        $requestedModel = $context['ai_model'] ?? null;

        // Check if requested provider is available
        $availableProviders = $ai->getAvailableProviders();
        if (!in_array($requestedProvider, $availableProviders)) {
            // Fallback to first available provider (from config, not hardcoded)
            $fallback = self::getDefaultProvider();
            $requestedProvider = $availableProviders[0] ?? $fallback;
            error_log("JTB AI Layout Engine: Requested provider not available, using: {$requestedProvider}");
        }

        // Set provider
        $ai->setProvider($requestedProvider);

        // Determine model
        if (empty($requestedModel)) {
            $requestedModel = self::PROVIDER_MODELS[$requestedProvider]['default'] ?? null;
        }

        // Build prompts
        $systemPrompt = self::buildLayoutSystemPrompt($context);
        $userPrompt = self::buildLayoutUserPrompt($prompt, $context);

        // Log the attempt
        error_log("JTB AI Layout Engine: Generating layout AST using {$requestedProvider}" . ($requestedModel ? " ({$requestedModel})" : ""));
        error_log('JTB AI Layout Engine: Prompt: ' . substr($prompt, 0, 100));

        // Build query options
        $queryOptions = [
            'system_prompt' => $systemPrompt,
            'json_mode' => true,
            'max_tokens' => 4000,
            'temperature' => 0.7,
        ];

        // Add model if specified
        if ($requestedModel) {
            $queryOptions['model'] = $requestedModel;
        }

        // CRITICAL: Actually call AI for layout!
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] Calling AI query...\n", FILE_APPEND);
        $response = $ai->query($userPrompt, $queryOptions);
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] AI response ok=" . ($response['ok'] ? 'YES' : 'NO') . "\n", FILE_APPEND);

        if (!$response['ok']) {
            file_put_contents('/tmp/jtb_engine.log', "[ENGINE] AI FAILED: " . ($response['error'] ?? 'unknown') . "\n", FILE_APPEND);
            error_log('JTB AI Layout Engine: AI query failed - ' . ($response['error'] ?? 'unknown'));
            return self::generateFallbackAST($prompt, $context);
        }

        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] AI returned text length: " . strlen($response['text'] ?? '') . "\n", FILE_APPEND);
        file_put_contents('/tmp/jtb_ai_response.log', $response['text'] ?? 'NO TEXT', FILE_APPEND);

        // Parse response
        $ast = $response['json'] ?? null;
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] json from response: " . (is_array($ast) ? 'ARRAY' : 'NULL') . "\n", FILE_APPEND);

        // If json_mode didn't work, try to parse from text
        if (!is_array($ast) && !empty($response['text'])) {
            file_put_contents('/tmp/jtb_engine.log', "[ENGINE] Trying to parse from text...\n", FILE_APPEND);
            $ast = self::parseASTFromText($response['text']);
            file_put_contents('/tmp/jtb_engine.log', "[ENGINE] parseASTFromText result: " . (is_array($ast) ? 'ARRAY with ' . count($ast) . ' keys' : 'NULL') . "\n", FILE_APPEND);
        }

        if (!is_array($ast)) {
            file_put_contents('/tmp/jtb_engine.log', "[ENGINE] PARSE FAILED - going to fallback\n", FILE_APPEND);
            error_log('JTB AI Layout Engine: Failed to parse AI response as JSON');
            return self::generateFallbackAST($prompt, $context);
        }

        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] AST parsed OK, validating...\n", FILE_APPEND);

        // Validate AST structure
        $validation = JTB_AI_Layout_AST::validate($ast);
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] Validation: " . ($validation['valid'] ? 'VALID' : 'INVALID: ' . json_encode($validation['errors'])) . "\n", FILE_APPEND);

        if (!$validation['valid']) {
            error_log('JTB AI Layout Engine: AST validation failed - ' . json_encode($validation['errors']));

            // Try to auto-fix common issues
            $ast = self::autoFixAST($ast, $validation['errors']);

            // Re-validate
            $validation = JTB_AI_Layout_AST::validate($ast);
            if (!$validation['valid']) {
                error_log('JTB AI Layout Engine: AST still invalid after autofix, using fallback');
                return self::generateFallbackAST($prompt, $context);
            }
        }

        // Log warnings
        if (!empty($validation['warnings'])) {
            error_log('JTB AI Layout Engine: AST warnings - ' . json_encode($validation['warnings']));
        }

        return [
            'ok' => true,
            'ast' => $ast,
            'source' => 'ai',
            'provider' => $requestedProvider,
            'model' => $requestedModel,
            'tokens_used' => $response['tokens_used'] ?? 0,
            'time_ms' => $response['time_ms'] ?? 0,
            'validation' => $validation,
        ];
    }

    /**
     * Get available AI providers for layout generation
     *
     * @return array Available providers with their models
     */
    public static function getAvailableProviders(): array
    {
        $ai = JTB_AI_Core::getInstance();
        $configured = $ai->getAvailableProviders();

        $result = [];
        foreach ($configured as $provider) {
            if (isset(self::PROVIDER_MODELS[$provider])) {
                $result[$provider] = self::PROVIDER_MODELS[$provider];
            }
        }

        return $result;
    }

    /**
     * Get default provider and model
     *
     * @return array ['provider' => string, 'model' => string]
     */
    public static function getDefaultProviderModel(): array
    {
        return [
            'provider' => self::DEFAULT_PROVIDER,
            'model' => self::DEFAULT_MODEL,
        ];
    }

    // ========================================
    // PROMPT BUILDING
    // ========================================

    /**
     * Build system prompt for layout generation
     *
     * @param array $context Context data
     * @return string System prompt
     */
    private static function buildLayoutSystemPrompt(array $context = []): string
    {
        $schema = JTB_AI_Layout_AST::getJsonSchemaString();

        $prompt = <<<PROMPT
You are an expert web page layout architect. Your task is to design page layouts as an Abstract Syntax Tree (AST).

## YOUR ROLE
You make ALL layout decisions:
- How many sections the page needs (typically 4-8)
- What type each section is (hero, features, testimonials, pricing, etc.)
- The intent of each section in the user journey (capture attention, explain, prove, convert)
- The layout structure (asymmetric, centered, grid, alternating)
- Column arrangements and widths (using 12-column grid)
- What elements go in each column

## CRITICAL: You DECIDE the structure. Do NOT just follow a template.
Consider the user's specific needs and create a custom layout.

## OUTPUT FORMAT
Return ONLY valid JSON matching this schema:
{$schema}

## LAYOUT DESIGN PRINCIPLES

1. **Start with Impact**: Hero section that captures attention immediately
2. **Build Trust Early**: Social proof, logos, or metrics in sections 2-3
3. **Explain Before Selling**: Features and benefits before pricing
4. **Vary Visual Rhythm**: Alternate layouts (asymmetric → grid → centered)
5. **Progressive Disclosure**: Don't overwhelm - space out information
6. **Clear CTA Path**: End with strong call-to-action
7. **Breathing Room**: Not every section needs to be dense

## COLUMN WIDTH PATTERNS (12-column grid)
- `[12]` = Full width (centered content, CTAs)
- `[7, 5]` or `[5, 7]` = Asymmetric (content + image)
- `[6, 6]` = Equal split (comparison, before/after)
- `[4, 4, 4]` = Three columns (features, pricing tiers)
- `[3, 3, 3, 3]` = Four columns (team members, logos)
- `[8, 4]` or `[4, 8]` = Dominant + supporting

## VISUAL WEIGHT
- `high`: Hero, final CTA - can use dark/bold backgrounds
- `medium`: Features, testimonials, pricing - standard styling
- `low`: FAQ, dividers, footer elements - subtle, breathing space

## SECTION INTENT GUIDE
- `capture`: Grab attention (hero only)
- `explain`: What we do/offer (features, services, about)
- `prove`: Build credibility (testimonials, stats, logos)
- `convince`: Overcome objections (benefits, comparisons)
- `convert`: Drive action (pricing, CTA)
- `reassure`: Handle doubts (FAQ, guarantees)
- `connect`: Enable contact (forms, contact info)
- `breathe`: Visual break (dividers, spacing)

## ELEMENT TYPES
Text: headline, subheadline, body_text, label
Actions: cta_primary, cta_secondary, link
Visual: image_hero, image_feature, video, icon, divider
Data: stat, counter, progress
Social Proof: testimonial, logo, logo_grid, rating
Commerce: pricing_card, feature_list
Interactive: faq_item, tab, accordion
Forms: form, newsletter
Composite: card, blurb, team_member

## CRITICAL: GENERATE REAL CONTENT

For EVERY element, you MUST provide a "content" object with actual text/data.
DO NOT leave content empty. Generate professional, realistic content.

Examples:
```json
{"type": "headline", "content": {"text": "Build Faster, Scale Smarter"}}
{"type": "subheadline", "content": {"text": "The all-in-one platform that helps teams ship 10x faster"}}
{"type": "cta_primary", "content": {"text": "Start Free Trial"}}
{"type": "stat", "content": {"value": "10,000+", "label": "Happy Customers"}}
{"type": "testimonial", "content": {"text": "This tool transformed how we work.", "author": "Sarah Chen", "role": "CTO", "company": "TechCorp"}}
{"type": "blurb", "content": {"title": "Lightning Fast", "description": "Deploy in seconds, not hours.", "icon": "zap"}}
{"type": "pricing_card", "content": {"title": "Pro", "price": "$29", "period": "/month", "features": ["Unlimited projects", "Priority support", "API access"]}}
{"type": "faq_item", "content": {"question": "How do I get started?", "answer": "Sign up for free and follow our quick setup guide."}}
{"type": "image_hero", "content": {"image_prompt": "Modern dashboard interface with analytics charts"}}
```

## CONTENT GUIDELINES
- Headlines: Short (3-6 words), impactful, benefit-focused
- Subheadlines: Expand on the headline, 10-15 words
- Body text: Clear, concise, 1-2 sentences max
- Stats: Use realistic numbers with context
- Testimonials: Sound authentic, include name/role/company
- CTAs: Action verbs, create urgency
- Features: Focus on benefits, not just features

## INDUSTRY-SPECIFIC TIPS
- SaaS: Focus on features, social proof, pricing tiers
- Agency: Portfolio, team, client logos prominent
- E-commerce: Product visuals, trust signals, urgency
- Healthcare: Trust, credentials, contact options
- Restaurant: Menu, atmosphere images, location

Return ONLY the JSON AST. No explanations or markdown.
PROMPT;

        return $prompt;
    }

    /**
     * Build user prompt with context
     *
     * @param string $prompt User's original prompt
     * @param array $context Context data
     * @return string User prompt
     */
    private static function buildLayoutUserPrompt(string $prompt, array $context = []): string
    {
        $parts = ["Design a page layout for:\n\"{$prompt}\""];

        // Add context if available
        if (!empty($context['industry'])) {
            $parts[] = "Industry: {$context['industry']}";
        }
        if (!empty($context['style'])) {
            $parts[] = "Visual style: {$context['style']}";
        }
        if (!empty($context['page_type'])) {
            $parts[] = "Page type: {$context['page_type']}";
        }
        if (!empty($context['site_name'])) {
            $parts[] = "Site name: {$context['site_name']}";
        }
        if (!empty($context['tone'])) {
            $parts[] = "Tone: {$context['tone']}";
        }

        // Add specific requirements if mentioned in prompt
        $requirements = [];

        // Detect specific section requests
        $sectionKeywords = [
            'pricing' => ['pricing', 'plans', 'cost'],
            'testimonials' => ['testimonials', 'reviews', 'feedback'],
            'faq' => ['faq', 'questions'],
            'contact' => ['contact', 'form'],
            'team' => ['team', 'staff'],
            'portfolio' => ['portfolio', 'projects', 'work'],
        ];

        $promptLower = strtolower($prompt);
        foreach ($sectionKeywords as $section => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($promptLower, $keyword) !== false) {
                    $requirements[] = "Include a {$section} section";
                    break;
                }
            }
        }

        if (!empty($requirements)) {
            $parts[] = "\nRequirements:\n- " . implode("\n- ", $requirements);
        }

        $parts[] = "\nReturn the layout AST as JSON.";

        return implode("\n", $parts);
    }

    // ========================================
    // FALLBACK GENERATION
    // ========================================

    /**
     * Generate AST without AI (deterministic fallback)
     * Uses existing Composer logic converted to AST format
     *
     * @param string $prompt User's prompt
     * @param array $context Context data
     * @return array Result with AST
     */
    public static function generateFallbackAST(string $prompt, array $context = []): array
    {
        // NO FALLBACK - AI must be configured!
        file_put_contents('/tmp/jtb_engine.log', "[ENGINE] FALLBACK CALLED - AI NOT CONFIGURED! Returning error.\n", FILE_APPEND);

        return [
            'ok' => false,
            'error' => 'AI is not configured. Please configure an AI provider (OpenAI, Anthropic, or DeepSeek) in CMS settings.',
            'source' => 'none',
            'provider' => 'none',
        ];
    }

    // ========================================
    // AST PARSING AND FIXING
    // ========================================

    /**
     * Parse AST from text response (when json_mode fails)
     *
     * @param string $text AI response text
     * @return array|null Parsed AST or null
     */
    private static function parseASTFromText(string $text): ?array
    {
        // Try direct JSON parse
        $json = @json_decode($text, true);
        if (is_array($json)) {
            return $json;
        }

        // Try to extract from markdown code block
        if (preg_match('/```(?:json)?\s*\n?([\s\S]*?)\n?```/', $text, $matches)) {
            $json = @json_decode(trim($matches[1]), true);
            if (is_array($json)) {
                return $json;
            }
        }

        // Try to find JSON object in text
        if (preg_match('/(\{[\s\S]*\})/', $text, $matches)) {
            $json = @json_decode($matches[1], true);
            if (is_array($json)) {
                return $json;
            }
        }

        return null;
    }

    /**
     * Auto-fix common AST issues
     *
     * @param array $ast AST with issues
     * @param array $errors Validation errors
     * @return array Fixed AST
     */
    private static function autoFixAST(array $ast, array $errors): array
    {
        // Ensure sections is array
        if (!isset($ast['sections']) || !is_array($ast['sections'])) {
            $ast['sections'] = [];
        }

        // Fix each section
        foreach ($ast['sections'] as $i => &$section) {
            // Ensure type exists
            if (empty($section['type'])) {
                $section['type'] = 'features'; // Default
            }

            // Get section defaults
            $defaults = JTB_AI_Layout_AST::getSectionDefaults($section['type']);
            if ($defaults) {
                // Set missing intent
                if (empty($section['intent'])) {
                    $section['intent'] = $defaults['default_intent'];
                }
                // Set missing layout
                if (empty($section['layout'])) {
                    $section['layout'] = $defaults['default_layout'];
                }
                // Set missing weight
                if (empty($section['visual_weight'])) {
                    $section['visual_weight'] = $defaults['default_weight'];
                }
            }

            // Ensure columns is array
            if (!isset($section['columns']) || !is_array($section['columns'])) {
                $section['columns'] = [
                    ['width' => 12, 'elements' => [['type' => 'body_text']]]
                ];
            }

            // Fix columns
            $totalWidth = 0;
            foreach ($section['columns'] as $j => &$column) {
                // Ensure width
                if (!isset($column['width']) || $column['width'] < 1 || $column['width'] > 12) {
                    $column['width'] = 12;
                }
                $totalWidth += $column['width'];

                // Ensure elements
                if (!isset($column['elements']) || !is_array($column['elements'])) {
                    $column['elements'] = [];
                }

                // Fix elements
                foreach ($column['elements'] as $k => &$element) {
                    if (!isset($element['type'])) {
                        $element['type'] = 'body_text';
                    }
                }
            }

            // Normalize column widths if they don't sum to 12
            if ($totalWidth !== 12 && $totalWidth > 0 && count($section['columns']) > 0) {
                $scale = 12 / $totalWidth;
                $newTotal = 0;
                foreach ($section['columns'] as $j => &$column) {
                    if ($j === count($section['columns']) - 1) {
                        // Last column gets remainder
                        $column['width'] = 12 - $newTotal;
                    } else {
                        $column['width'] = max(1, round($column['width'] * $scale));
                        $newTotal += $column['width'];
                    }
                }
            }
        }

        return $ast;
    }

    // ========================================
    // UTILITY METHODS
    // ========================================

    /**
     * Check if AI is available
     *
     * @return bool
     */
    public static function isAIAvailable(): bool
    {
        return JTB_AI_Core::getInstance()->isConfigured();
    }

    /**
     * Get current AI provider
     *
     * @return string
     */
    public static function getProvider(): string
    {
        return JTB_AI_Core::getInstance()->getProvider();
    }

    /**
     * Get default AI provider from config (no hardcodes!)
     *
     * @return string
     */
    private static function getDefaultProvider(): string
    {
        $settingsPath = CMS_ROOT . '/config/ai_settings.json';
        if (file_exists($settingsPath)) {
            $settings = @json_decode(file_get_contents($settingsPath), true);
            if (!empty($settings['default_provider'])) {
                return $settings['default_provider'];
            }
        }
        return 'anthropic'; // fallback only if config missing
    }
}
