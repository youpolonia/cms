<?php
declare(strict_types=1);

/**
 * ShopAI — AI-powered product copywriting for Jessie AI CMS
 * Provides AI generation for product copy, SEO, pricing, and reviews.
 */
class ShopAI
{
    /**
     * Generate full product copy using AI.
     *
     * @param string $name Product name
     * @param string $category Category name
     * @param string $features Comma-separated features/keywords
     * @param string $tone Tone: professional|casual|luxury|playful|technical|minimal
     * @param string $language Language code (en, pl, de, fr, es, it, pt, nl, etc.)
     * @return array ['ok' => bool, 'data' => [...] | 'error' => string]
     */
    public static function generateProductCopy(
        string $name,
        string $category,
        string $features,
        string $tone = 'professional',
        string $language = 'en'
    ): array {
        $allowedTones = ['professional', 'casual', 'luxury', 'playful', 'technical', 'minimal'];
        if (!in_array($tone, $allowedTones, true)) {
            $tone = 'professional';
        }

        $langNames = [
            'en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French',
            'es' => 'Spanish', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch',
            'sv' => 'Swedish', 'da' => 'Danish', 'no' => 'Norwegian', 'fi' => 'Finnish',
            'cs' => 'Czech', 'sk' => 'Slovak', 'hu' => 'Hungarian', 'ro' => 'Romanian',
            'bg' => 'Bulgarian', 'hr' => 'Croatian', 'sl' => 'Slovenian', 'et' => 'Estonian',
            'lv' => 'Latvian', 'lt' => 'Lithuanian', 'ja' => 'Japanese', 'ko' => 'Korean',
            'zh' => 'Chinese', 'ar' => 'Arabic', 'tr' => 'Turkish', 'ru' => 'Russian',
        ];
        $langLabel = $langNames[$language] ?? $language;

        $systemPrompt = 'You are a world-class e-commerce copywriter. You write compelling, SEO-optimized product copy that converts browsers into buyers.';

        $userPrompt = <<<PROMPT
Write product copy for the following product. Use a {$tone} tone. Write ALL content in {$langLabel}.

Product name: {$name}
Category: {$category}
Key features/keywords: {$features}

Return ONLY valid JSON (no markdown, no explanation) with these exact keys:
{
  "short_description": "A compelling 1-2 sentence product summary (max 255 chars)",
  "description": "Rich HTML product description with <p> paragraphs and <ul><li> bullet points. 2-4 paragraphs + feature list. Make it engaging and SEO-friendly.",
  "meta_title": "SEO meta title, max 60 characters",
  "meta_description": "SEO meta description, max 160 characters",
  "tags": ["tag1", "tag2", "tag3", "tag4", "tag5"]
}
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 2000, 'temperature' => 0.7]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $content = $result['content'] ?? '';
        $parsed = self::parseJsonFromResponse($content);

        if ($parsed === null) {
            return ['ok' => false, 'error' => 'Failed to parse AI response as JSON'];
        }

        // Validate required keys
        $required = ['short_description', 'description', 'meta_title', 'meta_description'];
        foreach ($required as $key) {
            if (empty($parsed[$key])) {
                return ['ok' => false, 'error' => "AI response missing required field: {$key}"];
            }
        }

        // Enforce limits
        $parsed['meta_title'] = mb_substr($parsed['meta_title'], 0, 60);
        $parsed['meta_description'] = mb_substr($parsed['meta_description'], 0, 160);
        $parsed['short_description'] = mb_substr($parsed['short_description'], 0, 255);

        if (!isset($parsed['tags']) || !is_array($parsed['tags'])) {
            $parsed['tags'] = [];
        }

        return [
            'ok' => true,
            'data' => [
                'short_description' => $parsed['short_description'],
                'description' => $parsed['description'],
                'meta_title' => $parsed['meta_title'],
                'meta_description' => $parsed['meta_description'],
                'tags' => $parsed['tags'],
            ],
        ];
    }

    /**
     * Generate SEO meta fields for an existing product.
     *
     * @param int $productId Product ID
     * @return array ['ok' => bool, 'meta_title' => string, 'meta_description' => string]
     */
    public static function generateSEO(int $productId): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $name = $product['name'] ?? '';
        $category = $product['category_name'] ?? '';
        $description = strip_tags($product['description'] ?? '');
        $shortDesc = $product['short_description'] ?? '';

        $systemPrompt = 'You are an SEO expert specializing in e-commerce product pages. You create meta titles and descriptions that maximize click-through rates from search results.';

        $userPrompt = <<<PROMPT
