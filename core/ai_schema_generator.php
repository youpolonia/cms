<?php
/**
 * Schema.org Structured Data Generator
 * Premium SEO feature for generating JSON-LD markup
 *
 * Supports:
 * - Article, BlogPosting, NewsArticle
 * - FAQPage
 * - HowTo
 * - Product
 * - Organization
 * - BreadcrumbList
 * - WebPage
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

/**
 * Generate Article schema (Article, BlogPosting, NewsArticle)
 *
 * @param array $data Article data
 * @return array JSON-LD structure
 */
function ai_schema_article(array $data): array
{
    $type = $data['type'] ?? 'Article'; // Article, BlogPosting, NewsArticle
    $allowedTypes = ['Article', 'BlogPosting', 'NewsArticle', 'TechArticle'];
    if (!in_array($type, $allowedTypes, true)) {
        $type = 'Article';
    }

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $type,
        'headline' => $data['title'] ?? '',
        'description' => $data['description'] ?? '',
        'datePublished' => $data['published_date'] ?? date('c'),
        'dateModified' => $data['modified_date'] ?? date('c'),
    ];

    // Author
    if (!empty($data['author_name'])) {
        $schema['author'] = [
            '@type' => 'Person',
            'name' => $data['author_name'],
        ];
        if (!empty($data['author_url'])) {
            $schema['author']['url'] = $data['author_url'];
        }
    }

    // Publisher
    if (!empty($data['publisher_name'])) {
        $schema['publisher'] = [
            '@type' => 'Organization',
            'name' => $data['publisher_name'],
        ];
        if (!empty($data['publisher_logo'])) {
            $schema['publisher']['logo'] = [
                '@type' => 'ImageObject',
                'url' => $data['publisher_logo'],
            ];
        }
    }

    // Image
    if (!empty($data['image'])) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $data['image'],
        ];
        if (!empty($data['image_width'])) {
            $schema['image']['width'] = $data['image_width'];
        }
        if (!empty($data['image_height'])) {
            $schema['image']['height'] = $data['image_height'];
        }
    }

    // URL
    if (!empty($data['url'])) {
        $schema['mainEntityOfPage'] = [
            '@type' => 'WebPage',
            '@id' => $data['url'],
        ];
    }

    // Word count
    if (!empty($data['word_count'])) {
        $schema['wordCount'] = (int)$data['word_count'];
    }

    // Keywords
    if (!empty($data['keywords'])) {
        $schema['keywords'] = is_array($data['keywords'])
            ? implode(', ', $data['keywords'])
            : $data['keywords'];
    }

    // Article body (optional, can be large)
    if (!empty($data['article_body']) && strlen($data['article_body']) <= 5000) {
        $schema['articleBody'] = $data['article_body'];
    }

    return $schema;
}

/**
 * Generate FAQ schema
 *
 * @param array $faqs Array of ['question' => '...', 'answer' => '...']
 * @return array JSON-LD structure
 */
function ai_schema_faq(array $faqs): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [],
    ];

    foreach ($faqs as $faq) {
        if (empty($faq['question']) || empty($faq['answer'])) {
            continue;
        }

        $schema['mainEntity'][] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer'],
            ],
        ];
    }

    return $schema;
}

/**
 * Generate HowTo schema
 *
 * @param array $data HowTo data with steps
 * @return array JSON-LD structure
 */
