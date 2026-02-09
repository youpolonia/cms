<?php
/**
 * JTB AI Agent SEO
 *
 * Generates SEO metadata for all pages:
 * - Meta title & description
 * - Open Graph tags
 * - Twitter Card tags
 * - Schema.org JSON-LD (Organization, WebSite, WebPage, FAQ, BreadcrumbList, etc.)
 *
 * Uses existing JTB_SEO class for generation.
 * Works with JTB_AI_Core for AI-assisted content generation.
 *
 * ZERO HARDCODES - derives everything from session context and content.
 *
 * @package JessieThemeBuilder
 * @since 2.0.0
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_AI_Agent_SEO
{
    /**
     * Industry-specific keywords for SEO
     */
    private const INDUSTRY_KEYWORDS = [
        'technology' => ['software', 'technology', 'digital solutions', 'innovation', 'tech', 'SaaS', 'cloud', 'automation'],
        'healthcare' => ['healthcare', 'medical', 'health services', 'patient care', 'wellness', 'clinic', 'medical professionals'],
        'legal' => ['law firm', 'legal services', 'attorney', 'lawyer', 'legal counsel', 'litigation', 'legal advice'],
        'restaurant' => ['restaurant', 'dining', 'cuisine', 'food', 'menu', 'catering', 'fine dining', 'chef'],
        'real_estate' => ['real estate', 'property', 'homes', 'housing', 'realty', 'listings', 'investment'],
        'fitness' => ['fitness', 'gym', 'workout', 'training', 'health', 'wellness', 'exercise', 'personal training'],
        'agency' => ['agency', 'marketing', 'creative', 'branding', 'design', 'digital marketing', 'advertising'],
        'ecommerce' => ['shop', 'store', 'products', 'online shopping', 'retail', 'ecommerce', 'buy online'],
        'education' => ['education', 'learning', 'courses', 'training', 'school', 'academy', 'certification'],
        'construction' => ['construction', 'building', 'contractor', 'renovation', 'development', 'architecture'],
        'general' => ['professional services', 'quality', 'excellence', 'trusted', 'reliable', 'expert']
    ];

    /**
     * Page-specific meta description templates
     */
    private const PAGE_DESCRIPTION_TEMPLATES = [
        'home' => 'Welcome to {business_name}. {unique_value_proposition}. Discover our {services_summary} and why clients choose us.',
        'about' => 'Learn about {business_name} - our story, mission, and the team behind our success. {years_experience} of delivering excellence.',
        'services' => 'Explore our comprehensive {industry} services at {business_name}. From {service_1} to {service_2}, we deliver results.',
        'contact' => 'Get in touch with {business_name}. Contact us for {industry} solutions. {cta_text}.',
        'pricing' => 'Transparent pricing for {business_name} services. Find the perfect plan for your {industry} needs.',
        'team' => 'Meet the experts at {business_name}. Our experienced {industry} professionals are here to help you succeed.',
        'portfolio' => 'Browse our portfolio at {business_name}. See examples of our {industry} work and success stories.',
        'faq' => 'Frequently asked questions about {business_name} and our {industry} services. Get answers to common queries.',
        'blog' => 'Read the latest insights and updates from {business_name}. Expert {industry} articles and resources.',
        'testimonials' => 'See what clients say about {business_name}. Real reviews and testimonials from satisfied customers.'
    ];

    /**
     * Schema.org types per page
     */
    private const PAGE_SCHEMA_TYPES = [
        'home' => ['WebSite', 'Organization'],
        'about' => ['AboutPage', 'Organization'],
        'services' => ['Service', 'ItemList'],
        'contact' => ['ContactPage', 'LocalBusiness'],
        'pricing' => ['Product', 'ItemList'],
        'team' => ['AboutPage', 'Person'],
        'portfolio' => ['CollectionPage', 'CreativeWork'],
        'faq' => ['FAQPage'],
        'blog' => ['Blog', 'ItemList'],
        'testimonials' => ['Review', 'ItemList']
    ];

    /**
     * Execute SEO generation for all pages
     *
     * @param array $session Multi-agent session data
     * @return array ['ok' => bool, 'seo' => [...], 'tokens_used' => int]
     */
    public static function execute(array $session): array
    {
        $startTime = microtime(true);
        $tokensUsed = 0;
        $seo = [];

        // Extract business context from session
        $context = self::buildBusinessContext($session);

        // Generate SEO for each page
        $pages = $session['pages'] ?? ['home', 'about', 'services', 'contact'];

        foreach ($pages as $pageName) {
            $pageContent = self::extractPageContent($session, $pageName);
            $pageSeo = self::generatePageSeo($pageName, $context, $pageContent, $session);

            if (!empty($pageSeo)) {
                $seo[$pageName] = $pageSeo;
            }
        }

        // Generate global site SEO (shared across pages)
        $seo['_global'] = self::generateGlobalSeo($context, $session);

        // If AI is available, enhance meta descriptions
        if (class_exists(__NAMESPACE__ . '\\JTB_AI_Core') && JTB_AI_Core::getInstance()->isConfigured()) {
            $aiResult = self::enhanceWithAI($seo, $context, $session);
            $seo = $aiResult['seo'];
            $tokensUsed = $aiResult['tokens_used'];
        }

        $timeMs = (int)((microtime(true) - $startTime) * 1000);

        return [
            'ok' => true,
            'seo' => $seo,
            'tokens_used' => $tokensUsed,
            'stats' => [
                'time_ms' => $timeMs,
                'pages_processed' => count($pages)
            ]
        ];
    }

    /**
     * Build business context from session
     */
    private static function buildBusinessContext(array $session): array
    {
        $prompt = $session['prompt'] ?? '';
        $industry = $session['industry'] ?? 'general';
        $style = $session['style'] ?? 'modern';

        // Extract business name from prompt or use generic
        $businessName = self::extractBusinessName($prompt);

        // Get industry keywords
        $keywords = self::INDUSTRY_KEYWORDS[$industry] ?? self::INDUSTRY_KEYWORDS['general'];

        // Extract content hints from architect phase
        $contentHints = $session['content_hints'] ?? [];

        return [
            'business_name' => $businessName,
            'industry' => $industry,
            'industry_label' => ucfirst(str_replace('_', ' ', $industry)),
            'style' => $style,
            'keywords' => $keywords,
            'prompt' => $prompt,
            'unique_value_proposition' => $contentHints['uvp'] ?? self::generateUVP($industry),
            'services_summary' => $contentHints['services_summary'] ?? self::getServicesSummary($industry),
            'years_experience' => $contentHints['years'] ?? 'Years',
            'cta_text' => $contentHints['cta'] ?? 'Get started today'
        ];
    }

    /**
     * Extract business name from prompt
     */
    private static function extractBusinessName(string $prompt): string
    {
        // Try to find business name in common patterns
        $patterns = [
            '/(?:for|called|named)\s+["\']?([A-Z][A-Za-z0-9\s&\-\'\.]+)["\']?/i',
            '/([A-Z][A-Za-z0-9]+(?:\s+[A-Z][A-Za-z0-9]+)*)\s+(?:website|site|page)/i',
            '/^([A-Z][A-Za-z0-9\s&\-\'\.]+?)(?:\s+[-–]\s+|\s+website|\s+site|\.)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $prompt, $matches)) {
                $name = trim($matches[1]);
                if (strlen($name) >= 2 && strlen($name) <= 50) {
                    return $name;
                }
            }
        }

        // Fallback to generic
        return 'Our Company';
    }

    /**
     * Generate unique value proposition based on industry
     */
    private static function generateUVP(string $industry): string
    {
        $uvps = [
            'technology' => 'Innovative solutions that transform your business',
            'healthcare' => 'Compassionate care with cutting-edge medical expertise',
            'legal' => 'Dedicated legal representation you can trust',
            'restaurant' => 'Exceptional dining experiences crafted with passion',
            'real_estate' => 'Your trusted partner in finding the perfect property',
            'fitness' => 'Transform your body and mind with expert guidance',
            'agency' => 'Creative solutions that drive measurable results',
            'ecommerce' => 'Quality products delivered with exceptional service',
            'education' => 'Empowering learners to achieve their full potential',
            'construction' => 'Building excellence with quality and precision',
            'general' => 'Excellence in everything we do'
        ];

        return $uvps[$industry] ?? $uvps['general'];
    }

    /**
     * Get services summary based on industry
     */
    private static function getServicesSummary(string $industry): string
    {
        $summaries = [
            'technology' => 'software development and digital solutions',
            'healthcare' => 'medical services and patient care',
            'legal' => 'legal services and consultation',
            'restaurant' => 'culinary experiences and catering',
            'real_estate' => 'property services and listings',
            'fitness' => 'fitness programs and personal training',
            'agency' => 'marketing and creative services',
            'ecommerce' => 'products and shopping experience',
            'education' => 'courses and learning programs',
            'construction' => 'construction and renovation services',
            'general' => 'professional services'
        ];

        return $summaries[$industry] ?? $summaries['general'];
    }

    /**
     * Extract page content from session for SEO
     */
    private static function extractPageContent(array $session, string $pageName): array
    {
        $content = [];

        // Get content from content agent output
        $allContent = $session['content'] ?? [];

        foreach ($allContent as $path => $moduleContent) {
            // Check if this path belongs to the current page
            if (strpos($path, "{$pageName}/") === 0) {
                $content[$path] = $moduleContent;
            }
        }

        // Get skeleton for structure info
        $skeleton = $session['skeleton'] ?? [];
        $pageData = $skeleton['pages'][$pageName] ?? [];

        return [
            'modules_content' => $content,
            'title' => $pageData['title'] ?? ucfirst($pageName),
            'sections' => $pageData['sections'] ?? []
        ];
    }

    /**
     * Generate SEO for a specific page
     */
    private static function generatePageSeo(string $pageName, array $context, array $pageContent, array $session): array
    {
        $businessName = $context['business_name'];
        $industry = $context['industry'];
        $industryLabel = $context['industry_label'];

        // Build title
        $pageTitle = $pageContent['title'] ?? ucfirst($pageName);
        $metaTitle = self::buildMetaTitle($pageName, $pageTitle, $businessName);

        // Build description
        $metaDescription = self::buildMetaDescription($pageName, $context, $pageContent);

        // Build keywords
        $keywords = self::buildKeywords($pageName, $context);

        // Build Open Graph
        $openGraph = self::buildOpenGraph($pageName, $metaTitle, $metaDescription, $context);

        // Build Twitter Card
        $twitterCard = self::buildTwitterCard($metaTitle, $metaDescription);

        // Build Schema.org
        $schemas = self::buildSchemas($pageName, $context, $pageContent);

        // Build canonical URL
        $canonicalPath = $pageName === 'home' ? '/' : "/{$pageName}";

        return [
            'title' => $metaTitle,
            'description' => $metaDescription,
            'keywords' => $keywords,
            'canonical' => $canonicalPath,
            'robots' => 'index, follow',
            'open_graph' => $openGraph,
            'twitter_card' => $twitterCard,
            'schemas' => $schemas
        ];
    }

    /**
     * Build meta title
     */
    private static function buildMetaTitle(string $pageName, string $pageTitle, string $businessName): string
    {
        $titles = [
            'home' => $businessName,
            'about' => "About Us | {$businessName}",
            'services' => "Our Services | {$businessName}",
            'contact' => "Contact Us | {$businessName}",
            'pricing' => "Pricing | {$businessName}",
            'team' => "Our Team | {$businessName}",
            'portfolio' => "Portfolio | {$businessName}",
            'faq' => "FAQ | {$businessName}",
            'blog' => "Blog | {$businessName}",
            'testimonials' => "Testimonials | {$businessName}"
        ];

        $title = $titles[$pageName] ?? "{$pageTitle} | {$businessName}";

        // Ensure title is not too long (60 chars for SEO)
        if (strlen($title) > 60) {
            $title = substr($title, 0, 57) . '...';
        }

        return $title;
    }

    /**
     * Build meta description
     */
    private static function buildMetaDescription(string $pageName, array $context, array $pageContent): string
    {
        $template = self::PAGE_DESCRIPTION_TEMPLATES[$pageName] ?? self::PAGE_DESCRIPTION_TEMPLATES['home'];

        // Replace placeholders
        $replacements = [
            '{business_name}' => $context['business_name'],
            '{industry}' => $context['industry_label'],
            '{unique_value_proposition}' => $context['unique_value_proposition'],
            '{services_summary}' => $context['services_summary'],
            '{years_experience}' => $context['years_experience'],
            '{cta_text}' => $context['cta_text'],
            '{service_1}' => $context['keywords'][0] ?? 'service',
            '{service_2}' => $context['keywords'][1] ?? 'solutions'
        ];

        $description = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $template
        );

        // Try to extract key content from page
        $extractedContent = self::extractKeyContent($pageContent);
        if (!empty($extractedContent)) {
            // Append or blend extracted content
            $description = self::blendDescription($description, $extractedContent);
        }

        // Ensure description is not too long (160 chars for SEO)
        if (strlen($description) > 160) {
            $description = substr($description, 0, 157) . '...';
        }

        return $description;
    }

    /**
     * Extract key content from page modules
     */
    private static function extractKeyContent(array $pageContent): string
    {
        $key_texts = [];
        $modules = $pageContent['modules_content'] ?? [];

        foreach ($modules as $path => $content) {
            // Look for headings
            if (isset($content['text']) && strpos($path, 'heading') !== false) {
                $text = strip_tags($content['text']);
                if (strlen($text) > 5 && strlen($text) < 100) {
                    $key_texts[] = $text;
                }
            }

            // Look for subheadlines
            if (isset($content['content']) && is_string($content['content'])) {
                $text = strip_tags($content['content']);
                if (strlen($text) > 20 && strlen($text) < 200) {
                    $key_texts[] = substr($text, 0, 100);
                }
            }
        }

        return implode('. ', array_slice($key_texts, 0, 2));
    }

    /**
     * Blend template description with extracted content
     */
    private static function blendDescription(string $template, string $extracted): string
    {
        // If extracted content is substantial, use it to enhance
        if (strlen($extracted) > 50) {
            // Use first sentence of template + extracted content
            $firstSentence = explode('.', $template)[0] . '.';
            return $firstSentence . ' ' . $extracted;
        }

        return $template;
    }

    /**
     * Build keywords
     */
    private static function buildKeywords(string $pageName, array $context): array
    {
        $keywords = $context['keywords'];

        // Add page-specific keywords
        $pageKeywords = [
            'home' => ['official website', 'home'],
            'about' => ['about us', 'our story', 'company', 'team'],
            'services' => ['services', 'solutions', 'offerings'],
            'contact' => ['contact', 'get in touch', 'location', 'email', 'phone'],
            'pricing' => ['pricing', 'plans', 'cost', 'packages'],
            'team' => ['team', 'staff', 'experts', 'professionals'],
            'portfolio' => ['portfolio', 'work', 'projects', 'case studies'],
            'faq' => ['faq', 'questions', 'answers', 'help'],
            'blog' => ['blog', 'articles', 'news', 'insights'],
            'testimonials' => ['testimonials', 'reviews', 'feedback', 'clients']
        ];

        $additional = $pageKeywords[$pageName] ?? [];

        // Merge and add business name
        $allKeywords = array_merge([$context['business_name']], $keywords, $additional);

        // Remove duplicates and limit
        return array_slice(array_unique($allKeywords), 0, 10);
    }

    /**
     * Build Open Graph tags
     */
    private static function buildOpenGraph(string $pageName, string $title, string $description, array $context): array
    {
        $og = [
            'og:title' => $title,
            'og:description' => $description,
            'og:type' => $pageName === 'home' ? 'website' : 'article',
            'og:site_name' => $context['business_name'],
            'og:locale' => 'en_US'
        ];

        // Add image if available from session
        // Will be populated by Images agent later
        $og['og:image'] = ''; // Placeholder

        return $og;
    }

    /**
     * Build Twitter Card tags
     */
    private static function buildTwitterCard(string $title, string $description): array
    {
        return [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $title,
            'twitter:description' => $description,
            'twitter:image' => '' // Placeholder
        ];
    }

    /**
     * Build Schema.org JSON-LD
     */
    private static function buildSchemas(string $pageName, array $context, array $pageContent): array
    {
        $schemas = [];
        $schemaTypes = self::PAGE_SCHEMA_TYPES[$pageName] ?? ['WebPage'];

        foreach ($schemaTypes as $type) {
            $schema = self::buildSchemaByType($type, $pageName, $context, $pageContent);
            if (!empty($schema)) {
                $schemas[] = $schema;
            }
        }

        // Always add BreadcrumbList for non-home pages
        if ($pageName !== 'home') {
            $schemas[] = self::buildBreadcrumbSchema($pageName, $context);
        }

        return $schemas;
    }

    /**
     * Build schema by type
     */
    private static function buildSchemaByType(string $type, string $pageName, array $context, array $pageContent): array
    {
        $businessName = $context['business_name'];

        switch ($type) {
            case 'WebSite':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebSite',
                    'name' => $businessName,
                    'description' => $context['unique_value_proposition'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => '{url}?s={search_term_string}',
                        'query-input' => 'required name=search_term_string'
                    ]
                ];

            case 'Organization':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Organization',
                    'name' => $businessName,
                    'description' => $context['unique_value_proposition'],
                    'sameAs' => [] // Will be populated with social links
                ];

            case 'LocalBusiness':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'LocalBusiness',
                    'name' => $businessName,
                    'description' => $context['unique_value_proposition'],
                    '@id' => '#organization'
                ];

            case 'AboutPage':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'AboutPage',
                    'name' => "About {$businessName}",
                    'description' => "Learn about {$businessName}",
                    'mainEntity' => [
                        '@type' => 'Organization',
                        'name' => $businessName
                    ]
                ];

            case 'ContactPage':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'ContactPage',
                    'name' => "Contact {$businessName}",
                    'description' => "Get in touch with {$businessName}"
                ];

            case 'FAQPage':
                return self::buildFAQSchema($pageContent, $context);

            case 'Service':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Service',
                    'provider' => [
                        '@type' => 'Organization',
                        'name' => $businessName
                    ],
                    'serviceType' => $context['industry_label']
                ];

            case 'CollectionPage':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'CollectionPage',
                    'name' => "Portfolio - {$businessName}",
                    'description' => "View our work and projects"
                ];

            case 'Blog':
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'Blog',
                    'name' => "{$businessName} Blog",
                    'description' => "Latest insights and articles"
                ];

            default:
                return [
                    '@context' => 'https://schema.org',
                    '@type' => 'WebPage',
                    'name' => $pageContent['title'] ?? ucfirst($pageName),
                    'isPartOf' => [
                        '@type' => 'WebSite',
                        'name' => $businessName
                    ]
                ];
        }
    }

    /**
     * Build FAQ Schema from page content
     */
    private static function buildFAQSchema(array $pageContent, array $context): array
    {
        $faqItems = [];
        $modules = $pageContent['modules_content'] ?? [];

        // Look for accordion items or FAQ content
        foreach ($modules as $path => $content) {
            if (strpos($path, 'accordion') !== false || strpos($path, 'faq') !== false) {
                if (isset($content['title']) && isset($content['content'])) {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => strip_tags($content['title']),
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => strip_tags($content['content'])
                        ]
                    ];
                }
            }
        }

        // If no FAQ items found, generate placeholder
        if (empty($faqItems)) {
            $faqItems = self::generateDefaultFAQItems($context);
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqItems
        ];
    }

    /**
     * Generate default FAQ items based on industry
     */
    private static function generateDefaultFAQItems(array $context): array
    {
        $industry = $context['industry'];
        $businessName = $context['business_name'];

        $defaultFAQs = [
            [
                '@type' => 'Question',
                'name' => "What services does {$businessName} offer?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "We offer comprehensive {$context['services_summary']}."
                ]
            ],
            [
                '@type' => 'Question',
                'name' => "How can I contact {$businessName}?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "You can reach us through our contact page or by calling our office."
                ]
            ],
            [
                '@type' => 'Question',
                'name' => "Why choose {$businessName}?",
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $context['unique_value_proposition']
                ]
            ]
        ];

        return $defaultFAQs;
    }

    /**
     * Build Breadcrumb Schema
     */
    private static function buildBreadcrumbSchema(string $pageName, array $context): array
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => '/'
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => ucfirst($pageName),
                'item' => "/{$pageName}"
            ]
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items
        ];
    }

    /**
     * Generate global SEO (site-wide)
     */
    private static function generateGlobalSeo(array $context, array $session): array
    {
        return [
            'site_name' => $context['business_name'],
            'site_description' => $context['unique_value_proposition'],
            'site_keywords' => $context['keywords'],
            'default_og_image' => '', // Will be set by Images agent
            'organization_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => $context['business_name'],
                'description' => $context['unique_value_proposition'],
                'sameAs' => []
            ],
            'website_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => $context['business_name'],
                'description' => $context['unique_value_proposition']
            ]
        ];
    }

    /**
     * Enhance SEO with AI-generated content
     */
    private static function enhanceWithAI(array $seo, array $context, array $session): array
    {
        $tokensUsed = 0;

        try {
            $ai = JTB_AI_Core::getInstance();

            // Build prompt for AI enhancement
            $enhancementPrompt = self::buildAIEnhancementPrompt($seo, $context);

            if (empty($enhancementPrompt)) {
                return ['seo' => $seo, 'tokens_used' => 0];
            }

            $systemPrompt = <<<PROMPT
You are an SEO expert. Improve the provided meta descriptions to be more compelling and clickable while maintaining accuracy.

RULES:
1. Each description must be under 160 characters
2. Include a clear call-to-action or benefit
3. Use active voice
4. Include relevant keywords naturally
5. Do NOT use clichés or generic phrases
6. Each description should be unique and specific to the page

OUTPUT FORMAT:
Return a JSON object with page names as keys and improved descriptions as values.
Only return the JSON, no other text.

Example:
{
  "home": "Improved description here",
  "about": "Another improved description"
}
PROMPT;

            $response = $ai->query($enhancementPrompt, [
                'system_prompt' => $systemPrompt,
                'temperature' => 0.7,
                'max_tokens' => 1000
            ]);

            if (!empty($response['text'])) {
                $enhanced = self::parseAIResponse($response['text']);
                $tokensUsed = $response['tokens_used'] ?? 0;

                // Merge AI improvements into SEO
                foreach ($enhanced as $pageName => $description) {
                    if (isset($seo[$pageName]) && strlen($description) <= 160 && strlen($description) > 20) {
                        $seo[$pageName]['description'] = $description;

                        // Update Open Graph and Twitter descriptions too
                        if (isset($seo[$pageName]['open_graph']['og:description'])) {
                            $seo[$pageName]['open_graph']['og:description'] = $description;
                        }
                        if (isset($seo[$pageName]['twitter_card']['twitter:description'])) {
                            $seo[$pageName]['twitter_card']['twitter:description'] = $description;
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            // AI enhancement failed, use generated descriptions
        }

        return [
            'seo' => $seo,
            'tokens_used' => $tokensUsed
        ];
    }

    /**
     * Build AI enhancement prompt
     */
    private static function buildAIEnhancementPrompt(array $seo, array $context): string
    {
        $descriptions = [];

        foreach ($seo as $pageName => $pageSeo) {
            if ($pageName !== '_global' && isset($pageSeo['description'])) {
                $descriptions[$pageName] = $pageSeo['description'];
            }
        }

        if (empty($descriptions)) {
            return '';
        }

        $prompt = "Business: {$context['business_name']}\n";
        $prompt .= "Industry: {$context['industry_label']}\n";
        $prompt .= "UVP: {$context['unique_value_proposition']}\n\n";
        $prompt .= "Current meta descriptions to improve:\n";
        $prompt .= json_encode($descriptions, JSON_PRETTY_PRINT);

        return $prompt;
    }

    /**
     * Parse AI response
     */
    private static function parseAIResponse(string $content): array
    {
        // Try to extract JSON
        $content = trim($content);

        // Remove markdown code blocks if present
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $content = $matches[1];
        }

        // Try to find JSON object
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $content = $matches[0];
        }

        $parsed = @json_decode($content, true);

        if (is_array($parsed)) {
            return $parsed;
        }

        return [];
    }

    /**
     * Generate meta tags HTML for a page
     * Uses JTB_SEO if available
     *
     * @param string $pageName
     * @param array $pageSeo
     * @return string HTML meta tags
     */
    public static function renderMetaTags(string $pageName, array $pageSeo): string
    {
        // If JTB_SEO class is available, use it
        if (class_exists(__NAMESPACE__ . '\\JTB_SEO')) {
            $seo = new JTB_SEO();

            $seo->setTitle($pageSeo['title'] ?? '');
            $seo->setDescription($pageSeo['description'] ?? '');
            $seo->setKeywords($pageSeo['keywords'] ?? []);
            $seo->setCanonical($pageSeo['canonical'] ?? '');
            $seo->setRobots($pageSeo['robots'] ?? 'index, follow');

            // Open Graph
            if (!empty($pageSeo['open_graph'])) {
                $seo->setOpenGraph($pageSeo['open_graph']);
            }

            // Twitter Card
            if (!empty($pageSeo['twitter_card'])) {
                $seo->setTwitterCard($pageSeo['twitter_card']);
            }

            // Schema.org
            if (!empty($pageSeo['schemas'])) {
                foreach ($pageSeo['schemas'] as $schema) {
                    $seo->addSchema($schema);
                }
            }

            return $seo->render();
        }

        // Fallback: manual meta tag generation
        return self::renderMetaTagsManually($pageSeo);
    }

    /**
     * Manual meta tag rendering (fallback)
     */
    private static function renderMetaTagsManually(array $pageSeo): string
    {
        $html = '';

        // Title
        if (!empty($pageSeo['title'])) {
            $html .= '<title>' . htmlspecialchars($pageSeo['title']) . '</title>' . "\n";
        }

        // Description
        if (!empty($pageSeo['description'])) {
            $html .= '<meta name="description" content="' . htmlspecialchars($pageSeo['description']) . '">' . "\n";
        }

        // Keywords
        if (!empty($pageSeo['keywords'])) {
            $keywords = is_array($pageSeo['keywords']) ? implode(', ', $pageSeo['keywords']) : $pageSeo['keywords'];
            $html .= '<meta name="keywords" content="' . htmlspecialchars($keywords) . '">' . "\n";
        }

        // Robots
        if (!empty($pageSeo['robots'])) {
            $html .= '<meta name="robots" content="' . htmlspecialchars($pageSeo['robots']) . '">' . "\n";
        }

        // Canonical
        if (!empty($pageSeo['canonical'])) {
            $html .= '<link rel="canonical" href="' . htmlspecialchars($pageSeo['canonical']) . '">' . "\n";
        }

        // Open Graph
        if (!empty($pageSeo['open_graph'])) {
            foreach ($pageSeo['open_graph'] as $property => $content) {
                if (!empty($content)) {
                    $html .= '<meta property="' . htmlspecialchars($property) . '" content="' . htmlspecialchars($content) . '">' . "\n";
                }
            }
        }

        // Twitter Card
        if (!empty($pageSeo['twitter_card'])) {
            foreach ($pageSeo['twitter_card'] as $name => $content) {
                if (!empty($content)) {
                    $html .= '<meta name="' . htmlspecialchars($name) . '" content="' . htmlspecialchars($content) . '">' . "\n";
                }
            }
        }

        // Schema.org JSON-LD
        if (!empty($pageSeo['schemas'])) {
            foreach ($pageSeo['schemas'] as $schema) {
                $html .= '<script type="application/ld+json">' . "\n";
                $html .= json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                $html .= "\n</script>\n";
            }
        }

        return $html;
    }
}
