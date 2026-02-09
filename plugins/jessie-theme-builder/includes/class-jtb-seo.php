<?php
/**
 * JTB SEO Engine
 * Complete SEO management for AI-generated pages
 *
 * Features:
 * - Meta tags (title, description, robots)
 * - Open Graph tags
 * - Twitter Card tags
 * - Schema.org structured data (JSON-LD)
 * - Canonical URLs
 * - Sitemap generation helpers
 *
 * @package JessieThemeBuilder
 * @since 1.0.0
 * @date 2026-02-04
 */

namespace JessieThemeBuilder;

defined('CMS_ROOT') or die('Direct access not allowed');

class JTB_SEO
{
    private static array $meta = [];
    private static array $schemas = [];
    private static ?string $canonical = null;
    private static array $alternates = [];

    /**
     * Initialize SEO for current page
     * Call this early in page rendering
     */
    public static function init(): void
    {
        self::$meta = [];
        self::$schemas = [];
        self::$canonical = null;
        self::$alternates = [];
    }

    /**
     * Set meta tag
     *
     * @param string $name Meta name or property
     * @param string $content Meta content
     * @param string $type 'name' or 'property'
     */
    public static function setMeta(string $name, string $content, string $type = 'name'): void
    {
        self::$meta[$name] = [
            'type' => $type,
            'content' => $content
        ];
    }

    /**
     * Set page title
     */
    public static function setTitle(string $title, ?string $siteName = null): void
    {
        if ($siteName) {
            self::$meta['_title'] = ['content' => $title . ' | ' . $siteName];
        } else {
            self::$meta['_title'] = ['content' => $title];
        }
    }

    /**
     * Set meta description
     */
    public static function setDescription(string $description): void
    {
        // Truncate to 160 chars for optimal SEO
        $description = self::truncate($description, 160);
        self::setMeta('description', $description);
        self::setMeta('og:description', $description, 'property');
        self::setMeta('twitter:description', $description);
    }

    /**
     * Set canonical URL
     */
    public static function setCanonical(string $url): void
    {
        self::$canonical = $url;
        self::setMeta('og:url', $url, 'property');
    }