function ai_schema_howto(array $data): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'HowTo',
        'name' => $data['name'] ?? '',
        'description' => $data['description'] ?? '',
    ];

    // Time estimates
    if (!empty($data['total_time'])) {
        $schema['totalTime'] = $data['total_time']; // ISO 8601 duration
    }
    if (!empty($data['prep_time'])) {
        $schema['prepTime'] = $data['prep_time'];
    }
    if (!empty($data['perform_time'])) {
        $schema['performTime'] = $data['perform_time'];
    }

    // Estimated cost
    if (!empty($data['estimated_cost'])) {
        $schema['estimatedCost'] = [
            '@type' => 'MonetaryAmount',
            'currency' => $data['currency'] ?? 'USD',
            'value' => $data['estimated_cost'],
        ];
    }

    // Supply (materials needed)
    if (!empty($data['supplies']) && is_array($data['supplies'])) {
        $schema['supply'] = [];
        foreach ($data['supplies'] as $supply) {
            $schema['supply'][] = [
                '@type' => 'HowToSupply',
                'name' => $supply,
            ];
        }
    }

    // Tools needed
    if (!empty($data['tools']) && is_array($data['tools'])) {
        $schema['tool'] = [];
        foreach ($data['tools'] as $tool) {
            $schema['tool'][] = [
                '@type' => 'HowToTool',
                'name' => $tool,
            ];
        }
    }

    // Steps
    if (!empty($data['steps']) && is_array($data['steps'])) {
        $schema['step'] = [];
        $position = 1;
        foreach ($data['steps'] as $step) {
            $stepSchema = [
                '@type' => 'HowToStep',
                'position' => $position,
            ];

            if (is_string($step)) {
                $stepSchema['text'] = $step;
            } else {
                if (!empty($step['name'])) {
                    $stepSchema['name'] = $step['name'];
                }
                if (!empty($step['text'])) {
                    $stepSchema['text'] = $step['text'];
                }
                if (!empty($step['url'])) {
                    $stepSchema['url'] = $step['url'];
                }
                if (!empty($step['image'])) {
                    $stepSchema['image'] = $step['image'];
                }
            }

            $schema['step'][] = $stepSchema;
            $position++;
        }
    }

    // Image
    if (!empty($data['image'])) {
        $schema['image'] = $data['image'];
    }

    return $schema;
}

/**
 * Generate Product schema
 *
 * @param array $data Product data
 * @return array JSON-LD structure
 */
function ai_schema_product(array $data): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $data['name'] ?? '',
        'description' => $data['description'] ?? '',
    ];

    // Image
    if (!empty($data['image'])) {
        $schema['image'] = is_array($data['image']) ? $data['image'] : [$data['image']];
    }

    // Brand
    if (!empty($data['brand'])) {
        $schema['brand'] = [
            '@type' => 'Brand',
            'name' => $data['brand'],
        ];
    }

    // SKU and identifiers
    if (!empty($data['sku'])) {
        $schema['sku'] = $data['sku'];
    }
    if (!empty($data['gtin'])) {
        $schema['gtin'] = $data['gtin'];
    }
    if (!empty($data['mpn'])) {
        $schema['mpn'] = $data['mpn'];
    }

    // Offer (price)
    if (!empty($data['price'])) {
        $schema['offers'] = [
            '@type' => 'Offer',
            'price' => $data['price'],
            'priceCurrency' => $data['currency'] ?? 'USD',
            'availability' => $data['availability'] ?? 'https://schema.org/InStock',
            'url' => $data['url'] ?? '',
        ];

        if (!empty($data['price_valid_until'])) {
            $schema['offers']['priceValidUntil'] = $data['price_valid_until'];
        }

        if (!empty($data['seller'])) {
            $schema['offers']['seller'] = [
                '@type' => 'Organization',
                'name' => $data['seller'],
            ];
        }
    }

    // Reviews and rating
    if (!empty($data['rating_value']) && !empty($data['rating_count'])) {
        $schema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => $data['rating_value'],
            'reviewCount' => $data['rating_count'],
            'bestRating' => $data['best_rating'] ?? 5,
            'worstRating' => $data['worst_rating'] ?? 1,
        ];
    }

    // Individual reviews
    if (!empty($data['reviews']) && is_array($data['reviews'])) {
        $schema['review'] = [];
        foreach ($data['reviews'] as $review) {
            $reviewSchema = [
                '@type' => 'Review',
                'reviewBody' => $review['body'] ?? '',
            ];

            if (!empty($review['author'])) {
                $reviewSchema['author'] = [
                    '@type' => 'Person',
                    'name' => $review['author'],
                ];
            }

            if (!empty($review['rating'])) {
                $reviewSchema['reviewRating'] = [
                    '@type' => 'Rating',
                    'ratingValue' => $review['rating'],
                ];
            }

            if (!empty($review['date'])) {
                $reviewSchema['datePublished'] = $review['date'];
            }

            $schema['review'][] = $reviewSchema;
        }
    }

    return $schema;
}

/**
 * Generate Organization schema
 *
 * @param array $data Organization data
 * @return array JSON-LD structure
 */