Generate optimized SEO meta fields for this product:

Product name: {$name}
Category: {$category}
Short description: {$shortDesc}
Description excerpt: {$description}

Return ONLY valid JSON (no markdown, no explanation):
{
  "meta_title": "SEO-optimized title, max 60 characters, include product name",
  "meta_description": "Compelling meta description, max 160 characters, include call-to-action"
}
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 500, 'temperature' => 0.6]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $parsed = self::parseJsonFromResponse($result['content'] ?? '');
        if ($parsed === null || empty($parsed['meta_title']) || empty($parsed['meta_description'])) {
            return ['ok' => false, 'error' => 'Failed to parse AI SEO response'];
        }

        return [
            'ok' => true,
            'meta_title' => mb_substr($parsed['meta_title'], 0, 60),
            'meta_description' => mb_substr($parsed['meta_description'], 0, 160),
        ];
    }

    /**
     * AI-powered price suggestion based on category analysis.
     *
     * @param int $productId Product ID
     * @return array ['ok' => bool, 'suggested_price' => float, 'reasoning' => string, ...]
     */
    public static function suggestPrice(int $productId): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $categoryId = (int)($product['category_id'] ?? 0);
        $pdo = db();

        // Get category prices
        $categoryPrices = [];
        if ($categoryId > 0) {
            $stmt = $pdo->prepare("SELECT price FROM products WHERE category_id = ? AND status = 'active' AND id != ?");
            $stmt->execute([$categoryId, $productId]);
            $categoryPrices = array_map('floatval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        if (empty($categoryPrices)) {
            // Fallback: all active products
            $stmt = $pdo->prepare("SELECT price FROM products WHERE status = 'active' AND id != ?");
            $stmt->execute([$productId]);
            $categoryPrices = array_map('floatval', $stmt->fetchAll(\PDO::FETCH_COLUMN));
        }

        if (empty($categoryPrices)) {
            return ['ok' => false, 'error' => 'Not enough products in category for price analysis'];
        }

        sort($categoryPrices);
        $count = count($categoryPrices);
        $avg = round(array_sum($categoryPrices) / $count, 2);
        $min = $categoryPrices[0];
        $max = $categoryPrices[$count - 1];
        $median = $count % 2 === 0
            ? round(($categoryPrices[$count / 2 - 1] + $categoryPrices[$count / 2]) / 2, 2)
            : $categoryPrices[(int)floor($count / 2)];

        $name = $product['name'] ?? '';
        $category = $product['category_name'] ?? 'Uncategorized';
        $currentPrice = (float)($product['price'] ?? 0);
        $description = strip_tags($product['short_description'] ?? $product['description'] ?? '');

        $systemPrompt = 'You are a pricing strategist for e-commerce. You analyze market data and product positioning to suggest optimal prices.';

        $userPrompt = <<<PROMPT
Suggest an optimal price for this product based on category analysis:

Product: {$name}
Category: {$category}
Current price: {$currentPrice}
Description: {$description}

Category statistics ({$count} products):
- Average: {$avg}
- Median: {$median}
- Min: {$min}
- Max: {$max}

Return ONLY valid JSON:
{
  "suggested_price": 29.99,
  "reasoning": "Brief explanation of the suggested price and positioning strategy"
}
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 500, 'temperature' => 0.5]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $parsed = self::parseJsonFromResponse($result['content'] ?? '');
        if ($parsed === null || !isset($parsed['suggested_price'])) {
            return ['ok' => false, 'error' => 'Failed to parse AI price suggestion'];
        }

        return [
            'ok' => true,
            'suggested_price' => round((float)$parsed['suggested_price'], 2),
            'reasoning' => $parsed['reasoning'] ?? '',
            'category_avg' => $avg,
            'category_min' => $min,
            'category_max' => $max,
        ];
    }

    /**
     * AI summary of product reviews.
     *
     * @param int $productId Product ID
     * @return array ['ok' => bool, 'summary' => string, 'sentiment' => string]
     */
    public static function summarizeReviews(int $productId): array
    {
        $pdo = db();

        // Query reviews directly
        $stmt = $pdo->prepare(
            "SELECT rating, title, review_text, customer_name FROM product_reviews WHERE product_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 50"
        );
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (count($reviews) < 3) {
            return ['ok' => false, 'error' => 'Not enough reviews (minimum 3 required)'];
        }

        // Build reviews text
        $reviewsText = '';
        foreach ($reviews as $i => $r) {
            $num = $i + 1;
            $rating = (int)$r['rating'];
            $title = $r['title'] ?? '';
            $text = $r['review_text'] ?? '';
            $reviewsText .= "Review #{$num} ({$rating}/5): {$title}\n{$text}\n\n";
        }

        $systemPrompt = 'You are a review analyst. You summarize customer reviews into clear, actionable insights.';

        $userPrompt = <<<PROMPT
Summarize these {$num} product reviews:

{$reviewsText}

Return ONLY valid JSON:
{
  "summary": "A concise 2-4 sentence summary highlighting key positives, negatives, and overall customer sentiment",
  "sentiment": "positive|mixed|negative"
}
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 800, 'temperature' => 0.5]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI generation failed'];
        }

        $parsed = self::parseJsonFromResponse($result['content'] ?? '');
        if ($parsed === null || empty($parsed['summary']) || empty($parsed['sentiment'])) {
            return ['ok' => false, 'error' => 'Failed to parse AI review summary'];
        }

        $sentiment = $parsed['sentiment'];
        if (!in_array($sentiment, ['positive', 'mixed', 'negative'], true)) {
            $sentiment = 'mixed';
        }

        return [
            'ok' => true,
            'summary' => $parsed['summary'],
            'sentiment' => $sentiment,
        ];
    }

    /**
     * Generate recommendation text for related products.
     *
     * @param array $product Main product
     * @param array $relatedProducts Array of related product arrays
     * @return string Generated text or fallback
     */
    public static function generateRecommendationText(array $product, array $relatedProducts): string
    {
        if (empty($relatedProducts)) {
            return '';
        }

        $productName = $product['name'] ?? 'this product';
        $relatedNames = array_map(fn($p) => $p['name'] ?? '', $relatedProducts);
        $relatedList = implode(', ', array_filter($relatedNames));

        $systemPrompt = 'You are a friendly e-commerce copywriter. Write short, engaging recommendation text.';

        $userPrompt = <<<PROMPT
Write a short, engaging "Why you'll also love..." recommendation blurb (2-3 sentences max) for a product page.

Main product: {$productName}
Related products: {$relatedList}

Return ONLY the text, no JSON, no quotes.
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 300, 'temperature' => 0.8]);

        if (!$result['ok']) {
            return "You might also love these products that pair perfectly with {$productName}.";
        }

        return trim($result['content'] ?? '');
    }

    // ─── SEO ANALYSIS ───

    /**
     * Full SEO health analysis for a product page.
     * Integrates with ai_seo_assistant for deep analysis.
     *
     * @param int $productId Product ID
     * @param string $focusKeyword Optional focus keyword (auto-detected from product name if empty)
     * @param string $language Language code
     * @return array Full SEO analysis result
     */
    public static function analyzeSEO(int $productId, string $focusKeyword = '', string $language = 'en'): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $name = $product['name'] ?? '';
        $slug = $product['slug'] ?? '';
        $shortDesc = $product['short_description'] ?? '';
        $description = $product['description'] ?? '';
        $metaTitle = $product['meta_title'] ?? '';
        $metaDescription = $product['meta_description'] ?? '';
        $categoryName = $product['category_name'] ?? '';
        $price = $product['price'] ?? 0;

        if ($focusKeyword === '') {
            $focusKeyword = $name;
        }

        // Build content for analysis (simulated product page)
        $contentHtml = "<h1>{$name}</h1>\n";
        if ($shortDesc) {
            $contentHtml .= "<p class=\"short-desc\">{$shortDesc}</p>\n";
        }
        if ($metaTitle) {
            $contentHtml .= "<!-- meta title: {$metaTitle} -->\n";
        }
        if ($metaDescription) {
            $contentHtml .= "<!-- meta description: {$metaDescription} -->\n";
        }
        $contentHtml .= "<div class=\"product-description\">{$description}</div>\n";
        if ($categoryName) {
            $contentHtml .= "<nav class=\"breadcrumb\">Shop > {$categoryName} > {$name}</nav>\n";
        }

        // Use the CMS SEO Assistant engine
        require_once CMS_ROOT . '/core/ai_content.php';
        require_once CMS_ROOT . '/core/ai_seo_assistant.php';

        $spec = [
            'title'              => $metaTitle ?: $name,
            'url'                => "/shop/{$slug}",
            'focus_keyword'      => $focusKeyword,
            'secondary_keywords' => $categoryName,
            'content_html'       => $contentHtml,
            'content_type'       => 'product_page',
            'language'           => $language,
            'notes'              => "This is an e-commerce product page. Price: {$price}. Category: {$categoryName}. Evaluate product-specific SEO factors: schema markup readiness, product title optimization, image alt text, price visibility, CTA presence, review snippet readiness.",
        ];

        $result = ai_seo_assistant_analyze($spec);

        if (!empty($result['ok']) && !empty($result['data'])) {
            // Track score
            ai_seo_track_score([
                'entity_type' => 'product',
                'entity_id'   => $productId,
                'keyword'     => $focusKeyword,
                'score'       => (int)($result['data']['health_score'] ?? 0),
                'url'         => "/shop/{$slug}",
            ]);
        }

        return $result;
    }

    /**
     * Get SEO score history for a product.
     *
     * @param int $productId Product ID
     * @param int $limit Number of records
     * @return array Score history
     */
    public static function getSEOHistory(int $productId, int $limit = 20): array
    {
        require_once CMS_ROOT . '/core/ai_content.php';
        require_once CMS_ROOT . '/core/ai_seo_assistant.php';
        return ai_seo_get_score_history('product', $productId, $limit);
    }

    /**
     * Bulk SEO scan — analyze all active products and return scores.
     *
     * @param string $language Language code
     * @param int $limit Max products to scan (0 = all)
     * @return array ['ok' => bool, 'products' => [...], 'summary' => [...]]
     */
    public static function bulkSEOScan(string $language = 'en', int $limit = 0): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $pdo = db();

        $sql = "SELECT p.id, p.name, p.slug, p.short_description, p.description,
                       p.meta_title, p.meta_description, p.image, p.status,
                       c.name AS category_name
                FROM products p
                LEFT JOIN product_categories c ON p.category_id = c.id
                WHERE p.status = 'active'
                ORDER BY p.name ASC";
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $products = $pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
        $results = [];
        $totalScore = 0;
        $issues = ['critical' => 0, 'warning' => 0, 'good' => 0];

        foreach ($products as $p) {
            $score = self::quickSEOScore($p);
            $totalScore += $score['score'];

            if ($score['score'] < 40) {
                $issues['critical']++;
            } elseif ($score['score'] < 70) {
                $issues['warning']++;
            } else {
                $issues['good']++;
            }

            $results[] = [
                'id'               => (int)$p['id'],
                'name'             => $p['name'],
                'slug'             => $p['slug'],
                'image'            => $p['image'] ?? '',
                'category'         => $p['category_name'] ?? '',
                'status'           => $p['status'],
                'score'            => $score['score'],
                'grade'            => $score['grade'],
                'issues'           => $score['issues'],
                'has_meta_title'   => !empty($p['meta_title']),
                'has_meta_desc'    => !empty($p['meta_description']),
                'has_description'  => !empty($p['description']),
                'has_image'        => !empty($p['image']),
            ];
        }

        $count = count($products);
        $avgScore = $count > 0 ? round($totalScore / $count) : 0;

        return [
            'ok'       => true,
            'products' => $results,
            'summary'  => [
                'total'     => $count,
                'avg_score' => $avgScore,
                'critical'  => $issues['critical'],
                'warning'   => $issues['warning'],
                'good'      => $issues['good'],
            ],
        ];
    }

    /**
     * Quick SEO score (rule-based, no AI call) for bulk scanning.
     *
     * @param array $product Product data
     * @return array ['score' => int, 'grade' => string, 'issues' => array]
     */
    public static function quickSEOScore(array $product): array
    {
        $score = 0;
        $maxScore = 0;
        $issues = [];

        // Title (15 pts)
        $maxScore += 15;
        $name = $product['name'] ?? '';
        if (mb_strlen($name) >= 10 && mb_strlen($name) <= 70) {
            $score += 15;
        } elseif (mb_strlen($name) >= 5) {
            $score += 8;
            $issues[] = 'Product name too short or too long for SEO';
        } else {
            $issues[] = 'Product name missing or very short';
        }

        // Meta title (15 pts)
        $maxScore += 15;
        $metaTitle = $product['meta_title'] ?? '';
        if ($metaTitle !== '' && mb_strlen($metaTitle) >= 20 && mb_strlen($metaTitle) <= 60) {
            $score += 15;
        } elseif ($metaTitle !== '') {
            $score += 8;
            $issues[] = 'Meta title length not optimal (aim for 20-60 chars)';
        } else {
            $issues[] = 'Missing meta title';
        }

        // Meta description (15 pts)
        $maxScore += 15;
        $metaDesc = $product['meta_description'] ?? '';
        if ($metaDesc !== '' && mb_strlen($metaDesc) >= 70 && mb_strlen($metaDesc) <= 160) {
            $score += 15;
        } elseif ($metaDesc !== '') {
            $score += 8;
            $issues[] = 'Meta description length not optimal (aim for 70-160 chars)';
        } else {
            $issues[] = 'Missing meta description';
        }

        // Description (20 pts)
        $maxScore += 20;
        $desc = strip_tags($product['description'] ?? '');
        $wordCount = str_word_count($desc);
        if ($wordCount >= 100) {
            $score += 20;
        } elseif ($wordCount >= 50) {
            $score += 12;
            $issues[] = 'Product description could be longer (aim for 100+ words)';
        } elseif ($wordCount >= 20) {
            $score += 6;
            $issues[] = 'Product description too short for good SEO';
        } else {
            $issues[] = 'Product description missing or very short';
        }

        // Short description (10 pts)
        $maxScore += 10;
        $shortDesc = $product['short_description'] ?? '';
        if ($shortDesc !== '' && mb_strlen($shortDesc) >= 50) {
            $score += 10;
        } elseif ($shortDesc !== '') {
            $score += 5;
            $issues[] = 'Short description could be more detailed';
        } else {
            $issues[] = 'Missing short description';
        }

        // Image (10 pts)
        $maxScore += 10;
        if (!empty($product['image'])) {
            $score += 10;
        } else {
            $issues[] = 'Missing product image';
        }

        // Slug (5 pts)
        $maxScore += 5;
        $slug = $product['slug'] ?? '';
        if ($slug !== '' && mb_strlen($slug) <= 60 && !preg_match('/[A-Z]/', $slug)) {
            $score += 5;
        } elseif ($slug !== '') {
            $score += 3;
            $issues[] = 'Product slug could be improved';
        } else {
            $issues[] = 'Missing product slug';
        }

        // Category (5 pts)
        $maxScore += 5;
        if (!empty($product['category_name'])) {
            $score += 5;
        } else {
            $issues[] = 'Product not assigned to a category';
        }

        // Keyword in meta (5 pts)
        $maxScore += 5;
        $nameWords = array_filter(explode(' ', strtolower($name)));
        $metaTitleLower = strtolower($metaTitle);
        $keywordInMeta = false;
        foreach ($nameWords as $w) {
            if (mb_strlen($w) >= 4 && strpos($metaTitleLower, $w) !== false) {
                $keywordInMeta = true;
                break;
            }
        }
        if ($keywordInMeta) {
            $score += 5;
        } elseif ($metaTitle !== '') {
            $issues[] = 'Product name keyword not found in meta title';
        }

        $pct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;

        $grade = 'A';
        if ($pct < 40) $grade = 'F';
        elseif ($pct < 55) $grade = 'D';
        elseif ($pct < 70) $grade = 'C';
        elseif ($pct < 85) $grade = 'B';

        return [
            'score'  => $pct,
            'grade'  => $grade,
            'issues' => $issues,
        ];
    }

    // ─── CONTENT REWRITE ───

    /**
     * Rewrite product description using AI.
     *
     * @param int $productId Product ID
     * @param string $mode Rewrite mode: paraphrase|summarize|expand|simplify|formalize|casual|seo|kids
     * @param string $targetField Which field to rewrite: description|short_description
     * @param array $options Additional options (tone, keywords, etc.)
     * @return array ['ok' => bool, 'original' => string, 'rewritten' => string]
     */
    public static function rewriteContent(int $productId, string $mode = 'seo', string $targetField = 'description', array $options = []): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $content = '';
        if ($targetField === 'short_description') {
            $content = $product['short_description'] ?? '';
        } else {
            $content = $product['description'] ?? '';
        }

        if (trim($content) === '') {
            return ['ok' => false, 'error' => "Product {$targetField} is empty — nothing to rewrite"];
        }

        require_once CMS_ROOT . '/core/ai_content.php';
        require_once CMS_ROOT . '/core/ai_content_rewrite.php';

        $rewriteOptions = array_merge([
            'tone'     => $options['tone'] ?? 'professional',
            'keywords' => $options['keywords'] ?? ($product['name'] ?? ''),
        ], $options);

        $result = ai_rewrite_content($content, $mode, $rewriteOptions);

        if (!empty($result['ok'])) {
            return [
                'ok'        => true,
                'original'  => $content,
                'rewritten' => $result['content'] ?? '',
                'mode'      => $mode,
                'field'     => $targetField,
            ];
        }

        return ['ok' => false, 'error' => $result['error'] ?? 'Rewrite failed'];
    }

    // ─── KEYWORD RESEARCH ───

    /**
     * AI-powered keyword research for a product.
     *
     * @param int $productId Product ID
     * @param string $language Language code
     * @return array ['ok' => bool, 'keywords' => [...], 'questions' => [...], 'lsi' => [...]]
     */
    public static function keywordResearch(int $productId, string $language = 'en'): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct($productId);
        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        $name = $product['name'] ?? '';
        $category = $product['category_name'] ?? '';
        $shortDesc = $product['short_description'] ?? '';
        $description = strip_tags($product['description'] ?? '');

        $langNames = [
            'en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French',
            'es' => 'Spanish', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch',
        ];
        $langLabel = $langNames[$language] ?? $language;

        $systemPrompt = 'You are an expert e-commerce SEO keyword researcher. Return ONLY valid JSON, no markdown.';

        $userPrompt = <<<PROMPT