    /**
     * Set Open Graph tags
     */
    public static function setOpenGraph(array $data): void
    {
        $defaults = [
            'type' => 'website',
            'locale' => 'pl_PL'
        ];

        $data = array_merge($defaults, $data);

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                self::setMeta('og:' . $key, $value, 'property');
            }
        }
    }

    /**
     * Set Twitter Card tags
     */
    public static function setTwitterCard(array $data): void
    {
        $defaults = [
            'card' => 'summary_large_image'
        ];

        $data = array_merge($defaults, $data);

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                self::setMeta('twitter:' . $key, $value);
            }
        }
    }

    /**
     * Set robots directives
     */
    public static function setRobots(array $directives): void
    {
        self::setMeta('robots', implode(', ', $directives));
    }

    /**
     * Add alternate language link
     */
    public static function addAlternate(string $hreflang, string $url): void
    {
        self::$alternates[$hreflang] = $url;
    }

    /**
     * Add Schema.org structured data
     *
     * @param array $schema JSON-LD schema data
     */
    public static function addSchema(array $schema): void
    {
        // Ensure @context exists
        if (!isset($schema['@context'])) {
            $schema['@context'] = 'https://schema.org';
        }

        self::$schemas[] = $schema;
    }

    /**
     * Generate Website schema
     */
    public static function addWebsiteSchema(array $data = []): void
    {
        $siteName = $data['name'] ?? JTB_Dynamic_Context::getSiteTitle();
        $siteUrl = $data['url'] ?? self::getSiteUrl();

        $schema = [
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $siteUrl
        ];

        // Add search action if search is enabled
        if (!empty($data['search_url'])) {
            $schema['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $data['search_url'] . '?q={search_term_string}'
                ],
                'query-input' => 'required name=search_term_string'
            ];
        }

        self::addSchema($schema);
    }

    /**
     * Generate Organization schema
     */
    public static function addOrganizationSchema(array $data = []): void
    {
        $schema = [
            '@type' => 'Organization',
            'name' => $data['name'] ?? JTB_Dynamic_Context::getSiteTitle(),
            'url' => $data['url'] ?? self::getSiteUrl()
        ];

        if (!empty($data['logo'])) {
            $schema['logo'] = $data['logo'];
        }

        if (!empty($data['social'])) {
            $schema['sameAs'] = array_values($data['social']);
        }

        if (!empty($data['contact'])) {
            $schema['contactPoint'] = [
                '@type' => 'ContactPoint',
                'telephone' => $data['contact']['phone'] ?? '',
                'email' => $data['contact']['email'] ?? '',
                'contactType' => 'customer service'
            ];
        }

        self::addSchema($schema);
    }

    /**
     * Generate Article schema (for blog posts)
     */
    public static function addArticleSchema(array $data): void
    {
        $schema = [
            '@type' => 'Article',
            'headline' => self::truncate($data['title'] ?? '', 110),
            'datePublished' => $data['published'] ?? date('c'),
            'dateModified' => $data['modified'] ?? $data['published'] ?? date('c')
        ];

        if (!empty($data['image'])) {
            $schema['image'] = $data['image'];
        }

        if (!empty($data['author'])) {
            $schema['author'] = [
                '@type' => 'Person',
                'name' => $data['author']['name'] ?? $data['author'],
                'url' => $data['author']['url'] ?? null
            ];
        }

        if (!empty($data['publisher'])) {
            $schema['publisher'] = [
                '@type' => 'Organization',
                'name' => $data['publisher']['name'] ?? $data['publisher'],
                'logo' => !empty($data['publisher']['logo']) ? [
                    '@type' => 'ImageObject',
                    'url' => $data['publisher']['logo']
                ] : null
            ];
        }

        if (!empty($data['description'])) {
            $schema['description'] = self::truncate($data['description'], 160);
        }

        self::addSchema($schema);
    }

    /**
     * Generate BreadcrumbList schema
     */
    public static function addBreadcrumbSchema(array $items): void
    {
        $listItems = [];
        $position = 1;

        foreach ($items as $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $item['name'],
                'item' => $item['url'] ?? null
            ];
        }

        self::addSchema([
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems
        ]);
    }

    /**
     * Generate LocalBusiness schema
     */
    public static function addLocalBusinessSchema(array $data): void
    {
        $schema = [
            '@type' => $data['type'] ?? 'LocalBusiness',
            'name' => $data['name'],
            'url' => $data['url'] ?? self::getSiteUrl()
        ];

        if (!empty($data['image'])) {
            $schema['image'] = $data['image'];
        }

        if (!empty($data['address'])) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $data['address']['street'] ?? '',
                'addressLocality' => $data['address']['city'] ?? '',
                'postalCode' => $data['address']['postal_code'] ?? '',
                'addressCountry' => $data['address']['country'] ?? 'PL'
            ];
        }

        if (!empty($data['phone'])) {
            $schema['telephone'] = $data['phone'];
        }

        if (!empty($data['hours'])) {
            $schema['openingHoursSpecification'] = $data['hours'];
        }

        if (!empty($data['priceRange'])) {
            $schema['priceRange'] = $data['priceRange'];
        }

        self::addSchema($schema);
    }

    /**
     * Generate Product schema
     */
    public static function addProductSchema(array $data): void
    {
        $schema = [
            '@type' => 'Product',
            'name' => $data['name']
        ];

        if (!empty($data['image'])) {
            $schema['image'] = $data['image'];
        }

        if (!empty($data['description'])) {
            $schema['description'] = $data['description'];
        }

        if (!empty($data['brand'])) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $data['brand']
            ];
        }

        if (!empty($data['price'])) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $data['price'],
                'priceCurrency' => $data['currency'] ?? 'PLN',
                'availability' => 'https://schema.org/' . ($data['availability'] ?? 'InStock')
            ];
        }

        if (!empty($data['rating'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $data['rating']['value'],
                'reviewCount' => $data['rating']['count']
            ];
        }

        self::addSchema($schema);
    }

    /**
     * Generate FAQ schema from accordion/FAQ content
     */
    public static function addFAQSchema(array $questions): void
    {
        $mainEntity = [];

        foreach ($questions as $q) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $q['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $q['answer']
                ]
            ];
        }

        self::addSchema([
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity
        ]);
    }

    /**
     * Generate Service schema
     */
    public static function addServiceSchema(array $data): void
    {
        $schema = [
            '@type' => 'Service',
            'name' => $data['name'],
            'description' => $data['description'] ?? ''
        ];

        if (!empty($data['provider'])) {
            $schema['provider'] = [
                '@type' => 'Organization',
                'name' => $data['provider']
            ];
        }

        if (!empty($data['area'])) {
            $schema['areaServed'] = $data['area'];
        }

        self::addSchema($schema);
    }

    /**
     * Auto-generate SEO from JTB content
     * Analyzes the content structure and extracts SEO data
     */
    public static function generateFromContent(array $content, array $options = []): void
    {
        $siteName = $options['site_name'] ?? JTB_Dynamic_Context::getSiteTitle();
        $pageUrl = $options['url'] ?? self::getCurrentUrl();
        $industry = $options['industry'] ?? 'general';

        // Extract title from first heading
        $title = self::extractFirstHeading($content);
        if ($title) {
            self::setTitle($title, $siteName);
            self::setMeta('og:title', $title, 'property');
            self::setMeta('twitter:title', $title);
        }

        // Extract description from first text/paragraph
        $description = self::extractFirstParagraph($content);
        if ($description) {
            self::setDescription($description);
        }

        // Extract image for OG
        $image = self::extractFirstImage($content);
        if ($image) {
            self::setMeta('og:image', $image, 'property');
            self::setMeta('twitter:image', $image);
        }

        // Set canonical
        self::setCanonical($pageUrl);

        // Set OG type
        self::setMeta('og:type', 'website', 'property');
        self::setMeta('og:site_name', $siteName, 'property');

        // Twitter card
        self::setMeta('twitter:card', 'summary_large_image');

        // Extract FAQ if present
        $faqs = self::extractFAQContent($content);
        if (!empty($faqs)) {
            self::addFAQSchema($faqs);
        }

        // Add website schema
        self::addWebsiteSchema(['name' => $siteName, 'url' => self::getSiteUrl()]);
    }

    /**
     * Render all SEO tags for <head>
     *
     * @return string HTML meta tags
     */
    public static function render(): string
    {
        $output = "\n<!-- JTB SEO -->\n";

        // Title tag
        if (isset(self::$meta['_title'])) {
            $output .= '<title>' . htmlspecialchars(self::$meta['_title']['content']) . "</title>\n";
            unset(self::$meta['_title']);
        }

        // Canonical
        if (self::$canonical) {
            $output .= '<link rel="canonical" href="' . htmlspecialchars(self::$canonical) . "\">\n";
        }

        // Alternates (hreflang)
        foreach (self::$alternates as $lang => $url) {
            $output .= '<link rel="alternate" hreflang="' . htmlspecialchars($lang) . '" href="' . htmlspecialchars($url) . "\">\n";
        }

        // Meta tags
        foreach (self::$meta as $name => $data) {
            $type = $data['type'] ?? 'name';
            $content = htmlspecialchars($data['content']);

            if ($type === 'property') {
                $output .= '<meta property="' . htmlspecialchars($name) . '" content="' . $content . "\">\n";
            } else {
                $output .= '<meta name="' . htmlspecialchars($name) . '" content="' . $content . "\">\n";
            }
        }

        // JSON-LD schemas
        if (!empty(self::$schemas)) {
            $jsonLd = count(self::$schemas) === 1 ? self::$schemas[0] : self::$schemas;
            $output .= '<script type="application/ld+json">' . "\n";
            $output .= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $output .= "\n</script>\n";
        }

        $output .= "<!-- /JTB SEO -->\n";

        return $output;
    }

    /**
     * Extract first heading from content
     */
    private static function extractFirstHeading(array $content): ?string
    {
        $sections = $content['content'] ?? $content['sections'] ?? [];

        foreach ($sections as $section) {
            $heading = self::findInChildren($section, 'heading');
            if ($heading) {
                return $heading['attrs']['text'] ?? null;
            }
        }

        return null;
    }

    /**
     * Extract first paragraph from content
     */
    private static function extractFirstParagraph(array $content): ?string
    {
        $sections = $content['content'] ?? $content['sections'] ?? [];

        foreach ($sections as $section) {
            $text = self::findInChildren($section, 'text');
            if ($text && !empty($text['attrs']['content'])) {
                return strip_tags($text['attrs']['content']);
            }
        }

        return null;
    }

    /**
     * Extract first image from content
     */
    private static function extractFirstImage(array $content): ?string
    {
        $sections = $content['content'] ?? $content['sections'] ?? [];

        foreach ($sections as $section) {
            $image = self::findInChildren($section, 'image');
            if ($image && !empty($image['attrs']['src'])) {
                return $image['attrs']['src'];
            }

            // Check section background
            if (!empty($section['attrs']['background_image'])) {
                return $section['attrs']['background_image'];
            }
        }

        return null;
    }

    /**
     * Extract FAQ content from accordions
     */
    private static function extractFAQContent(array $content): array
    {
        $faqs = [];
        $sections = $content['content'] ?? $content['sections'] ?? [];

        foreach ($sections as $section) {
            $accordion = self::findInChildren($section, 'accordion');
            if ($accordion && !empty($accordion['children'])) {
                foreach ($accordion['children'] as $item) {
                    if (($item['type'] ?? '') === 'accordion_item') {
                        $faqs[] = [
                            'question' => $item['attrs']['title'] ?? '',
                            'answer' => strip_tags($item['attrs']['content'] ?? '')
                        ];
                    }
                }
            }
        }

        return $faqs;
    }

    /**
     * Find module in children recursively
     */
    private static function findInChildren(array $node, string $type): ?array
    {
        if (($node['type'] ?? '') === $type) {
            return $node;
        }

        if (isset($node['children']) && is_array($node['children'])) {
            foreach ($node['children'] as $child) {
                $found = self::findInChildren($child, $type);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Truncate text to specified length
     */
    private static function truncate(string $text, int $length): string
    {
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (strlen($text) <= $length) {
            return $text;
        }

        $text = substr($text, 0, $length - 3);
        $lastSpace = strrpos($text, ' ');

        if ($lastSpace !== false) {
            $text = substr($text, 0, $lastSpace);
        }

        return $text . '...';
    }

    /**
     * Get current URL
     */
    private static function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        return $protocol . '://' . $host . $uri;
    }

    /**
     * Get site base URL
     */
    private static function getSiteUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $protocol . '://' . $host;
    }
}
