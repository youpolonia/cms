<?php
/**
 * JTB AI Agent: Content (Copywriter)
 *
 * Generates professional content for all modules.
 * Responsibilities:
 * - Generate content for each module based on path_map
 * - Use module fields from JTB_Registry (ZERO hardcodes)
 * - Match content style to mockup and business context
 * - Support per-page content generation for shared hosting
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 * @updated 2026-02-05
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Agent_Content
{
    /** @var int Max modules to process per call (shared hosting limit) */
    private const MAX_MODULES_PER_CALL = 15;

    /** @var array Module fields cache */
    private static array $fieldsCache = [];

    /**
     * Execute content generation for a specific page
     *
     * @param array $session Session data with skeleton and path_map
     * @param string $page Page name (home, about, services, contact, header_footer)
     * @return array Result with content for modules
     */
    public static function executeForPage(array $session, string $page): array
    {
        $startTime = microtime(true);

        try {
            // Validate session has required data
            if (empty($session['skeleton']) || empty($session['path_map'])) {
                return [
                    'ok' => false,
                    'error' => 'Missing skeleton or path_map in session'
                ];
            }

            $pathMap = $session['path_map'];
            $skeleton = $session['skeleton'];
            $industry = $session['industry'] ?? 'technology';
            $style = $session['style'] ?? 'modern';
            $prompt = $session['prompt'] ?? '';
            $contentHints = $session['content_hints'] ?? [];

            // Filter paths for this page
            $pagePrefix = ($page === 'header_footer') ? ['header/', 'footer/'] : ["{$page}/"];
            $pagePaths = self::filterPathsForPage($pathMap, $pagePrefix);

            if (empty($pagePaths)) {
                return [
                    'ok' => true,
                    'content' => [],
                    'stats' => ['modules_count' => 0, 'time_ms' => 0]
                ];
            }

            // Build module info for each path (from Registry)
            $moduleInfos = self::buildModuleInfos($pagePaths, $skeleton);

            // Generate content via AI
            $content = self::generateContent($moduleInfos, [
                'industry' => $industry,
                'style' => $style,
                'prompt' => $prompt,
                'page' => $page,
                'content_hints' => $contentHints
            ]);

            $duration = round((microtime(true) - $startTime) * 1000);

            return [
                'ok' => true,
                'content' => $content['content'] ?? [],
                'stats' => [
                    'time_ms' => $duration,
                    'modules_count' => count($content['content'] ?? [])
                ],
                'tokens_used' => $content['tokens_used'] ?? 0
            ];

        } catch (\Exception $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Filter path_map for specific page(s)
     *
     * @param array $pathMap Full path map
     * @param array $prefixes Page prefixes to filter
     * @return array Filtered paths [path => id]
     */
    private static function filterPathsForPage(array $pathMap, array $prefixes): array
    {
        $filtered = [];

        foreach ($pathMap as $path => $id) {
            foreach ($prefixes as $prefix) {
                if (strpos($path, $prefix) === 0) {
                    // Only include module paths (contain /col and /_)
                    if (strpos($path, '/col') !== false) {
                        $filtered[$path] = $id;
                    }
                    break;
                }
            }
        }

        return $filtered;
    }

    /**
     * Build module info for each path from Registry
     *
     * @param array $pagePaths Paths for this page
     * @param array $skeleton Full skeleton
     * @return array Module infos [path => info]
     */
    private static function buildModuleInfos(array $pagePaths, array $skeleton): array
    {
        $moduleInfos = [];

        foreach ($pagePaths as $path => $id) {
            // Extract module type from path (e.g., "home/hero/col0/heading_0" → "heading")
            $pathParts = explode('/', $path);
            $lastPart = end($pathParts); // e.g., "heading_0"
            $moduleType = preg_replace('/_\d+$/', '', $lastPart); // Remove index

            // Get fields from Registry
            $fields = self::getModuleFields($moduleType);

            // Get module data from skeleton
            $moduleData = self::findModuleInSkeleton($id, $skeleton);

            // Extract role from module attrs
            $role = $moduleData['attrs']['_role'] ?? null;

            // Determine which fields need content
            $contentFields = self::getContentFields($fields);

            $moduleInfos[$path] = [
                'id' => $id,
                'type' => $moduleType,
                'role' => $role,
                'fields' => $contentFields,
                'current_attrs' => $moduleData['attrs'] ?? []
            ];
        }

        return $moduleInfos;
    }

    /**
     * Get module fields from Registry (with caching)
     *
     * @param string $moduleType Module type slug
     * @return array Fields definition
     */
    private static function getModuleFields(string $moduleType): array
    {
        if (isset(self::$fieldsCache[$moduleType])) {
            return self::$fieldsCache[$moduleType];
        }

        $fields = [];

        if (class_exists('\\JessieThemeBuilder\\JTB_Registry')) {
            $module = \JessieThemeBuilder\JTB_Registry::get($moduleType);
            if ($module) {
                $fields = $module->getFields();
            }
        }

        // Fallback to common fields if Registry unavailable
        if (empty($fields)) {
            $fields = self::getFallbackFields($moduleType);
        }

        self::$fieldsCache[$moduleType] = $fields;
        return $fields;
    }

    /**
     * Get fallback fields for common modules
     *
     * @param string $moduleType Module type
     * @return array Fields
     */
    private static function getFallbackFields(string $moduleType): array
    {
        $common = [
            'heading' => [
                'text' => ['type' => 'text', 'label' => 'Heading Text'],
                'level' => ['type' => 'select', 'label' => 'Heading Level', 'default' => 'h2']
            ],
            'text' => [
                'content' => ['type' => 'richtext', 'label' => 'Content']
            ],
            'button' => [
                'text' => ['type' => 'text', 'label' => 'Button Text'],
                'link_url' => ['type' => 'url', 'label' => 'Link URL']
            ],
            'image' => [
                'src' => ['type' => 'upload', 'label' => 'Image'],
                'alt' => ['type' => 'text', 'label' => 'Alt Text']
            ],
            'blurb' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'content' => ['type' => 'richtext', 'label' => 'Content'],
                'font_icon' => ['type' => 'icon', 'label' => 'Icon']
            ],
            'testimonial' => [
                'content' => ['type' => 'textarea', 'label' => 'Testimonial'],
                'author' => ['type' => 'text', 'label' => 'Author Name'],
                'job_title' => ['type' => 'text', 'label' => 'Job Title'],
                'company' => ['type' => 'text', 'label' => 'Company'],
                'portrait_url' => ['type' => 'upload', 'label' => 'Portrait']
            ],
            'team_member' => [
                'name' => ['type' => 'text', 'label' => 'Name'],
                'position' => ['type' => 'text', 'label' => 'Position'],
                'bio' => ['type' => 'textarea', 'label' => 'Bio'],
                'image_url' => ['type' => 'upload', 'label' => 'Photo']
            ],
            'pricing_table' => [
                'title' => ['type' => 'text', 'label' => 'Plan Name'],
                'price' => ['type' => 'text', 'label' => 'Price'],
                'period' => ['type' => 'text', 'label' => 'Period'],
                'features' => ['type' => 'textarea', 'label' => 'Features'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                'button_url' => ['type' => 'url', 'label' => 'Button URL']
            ],
            'cta' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'content' => ['type' => 'richtext', 'label' => 'Content'],
                'button_text' => ['type' => 'text', 'label' => 'Button Text'],
                'button_url' => ['type' => 'url', 'label' => 'Button URL']
            ],
            'site_logo' => [
                'logo' => ['type' => 'upload', 'label' => 'Logo Image'],
                'logo_alt' => ['type' => 'text', 'label' => 'Alt Text']
            ],
            'menu' => [
                // Menu items come from database
            ],
            'social_icons' => [
                'facebook_url' => ['type' => 'url', 'label' => 'Facebook URL'],
                'twitter_url' => ['type' => 'url', 'label' => 'Twitter URL'],
                'instagram_url' => ['type' => 'url', 'label' => 'Instagram URL'],
                'linkedin_url' => ['type' => 'url', 'label' => 'LinkedIn URL']
            ],
            'contact_form' => [
                'title' => ['type' => 'text', 'label' => 'Form Title'],
                'submit_text' => ['type' => 'text', 'label' => 'Submit Button Text'],
                'email_to' => ['type' => 'text', 'label' => 'Email To']
            ],
            'accordion' => [
                // Items are children
            ],
            'accordion_item' => [
                'title' => ['type' => 'text', 'label' => 'Question'],
                'content' => ['type' => 'richtext', 'label' => 'Answer']
            ],
            'map' => [
                'address' => ['type' => 'text', 'label' => 'Address'],
                'zoom' => ['type' => 'number', 'label' => 'Zoom Level', 'default' => 14]
            ]
        ];

        return $common[$moduleType] ?? [];
    }

    /**
     * Get content fields (text-based) from fields definition
     *
     * @param array $fields All fields
     * @return array Content fields only
     */
    private static function getContentFields(array $fields): array
    {
        $contentFields = [];
        $textTypes = ['text', 'textarea', 'richtext', 'url'];

        foreach ($fields as $fieldName => $fieldDef) {
            $type = $fieldDef['type'] ?? 'text';

            if (in_array($type, $textTypes)) {
                $contentFields[$fieldName] = [
                    'type' => $type,
                    'label' => $fieldDef['label'] ?? $fieldName,
                    'default' => $fieldDef['default'] ?? ''
                ];
            }
        }

        return $contentFields;
    }

    /**
     * Find module in skeleton by ID
     *
     * @param string $id Module ID
     * @param array $skeleton Full skeleton
     * @return array|null Module data
     */
    private static function findModuleInSkeleton(string $id, array $skeleton): ?array
    {
        // Search header
        if (!empty($skeleton['header']['sections'])) {
            $found = self::findModuleInSections($id, $skeleton['header']['sections']);
            if ($found) return $found;
        }

        // Search footer
        if (!empty($skeleton['footer']['sections'])) {
            $found = self::findModuleInSections($id, $skeleton['footer']['sections']);
            if ($found) return $found;
        }

        // Search pages
        foreach ($skeleton['pages'] as $pageData) {
            if (!empty($pageData['sections'])) {
                $found = self::findModuleInSections($id, $pageData['sections']);
                if ($found) return $found;
            }
        }

        return null;
    }

    /**
     * Find module in sections array
     *
     * @param string $id Module ID
     * @param array $sections Sections array
     * @return array|null Module data
     */
    private static function findModuleInSections(string $id, array $sections): ?array
    {
        foreach ($sections as $section) {
            if (($section['id'] ?? '') === $id) {
                return $section;
            }

            if (!empty($section['children'])) {
                foreach ($section['children'] as $row) {
                    if (($row['id'] ?? '') === $id) {
                        return $row;
                    }

                    if (!empty($row['children'])) {
                        foreach ($row['children'] as $column) {
                            if (($column['id'] ?? '') === $id) {
                                return $column;
                            }

                            if (!empty($column['children'])) {
                                foreach ($column['children'] as $module) {
                                    if (($module['id'] ?? '') === $id) {
                                        return $module;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Generate content via AI
     *
     * @param array $moduleInfos Module infos to fill
     * @param array $context Context (industry, style, prompt, page)
     * @return array Generated content
     */
    private static function generateContent(array $moduleInfos, array $context): array
    {
        // Build system prompt
        $systemPrompt = self::buildSystemPrompt($context);

        // Build user prompt with modules to fill
        $userPrompt = self::buildUserPrompt($moduleInfos, $context);

        // Call AI
        $ai = JTB_AI_Core::getInstance();
        $response = $ai->query($userPrompt, [
            'system_prompt' => $systemPrompt,
            'max_tokens' => 4000,
            'temperature' => 0.7
        ]);

        if (!$response['ok']) {
            // Fallback to placeholder content
            return self::generateFallbackContent($moduleInfos, $context);
        }

        // Parse AI response
        $content = self::parseAIResponse($response['text'], $moduleInfos);

        return [
            'content' => $content,
            'tokens_used' => $response['tokens_used'] ?? 0
        ];
    }

    /**
     * Build system prompt for content generation
     *
     * @param array $context Context
     * @return string System prompt
     */
    private static function buildSystemPrompt(array $context): string
    {
        $industry = $context['industry'] ?? 'technology';
        $style = $context['style'] ?? 'modern';

        return <<<PROMPT
You are a professional copywriter creating website content.

INDUSTRY: {$industry}
STYLE: {$style}

RULES:
1. Write professional, engaging content appropriate for the industry
2. Use active voice and clear language
3. Match the tone to the visual style (modern = concise, elegant = sophisticated, playful = friendly)
4. For headings: Keep them punchy and benefit-focused (max 8 words)
5. For paragraphs: 2-3 sentences max, focus on value proposition
6. For buttons: 2-4 words, action-oriented (Get Started, Learn More, Contact Us)
7. For URLs: Use realistic but placeholder paths (#contact, /about, /services)
8. NEVER use generic placeholders like "Your Heading Here" or "Lorem ipsum"

OUTPUT FORMAT:
Return a JSON object where:
- Keys are paths (e.g., "home/hero/col0/heading_0")
- Values are objects with field names and content

Example:
{
    "home/hero/col0/heading_0": {
        "text": "Transform Your Business Today",
        "level": "h1"
    },
    "home/hero/col0/text_0": {
        "content": "<p>Discover how our innovative solutions can help your team achieve more in less time.</p>"
    }
}
PROMPT;
    }

    /**
     * Build user prompt with modules to fill
     *
     * @param array $moduleInfos Module infos
     * @param array $context Context
     * @return string User prompt
     */
    private static function buildUserPrompt(array $moduleInfos, array $context): string
    {
        $prompt = $context['prompt'] ?? 'Professional business website';
        $page = $context['page'] ?? 'home';

        $modulesText = "BUSINESS CONTEXT:\n{$prompt}\n\n";
        $modulesText .= "PAGE: {$page}\n\n";
        $modulesText .= "MODULES TO FILL:\n\n";

        foreach ($moduleInfos as $path => $info) {
            $modulesText .= "PATH: {$path}\n";
            $modulesText .= "TYPE: {$info['type']}\n";

            if ($info['role']) {
                $modulesText .= "ROLE: {$info['role']}\n";
            }

            if (!empty($info['fields'])) {
                $modulesText .= "FIELDS:\n";
                foreach ($info['fields'] as $fieldName => $fieldDef) {
                    $type = $fieldDef['type'];
                    $label = $fieldDef['label'];
                    $modulesText .= "  - {$fieldName} ({$type}): {$label}\n";
                }
            }

            $modulesText .= "\n";
        }

        $modulesText .= "\nGenerate content for ALL modules listed above. Return valid JSON.";

        return $modulesText;
    }

    /**
     * Parse AI response to content array
     *
     * @param string $response AI response
     * @param array $moduleInfos Module infos for validation
     * @return array Content [path => attrs]
     */
    private static function parseAIResponse(string $response, array $moduleInfos): array
    {
        // Extract JSON from response
        $json = $response;

        // Try to find JSON block
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $response, $matches)) {
            $json = $matches[1];
        } elseif (preg_match('/\{[\s\S]*\}/', $response, $matches)) {
            $json = $matches[0];
        }

        $parsed = json_decode($json, true);

        if (!is_array($parsed)) {
            return [];
        }

        // Validate and filter to known paths
        $content = [];
        foreach ($moduleInfos as $path => $info) {
            if (isset($parsed[$path])) {
                $content[$path] = $parsed[$path];
            }
        }

        return $content;
    }

    /**
     * Generate fallback content when AI fails
     *
     * @param array $moduleInfos Module infos
     * @param array $context Context
     * @return array Fallback content
     */
    private static function generateFallbackContent(array $moduleInfos, array $context): array
    {
        $content = [];
        $industry = $context['industry'] ?? 'technology';
        $companyName = self::extractCompanyName($context['prompt'] ?? '') ?: 'Our Company';

        foreach ($moduleInfos as $path => $info) {
            $type = $info['type'];
            $role = $info['role'] ?? '';

            $content[$path] = self::generateFallbackForModule($type, $role, $companyName, $industry);
        }

        return [
            'content' => $content,
            'tokens_used' => 0
        ];
    }

    /**
     * Extract company name from prompt
     *
     * @param string $prompt Business prompt
     * @return string|null Company name
     */
    private static function extractCompanyName(string $prompt): ?string
    {
        // Try to find company name patterns
        $patterns = [
            '/\b(?:we are|called|named|introducing)\s+([A-Z][a-zA-Z0-9\s&]+?)(?:\.|,|is|which)/i',
            '/^([A-Z][a-zA-Z0-9\s&]+?)\s+(?:is|provides|offers)/i',
            '/\b([A-Z][a-zA-Z0-9\s&]+?)\s+(?:LLC|Inc|Ltd|Corp)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $prompt, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Generate fallback content for specific module type
     *
     * @param string $type Module type
     * @param string $role Module role
     * @param string $companyName Company name
     * @param string $industry Industry
     * @return array Module attrs
     */
    private static function generateFallbackForModule(string $type, string $role, string $companyName, string $industry): array
    {
        $industryVerb = self::getIndustryVerb($industry);
        $industryNoun = self::getIndustryNoun($industry);

        switch ($type) {
            case 'heading':
                $texts = [
                    'h1_title' => "Transform Your {$industryNoun} Today",
                    'page_title' => "About {$companyName}",
                    'section_title' => "Why Choose {$companyName}",
                    'cta_title' => "Ready to Get Started?",
                    'faq_title' => "Frequently Asked Questions"
                ];
                return [
                    'text' => $texts[$role] ?? "Welcome to {$companyName}",
                    'level' => $role === 'h1_title' ? 'h1' : 'h2'
                ];

            case 'text':
                $texts = [
                    'subheadline' => "<p>Discover how {$companyName} can help you {$industryVerb} with our innovative solutions.</p>",
                    'about_text' => "<p>{$companyName} has been helping businesses {$industryVerb} for years. Our team of experts is dedicated to delivering exceptional results.</p>",
                    'cta_text' => "<p>Join thousands of satisfied customers who have already transformed their {$industryNoun}.</p>",
                    'contact_info' => "<p>Get in touch with our team. We're here to help you succeed.</p>"
                ];
                return [
                    'content' => $texts[$role] ?? "<p>{$companyName} provides professional {$industryNoun} services tailored to your needs.</p>"
                ];

            case 'button':
                $buttons = [
                    'primary_cta' => ['text' => 'Get Started', 'link_url' => '#contact'],
                    'secondary_cta' => ['text' => 'Learn More', 'link_url' => '/about'],
                    'cta_button' => ['text' => 'Contact Us', 'link_url' => '/contact']
                ];
                return $buttons[$role] ?? ['text' => 'Learn More', 'link_url' => '#'];

            case 'blurb':
                return [
                    'title' => "Expert {$industryNoun}",
                    'content' => "<p>Professional services designed to help your business grow and succeed.</p>",
                    'font_icon' => 'check-circle'
                ];

            case 'testimonial':
                return [
                    'content' => "Working with {$companyName} has transformed our business. Their expertise in {$industryNoun} is unmatched.",
                    'author' => 'John Smith',
                    'job_title' => 'CEO',
                    'company' => 'ABC Company'
                ];

            case 'team_member':
                return [
                    'name' => 'Team Member',
                    'position' => 'Expert',
                    'bio' => "Dedicated professional with years of experience in {$industryNoun}."
                ];

            case 'pricing_table':
                return [
                    'title' => 'Professional',
                    'price' => '$99',
                    'period' => '/month',
                    'features' => "Feature 1\nFeature 2\nFeature 3\nPriority Support",
                    'button_text' => 'Choose Plan',
                    'button_url' => '#signup'
                ];

            case 'cta':
                return [
                    'title' => 'Ready to Transform Your Business?',
                    'content' => "<p>Take the first step towards success with {$companyName}.</p>",
                    'button_text' => 'Get Started Today',
                    'button_url' => '#contact'
                ];

            case 'contact_form':
                return [
                    'title' => 'Contact Us',
                    'submit_text' => 'Send Message',
                    'email_to' => 'contact@example.com'
                ];

            case 'accordion_item':
                return [
                    'title' => 'How can I get started?',
                    'content' => "<p>Getting started is easy! Simply contact our team and we'll guide you through the process.</p>"
                ];

            case 'social_icons':
                return [
                    'facebook_url' => 'https://facebook.com/',
                    'twitter_url' => 'https://twitter.com/',
                    'linkedin_url' => 'https://linkedin.com/'
                ];

            default:
                return [];
        }
    }

    /**
     * Get industry-specific verb
     *
     * @param string $industry Industry
     * @return string Verb
     */
    private static function getIndustryVerb(string $industry): string
    {
        $verbs = [
            'technology' => 'innovate and scale',
            'healthcare' => 'improve patient care',
            'legal' => 'protect your interests',
            'finance' => 'grow your wealth',
            'education' => 'achieve your goals',
            'restaurant' => 'delight your customers',
            'fitness' => 'transform your health',
            'realestate' => 'find your dream home',
            'agency' => 'elevate your brand',
            'ecommerce' => 'boost your sales'
        ];

        return $verbs[$industry] ?? 'achieve your goals';
    }

    /**
     * Get industry-specific noun
     *
     * @param string $industry Industry
     * @return string Noun
     */
    private static function getIndustryNoun(string $industry): string
    {
        $nouns = [
            'technology' => 'business',
            'healthcare' => 'practice',
            'legal' => 'case',
            'finance' => 'portfolio',
            'education' => 'learning',
            'restaurant' => 'dining experience',
            'fitness' => 'fitness journey',
            'realestate' => 'property',
            'agency' => 'brand',
            'ecommerce' => 'store'
        ];

        return $nouns[$industry] ?? 'business';
    }
}