Perform keyword research for this e-commerce product. Write all content in {$langLabel}.

Product: {$name}
Category: {$category}
Short description: {$shortDesc}
Description excerpt: {$description}

Return ONLY valid JSON:
{
  "primary_keywords": [
    {"keyword": "main keyword", "search_intent": "transactional|informational|navigational", "difficulty": "low|medium|high", "priority": "high|medium|low"}
  ],
  "long_tail_keywords": [
    {"keyword": "long tail phrase", "search_intent": "transactional|informational", "monthly_volume_est": "high|medium|low"}
  ],
  "lsi_keywords": ["semantic keyword 1", "semantic keyword 2"],
  "questions": ["question people ask 1", "question 2", "question 3"],
  "competitor_keywords": ["keyword competitors rank for 1", "keyword 2"],
  "content_suggestions": [
    {"type": "blog_post|faq|buying_guide|comparison", "title": "suggested content title", "target_keyword": "keyword to target"}
  ]
}

Requirements:
- 5-8 primary keywords (mix of head terms and mid-tail)
- 8-12 long tail keywords (buyer intent focus)
- 10-15 LSI/semantic keywords
- 5-8 People Also Ask questions
- 5-8 competitor keywords
- 3-5 content suggestions for supporting the product page
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 2500, 'temperature' => 0.6]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Keyword research failed'];
        }

        $parsed = self::parseJsonFromResponse($result['content'] ?? '');
        if ($parsed === null) {
            return ['ok' => false, 'error' => 'Failed to parse keyword research response'];
        }

        return [
            'ok'                   => true,
            'primary_keywords'     => $parsed['primary_keywords'] ?? [],
            'long_tail_keywords'   => $parsed['long_tail_keywords'] ?? [],
            'lsi_keywords'         => $parsed['lsi_keywords'] ?? [],
            'questions'            => $parsed['questions'] ?? [],
            'competitor_keywords'  => $parsed['competitor_keywords'] ?? [],
            'content_suggestions'  => $parsed['content_suggestions'] ?? [],
        ];
    }

    // ─── CATEGORY AI ───

    /**
     * Generate AI description for a product category.
     *
     * @param int $categoryId Category ID
     * @param string $tone Tone
     * @param string $language Language code
     * @return array ['ok' => bool, 'description' => string, 'meta_title' => string, 'meta_description' => string]
     */
    public static function generateCategoryDescription(int $categoryId, string $tone = 'professional', string $language = 'en'): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM product_categories WHERE id = ?");
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$category) {
            return ['ok' => false, 'error' => 'Category not found'];
        }

        $catName = $category['name'] ?? '';
        $catSlug = $category['slug'] ?? '';

        // Get products in this category for context
        $stmt2 = $pdo->prepare("SELECT name, short_description FROM products WHERE category_id = ? AND status = 'active' LIMIT 10");
        $stmt2->execute([$categoryId]);
        $products = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
        $productList = implode(', ', array_column($products, 'name'));

        $langNames = [
            'en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French',
            'es' => 'Spanish', 'it' => 'Italian', 'pt' => 'Portuguese', 'nl' => 'Dutch',
        ];
        $langLabel = $langNames[$language] ?? $language;

        $systemPrompt = 'You are an expert e-commerce copywriter. Write compelling, SEO-optimized category descriptions. Return ONLY valid JSON.';

        $userPrompt = <<<PROMPT