function ai_schema_organization(array $data): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $data['type'] ?? 'Organization', // Organization, LocalBusiness, Corporation
        'name' => $data['name'] ?? '',
    ];

    // URL
    if (!empty($data['url'])) {
        $schema['url'] = $data['url'];
    }

    // Logo
    if (!empty($data['logo'])) {
        $schema['logo'] = $data['logo'];
    }

    // Description
    if (!empty($data['description'])) {
        $schema['description'] = $data['description'];
    }

    // Contact
    if (!empty($data['email'])) {
        $schema['email'] = $data['email'];
    }
    if (!empty($data['phone'])) {
        $schema['telephone'] = $data['phone'];
    }

    // Address
    if (!empty($data['address'])) {
        if (is_string($data['address'])) {
            $schema['address'] = $data['address'];
        } else {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $data['address']['street'] ?? '',
                'addressLocality' => $data['address']['city'] ?? '',
                'addressRegion' => $data['address']['region'] ?? '',
                'postalCode' => $data['address']['postal_code'] ?? '',
                'addressCountry' => $data['address']['country'] ?? '',
            ];
        }
    }

    // Social profiles
    if (!empty($data['social_profiles']) && is_array($data['social_profiles'])) {
        $schema['sameAs'] = array_values($data['social_profiles']);
    }

    // Founding date
    if (!empty($data['founding_date'])) {
        $schema['foundingDate'] = $data['founding_date'];
    }

    // Contact point (customer service)
    if (!empty($data['contact_phone']) || !empty($data['contact_email'])) {
        $schema['contactPoint'] = [
            '@type' => 'ContactPoint',
            'contactType' => $data['contact_type'] ?? 'customer service',
        ];
        if (!empty($data['contact_phone'])) {
            $schema['contactPoint']['telephone'] = $data['contact_phone'];
        }
        if (!empty($data['contact_email'])) {
            $schema['contactPoint']['email'] = $data['contact_email'];
        }
    }

    return $schema;
}

/**
 * Generate BreadcrumbList schema
 *
 * @param array $items Array of ['name' => '...', 'url' => '...']
 * @return array JSON-LD structure
 */
function ai_schema_breadcrumb(array $items): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [],
    ];

    $position = 1;
    foreach ($items as $item) {
        if (empty($item['name'])) {
            continue;
        }

        $element = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $item['name'],
        ];

        if (!empty($item['url'])) {
            $element['item'] = $item['url'];
        }

        $schema['itemListElement'][] = $element;
        $position++;
    }

    return $schema;
}

/**
 * Generate WebPage schema
 *
 * @param array $data Page data
 * @return array JSON-LD structure
 */
function ai_schema_webpage(array $data): array
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $data['type'] ?? 'WebPage', // WebPage, AboutPage, ContactPage, FAQPage
        'name' => $data['name'] ?? '',
    ];

    if (!empty($data['url'])) {
        $schema['url'] = $data['url'];
    }

    if (!empty($data['description'])) {
        $schema['description'] = $data['description'];
    }

    if (!empty($data['date_published'])) {
        $schema['datePublished'] = $data['date_published'];
    }

    if (!empty($data['date_modified'])) {
        $schema['dateModified'] = $data['date_modified'];
    }

    // Primary image
    if (!empty($data['image'])) {
        $schema['primaryImageOfPage'] = [
            '@type' => 'ImageObject',
            'url' => $data['image'],
        ];
    }

    // Breadcrumb reference
    if (!empty($data['breadcrumb'])) {
        $schema['breadcrumb'] = ai_schema_breadcrumb($data['breadcrumb']);
    }

    // Part of website
    if (!empty($data['website_name']) || !empty($data['website_url'])) {
        $schema['isPartOf'] = [
            '@type' => 'WebSite',
            'name' => $data['website_name'] ?? '',
            'url' => $data['website_url'] ?? '',
        ];
    }

    return $schema;
}

/**
 * Extract FAQ questions from HTML content
 *
 * @param string $html HTML content
 * @return array Array of ['question' => '...', 'answer' => '...']
 */
