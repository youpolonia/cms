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