Write a category page description for an e-commerce store. Use a {$tone} tone. Write ALL content in {$langLabel}.

Category name: {$catName}
Products in category: {$productList}

Return ONLY valid JSON:
{
  "description": "Rich HTML description (2-3 paragraphs with <p> tags, include relevant keywords naturally). Should help customers understand what they'll find and why they should browse this category.",
  "meta_title": "SEO meta title, max 60 characters, include category name",
  "meta_description": "SEO meta description, max 160 characters, compelling with CTA"
}
PROMPT;

        $result = self::callAI($systemPrompt, $userPrompt, ['max_tokens' => 1500, 'temperature' => 0.7]);

        if (!$result['ok']) {
            return ['ok' => false, 'error' => $result['error'] ?? 'Category description generation failed'];
        }

        $parsed = self::parseJsonFromResponse($result['content'] ?? '');
        if ($parsed === null || empty($parsed['description'])) {
            return ['ok' => false, 'error' => 'Failed to parse category description'];
        }

        return [
            'ok'               => true,
            'description'      => $parsed['description'],
            'meta_title'       => mb_substr($parsed['meta_title'] ?? $catName, 0, 60),
            'meta_description' => mb_substr($parsed['meta_description'] ?? '', 0, 160),
        ];
    }

    // ─── BULK OPERATIONS ───

    /**
     * Bulk generate SEO meta fields for multiple products.
     *
     * @param array $productIds Array of product IDs
     * @param bool $overwrite Overwrite existing meta fields
     * @return array ['ok' => bool, 'results' => [...], 'generated' => int, 'skipped' => int, 'failed' => int]
     */
    public static function bulkGenerateSEO(array $productIds, bool $overwrite = false): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $results = [];
        $generated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($productIds as $pid) {
            $pid = (int)$pid;
            $product = \Shop::getProduct($pid);
            if (!$product) {
                $failed++;
                $results[] = ['id' => $pid, 'status' => 'error', 'error' => 'Not found'];
                continue;
            }

            // Skip if already has meta and overwrite is false
            if (!$overwrite && !empty($product['meta_title']) && !empty($product['meta_description'])) {
                $skipped++;
                $results[] = ['id' => $pid, 'name' => $product['name'], 'status' => 'skipped'];
                continue;
            }

            $seoResult = self::generateSEO($pid);
            if ($seoResult['ok']) {
                // Save to database
                $pdo = db();
                $stmt = $pdo->prepare("UPDATE products SET meta_title = ?, meta_description = ? WHERE id = ?");
                $stmt->execute([
                    $seoResult['meta_title'],
                    $seoResult['meta_description'],
                    $pid,
                ]);
                $generated++;
                $results[] = [
                    'id'               => $pid,
                    'name'             => $product['name'],
                    'status'           => 'generated',
                    'meta_title'       => $seoResult['meta_title'],
                    'meta_description' => $seoResult['meta_description'],
                ];
            } else {
                $failed++;
                $results[] = ['id' => $pid, 'name' => $product['name'], 'status' => 'error', 'error' => $seoResult['error'] ?? 'Unknown error'];
            }

            // Rate limit — don't hammer the API
            usleep(500000); // 0.5s between calls
        }

        return [
            'ok'        => true,
            'results'   => $results,
            'generated' => $generated,
            'skipped'   => $skipped,
            'failed'    => $failed,
        ];
    }

    /**
     * Bulk rewrite descriptions for multiple products.
     *
     * @param array $productIds Array of product IDs
     * @param string $mode Rewrite mode
     * @param bool $apply Auto-save to database
     * @return array
     */
    public static function bulkRewrite(array $productIds, string $mode = 'seo', bool $apply = false): array
    {
        require_once CMS_ROOT . '/core/shop.php';
        $results = [];
        $rewritten = 0;
        $failed = 0;

        foreach ($productIds as $pid) {
            $pid = (int)$pid;
            $result = self::rewriteContent($pid, $mode, 'description');

            if ($result['ok']) {
                if ($apply) {
                    $pdo = db();
                    $stmt = $pdo->prepare("UPDATE products SET description = ? WHERE id = ?");
                    $stmt->execute([$result['rewritten'], $pid]);
                }
                $rewritten++;
                $results[] = [
                    'id'       => $pid,
                    'status'   => 'ok',
                    'preview'  => mb_substr(strip_tags($result['rewritten']), 0, 200),
                ];
            } else {
                $failed++;
                $results[] = ['id' => $pid, 'status' => 'error', 'error' => $result['error'] ?? 'Unknown'];
            }

            usleep(500000);
        }

        return [
            'ok'        => true,
            'results'   => $results,
            'rewritten' => $rewritten,
            'failed'    => $failed,
        ];
    }

    // ─── PRIVATE HELPERS ───

    /**
     * Auto-detect AI provider and model from settings.
     *
     * @return array [$provider, $model] or ['', '']
     */
    private static function getProviderAndModel(): array
    {
        $settingsFile = CMS_ROOT . '/config/ai_settings.json';
        if (!file_exists($settingsFile)) {
            return ['', ''];
        }

        $settings = json_decode(file_get_contents($settingsFile), true);
        if (!is_array($settings)) {
            return ['', ''];
        }

        $providers = $settings['providers'] ?? [];

        foreach (['anthropic', 'openai', 'deepseek', 'google'] as $p) {
            if (!empty($providers[$p]['api_key']) && !empty($providers[$p]['enabled'])) {
                $model = $providers[$p]['default_model'] ?? '';
                if ($model) {
                    return [$p, $model];
                }
            }
        }

        return ['', ''];
    }

    /**
     * Call AI with system and user prompts.
     *
     * @param string $systemPrompt System prompt
     * @param string $userPrompt User prompt
     * @param array $options Options (max_tokens, temperature)
     * @return array ['ok' => bool, 'content' => string|null, 'error' => string|null]
     */
    private static function callAI(string $systemPrompt, string $userPrompt, array $options = []): array
    {
        require_once CMS_ROOT . '/core/ai_content.php';

        [$provider, $model] = self::getProviderAndModel();

        if ($provider === '' || $model === '') {
            return ['ok' => false, 'content' => null, 'error' => 'No AI provider configured. Please configure an AI provider in Settings.'];
        }

        return ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, $options);
    }

    /**
     * Parse JSON from AI response, handling markdown code blocks.
     *
     * @param string $text Raw AI response
     * @return array|null Parsed data or null on failure
     */
    private static function parseJsonFromResponse(string $text): ?array
    {
        $text = trim($text);

        // Try direct parse first
        $data = json_decode($text, true);
        if (is_array($data)) {
            return $data;
        }

        // Strip markdown code blocks: ```json ... ``` or ``` ... ```
        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?\s*```/s', $text, $m)) {
            $data = json_decode(trim($m[1]), true);
            if (is_array($data)) {
                return $data;
            }
        }

        // Try to find JSON object in the response
        if (preg_match('/\{[\s\S]*\}/m', $text, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) {
                return $data;
            }
        }

        return null;
    }
}