function ai_schema_extract_faqs(string $html): array
{
    $faqs = [];

    // Pattern 1: FAQ sections with h2/h3 questions
    preg_match_all('/<h[23][^>]*>\s*(.*?)\?\s*<\/h[23]>\s*<p[^>]*>(.*?)<\/p>/is', $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $question = trim(strip_tags($match[1])) . '?';
        $answer = trim(strip_tags($match[2]));

        if (strlen($question) > 10 && strlen($answer) > 20) {
            $faqs[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }
    }

    // Pattern 2: Definition list style
    preg_match_all('/<dt[^>]*>(.*?)<\/dt>\s*<dd[^>]*>(.*?)<\/dd>/is', $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
        $question = trim(strip_tags($match[1]));
        $answer = trim(strip_tags($match[2]));

        if (strlen($question) > 5 && strlen($answer) > 20) {
            // Add question mark if missing
            if (!str_ends_with($question, '?')) {
                $question .= '?';
            }
            $faqs[] = [
                'question' => $question,
                'answer' => $answer,
            ];
        }
    }

    return $faqs;
}

/**
 * Convert schema array to JSON-LD script tag
 *
 * @param array $schema Schema data
 * @return string HTML script tag
 */
function ai_schema_to_html(array $schema): string
{
    $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

    if ($json === false) {
        return '';
    }

    return '<script type="application/ld+json">' . "\n" . $json . "\n" . '</script>';
}

/**
 * Validate schema structure
 *
 * @param array $schema Schema data
 * @return array Validation result with 'valid' and 'errors'
 */
function ai_schema_validate(array $schema): array
{
    $errors = [];

    // Check required fields
    if (empty($schema['@context'])) {
        $errors[] = 'Missing @context';
    }

    if (empty($schema['@type'])) {
        $errors[] = 'Missing @type';
    }

    // Type-specific validation
    $type = $schema['@type'] ?? '';

    switch ($type) {
        case 'Article':
        case 'BlogPosting':
        case 'NewsArticle':
            if (empty($schema['headline'])) {
                $errors[] = 'Article requires headline';
            }
            if (empty($schema['author'])) {
                $errors[] = 'Article should have author';
            }
            if (empty($schema['datePublished'])) {
                $errors[] = 'Article should have datePublished';
            }
            break;

        case 'FAQPage':
            if (empty($schema['mainEntity']) || !is_array($schema['mainEntity'])) {
                $errors[] = 'FAQPage requires at least one question';
            }
            break;

        case 'Product':
            if (empty($schema['name'])) {
                $errors[] = 'Product requires name';
            }
            if (empty($schema['offers'])) {
                $errors[] = 'Product should have offers (price)';
            }
            break;

        case 'Organization':
            if (empty($schema['name'])) {
                $errors[] = 'Organization requires name';
            }
            break;

        case 'HowTo':
            if (empty($schema['name'])) {
                $errors[] = 'HowTo requires name';
            }
            if (empty($schema['step']) || !is_array($schema['step'])) {
                $errors[] = 'HowTo requires at least one step';
            }
            break;
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors,
    ];
}

/**
 * Auto-detect appropriate schema type from content
 *
 * @param string $html HTML content
 * @param string $title Page title
 * @return string Suggested schema type
 */
function ai_schema_detect_type(string $html, string $title): string
{
    $htmlLower = strtolower($html);
    $titleLower = strtolower($title);

    // FAQ detection
    $faqIndicators = ['frequently asked', 'faq', 'questions and answers', 'q&a', 'q & a'];
    foreach ($faqIndicators as $indicator) {
        if (strpos($htmlLower, $indicator) !== false || strpos($titleLower, $indicator) !== false) {
            return 'FAQPage';
        }
    }

    // HowTo detection
    $howtoIndicators = ['how to', 'step by step', 'tutorial', 'guide', 'instructions'];
    foreach ($howtoIndicators as $indicator) {
        if (strpos($titleLower, $indicator) !== false) {
            return 'HowTo';
        }
    }

    // Product detection
    $productIndicators = ['price', 'buy now', 'add to cart', '$', '€', '£'];
    $productCount = 0;
    foreach ($productIndicators as $indicator) {
        if (strpos($htmlLower, $indicator) !== false) {
            $productCount++;
        }
    }
    if ($productCount >= 2) {
        return 'Product';
    }

    // Blog post indicators
    $blogIndicators = ['posted on', 'written by', 'comments', 'share this'];
    $blogCount = 0;
    foreach ($blogIndicators as $indicator) {
        if (strpos($htmlLower, $indicator) !== false) {
            $blogCount++;
        }
    }
    if ($blogCount >= 2) {
        return 'BlogPosting';
    }

    // Default to Article
    return 'Article';
}
