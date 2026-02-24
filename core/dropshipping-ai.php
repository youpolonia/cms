<?php
declare(strict_types=1);

/**
 * Dropshipping AI — Product Scout, Niche Finder, Competition Analysis, Trend Analysis
 * Faza 3: AI-powered research tools for finding profitable products
 */

class DSAI
{
    // ═══════════════════════════════════════════
    //  AI PRODUCT SCOUT
    // ═══════════════════════════════════════════

    /**
     * Analyze a product URL for dropshipping potential.
     * Returns: profitability score, competition level, demand estimate, recommendations.
     */
    public static function scoutProduct(string $url, array $options = []): array
    {
        // Fetch the page
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        ]);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$html || $httpCode !== 200) {
            return ['ok' => false, 'error' => "Failed to fetch URL (HTTP {$httpCode})"];
        }

        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = mb_substr($text, 0, 4000);

        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $language = $options['language'] ?? 'en';
        $market = $options['market'] ?? 'general';

        $prompt = <<<PROMPT
You are an expert dropshipping product analyst. Analyze this product page and provide a detailed assessment.

Product URL: {$url}
Target market: {$market}

Page content:
{$text}

Provide analysis in JSON format:
{
    "product_name": "extracted product name",
    "category": "product category",
    "supplier_price": 0.00,
    "suggested_sell_price": 0.00,
    "estimated_margin_pct": 0,
    "scores": {
        "profitability": 0-100,
        "demand": 0-100,
        "competition": 0-100,
        "shipping_ease": 0-100,
        "overall": 0-100
    },
    "pros": ["list of advantages for dropshipping this product"],
    "cons": ["list of disadvantages/risks"],
    "target_audience": "who would buy this",
    "marketing_angles": ["3-5 marketing/ad angles"],
    "similar_products": ["similar products to consider"],
    "verdict": "BUY / MAYBE / SKIP",
    "reasoning": "1-2 sentence summary"
}

Be realistic and data-driven. Consider: profit margins (aim for 30%+), shipping complexity, return rate potential, market saturation, seasonality.
Return ONLY valid JSON.
PROMPT;

        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.3]);

        $data = self::parseJsonResponse($response);
        if (!$data) {
            return ['ok' => false, 'error' => 'AI analysis failed to produce valid results'];
        }

        return ['ok' => true, 'analysis' => $data];
    }

    // ═══════════════════════════════════════════
    //  NICHE FINDER
    // ═══════════════════════════════════════════

    /**
     * AI-powered niche finder — suggests profitable niches based on criteria.
     */
    public static function findNiches(array $options = []): array
    {
        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $budget = $options['budget'] ?? 'medium';
        $interest = $options['interest'] ?? '';
        $market = $options['market'] ?? 'global';
        $season = $options['season'] ?? date('F Y');
        $count = min((int)($options['count'] ?? 5), 10);

        $interestClause = $interest ? "\nThe user is particularly interested in: {$interest}" : '';

        $prompt = <<<PROMPT
You are an expert e-commerce niche researcher. Find {$count} profitable dropshipping niches for {$season}.

Budget level: {$budget} (low = under $500 starting, medium = $500-2000, high = $2000+)
Target market: {$market}{$interestClause}

For each niche provide:
{
    "niches": [
        {
            "name": "niche name",
            "description": "brief description",
            "avg_product_price": "$X-$Y range",
            "avg_margin": "X-Y%",
            "competition_level": "low/medium/high",
            "trend": "rising/stable/declining",
            "seasonality": "year-round/seasonal (which months)",
            "target_demographic": "who buys",
            "example_products": ["3-5 specific product ideas"],
            "marketing_channels": ["best channels to sell"],
            "difficulty": 1-10,
            "profit_potential": 1-10,
            "why": "1-2 sentences on why this niche is good right now"
        }
    ]
}

Focus on:
- Niches with proven demand but not oversaturated
- Products with good margins (30%+ after all costs)
- Products that are easy to ship (light, not fragile)
- Trending or evergreen categories
- Low return rate potential

Return ONLY valid JSON.
PROMPT;

        $response = ai_universal_generate($prompt, ['max_tokens' => 3000, 'temperature' => 0.5]);

        $data = self::parseJsonResponse($response);
        if (!$data || empty($data['niches'])) {
            return ['ok' => false, 'error' => 'Niche analysis failed'];
        }

        return ['ok' => true, 'niches' => $data['niches']];
    }

    // ═══════════════════════════════════════════
    //  COMPETITION ANALYZER
    // ═══════════════════════════════════════════

    /**
     * Analyze competition for a product or niche using web search context.
     */
    public static function analyzeCompetition(string $query, array $options = []): array
    {
        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $market = $options['market'] ?? 'global';

        $prompt = <<<PROMPT
You are a competitive intelligence analyst for e-commerce. Analyze the competitive landscape for:

Product/Niche: {$query}
Market: {$market}

Provide analysis in JSON:
{
    "query": "{$query}",
    "market_size": "estimated (small/medium/large/huge)",
    "saturation_level": "low/medium/high/very_high",
    "dominant_players": ["major sellers/brands in this space"],
    "entry_barriers": ["barriers to entry"],
    "opportunities": ["gaps or opportunities newcomers can exploit"],
    "pricing_landscape": {
        "low_end": "$X",
        "mid_range": "$X-$Y",
        "premium": "$Y+"
    },
    "recommended_strategy": "how to compete effectively",
    "differentiation_ideas": ["ways to stand out"],
    "risk_factors": ["potential risks"],
    "verdict": {
        "enter_market": true/false,
        "confidence": 0-100,
        "reasoning": "why or why not"
    }
}

Be realistic. Consider: existing sellers, price wars, customer loyalty, marketing costs, product differentiation potential.
Return ONLY valid JSON.
PROMPT;

        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.3]);

        $data = self::parseJsonResponse($response);
        if (!$data) {
            return ['ok' => false, 'error' => 'Competition analysis failed'];
        }

        return ['ok' => true, 'analysis' => $data];
    }

    // ═══════════════════════════════════════════
    //  PROFIT CALCULATOR
    // ═══════════════════════════════════════════

    /**
     * Calculate full profitability including all costs.
     */
    public static function calculateFullProfit(array $params): array
    {
        $supplierPrice = (float)($params['supplier_price'] ?? 0);
        $sellPrice = (float)($params['sell_price'] ?? 0);
        $shippingCost = (float)($params['shipping_cost'] ?? 0);
        $adSpendPerSale = (float)($params['ad_spend_per_sale'] ?? 0);
        $returnRate = (float)($params['return_rate'] ?? 5); // %
        $paymentFee = (float)($params['payment_fee'] ?? 2.9); // %
        $paymentFixed = (float)($params['payment_fixed_fee'] ?? 0.30);
        $monthlyOverhead = (float)($params['monthly_overhead'] ?? 0);
        $expectedSalesPerMonth = max(1, (int)($params['expected_sales'] ?? 30));

        // Calculate per-unit costs
        $paymentCost = ($sellPrice * $paymentFee / 100) + $paymentFixed;
        $returnCost = $sellPrice * ($returnRate / 100);
        $overheadPerUnit = $monthlyOverhead / $expectedSalesPerMonth;

        $totalCost = $supplierPrice + $shippingCost + $adSpendPerSale + $paymentCost + $returnCost + $overheadPerUnit;
        $profit = $sellPrice - $totalCost;
        $marginPct = $sellPrice > 0 ? round(($profit / $sellPrice) * 100, 1) : 0;
        $roiPct = $totalCost > 0 ? round(($profit / $totalCost) * 100, 1) : 0;

        $monthlyRevenue = $sellPrice * $expectedSalesPerMonth;
        $monthlyProfit = $profit * $expectedSalesPerMonth;
        $monthlyCost = $totalCost * $expectedSalesPerMonth;

        // Determine verdict
        $verdict = 'profitable';
        if ($marginPct < 10) $verdict = 'risky';
        if ($marginPct < 0) $verdict = 'unprofitable';
        if ($marginPct >= 30) $verdict = 'highly_profitable';

        return [
            'ok' => true,
            'per_unit' => [
                'sell_price'      => round($sellPrice, 2),
                'supplier_cost'   => round($supplierPrice, 2),
                'shipping'        => round($shippingCost, 2),
                'ad_spend'        => round($adSpendPerSale, 2),
                'payment_fee'     => round($paymentCost, 2),
                'return_cost'     => round($returnCost, 2),
                'overhead'        => round($overheadPerUnit, 2),
                'total_cost'      => round($totalCost, 2),
                'profit'          => round($profit, 2),
                'margin_pct'      => $marginPct,
                'roi_pct'         => $roiPct,
            ],
            'monthly' => [
                'revenue'    => round($monthlyRevenue, 2),
                'cost'       => round($monthlyCost, 2),
                'profit'     => round($monthlyProfit, 2),
                'sales'      => $expectedSalesPerMonth,
                'ad_budget'  => round($adSpendPerSale * $expectedSalesPerMonth, 2),
            ],
            'annual' => [
                'revenue' => round($monthlyRevenue * 12, 2),
                'profit'  => round($monthlyProfit * 12, 2),
            ],
            'verdict'    => $verdict,
            'breakeven'  => $totalCost > 0 ? (int)ceil($monthlyOverhead / max(0.01, $profit)) : 0,
        ];
    }

    // ═══════════════════════════════════════════
    //  PRODUCT DESCRIPTION OPTIMIZER
    // ═══════════════════════════════════════════

    /**
     * Generate optimized product listing (title, bullets, description) for marketplace.
     */
    public static function optimizeListing(int $productId, string $platform = 'general', string $language = 'en'): array
    {
        $pdo = db();
        $product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $product->execute([$productId]);
        $product = $product->fetch(\PDO::FETCH_ASSOC);

        if (!$product) {
            return ['ok' => false, 'error' => 'Product not found'];
        }

        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $name = $product['name'] ?? '';
        $desc = $product['description'] ?? '';
        $price = $product['price'] ?? 0;

        $platformInstructions = match ($platform) {
            'amazon'   => 'Optimize for Amazon: use search-friendly title (max 200 chars), 5 bullet points with keywords, A+ content style description.',
            'ebay'     => 'Optimize for eBay: catchy title (max 80 chars), item specifics-style bullets, buyer-friendly description.',
            'shopify'  => 'Optimize for Shopify store: clean, scannable, benefit-focused, include trust signals.',
            'facebook' => 'Optimize for Facebook Marketplace/Ads: attention-grabbing, emotional, short and punchy.',
            default    => 'Create a professional, SEO-optimized listing that works across platforms.',
        };

        $prompt = <<<PROMPT
You are an expert e-commerce copywriter. Optimize this product listing.

Current product:
Name: {$name}
Price: \${$price}
Description: {$desc}

{$platformInstructions}
Language: {$language}

Return JSON:
{
    "title": "optimized product title",
    "subtitle": "optional subtitle or tagline",
    "bullet_points": ["5-7 benefit-focused bullet points"],
    "description": "full optimized description (HTML allowed)",
    "short_description": "1-2 sentence summary",
    "search_keywords": ["10 relevant search keywords"],
    "target_emotions": ["emotional triggers used"],
    "cta": "call to action text"
}

Return ONLY valid JSON.
PROMPT;

        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.4]);
        $data = self::parseJsonResponse($response);

        if (!$data) {
            return ['ok' => false, 'error' => 'Listing optimization failed'];
        }

        return ['ok' => true, 'listing' => $data, 'product_id' => $productId, 'platform' => $platform];
    }

    // ═══════════════════════════════════════════
    //  TREND ANALYSIS
    // ═══════════════════════════════════════════

    /**
     * AI-based trend analysis for a product category.
     */
    public static function analyzeTrends(string $category, array $options = []): array
    {
        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $timeframe = $options['timeframe'] ?? '2024-2026';
        $market = $options['market'] ?? 'global';

        $prompt = <<<PROMPT
You are a market trend analyst for e-commerce. Analyze trends for:

Category: {$category}
Timeframe: {$timeframe}
Market: {$market}

Provide analysis in JSON:
{
    "category": "{$category}",
    "trend_direction": "rising/stable/declining",
    "growth_rate": "estimated annual growth %",
    "peak_seasons": ["best months/seasons for sales"],
    "emerging_subcategories": ["3-5 trending sub-categories"],
    "dying_subcategories": ["sub-categories to avoid"],
    "consumer_shifts": ["key changes in consumer behavior"],
    "price_trends": "are prices going up/down/stable",
    "technology_impact": "how tech is changing this category",
    "predictions": ["3-5 predictions for next 12 months"],
    "opportunities": ["specific opportunities for new sellers"],
    "risks": ["market risks to watch"],
    "recommended_products": ["5 specific product ideas that fit current trends"]
}

Base your analysis on real market knowledge. Be specific and actionable.
Return ONLY valid JSON.
PROMPT;

        $response = ai_universal_generate($prompt, ['max_tokens' => 2000, 'temperature' => 0.4]);
        $data = self::parseJsonResponse($response);

        if (!$data) {
            return ['ok' => false, 'error' => 'Trend analysis failed'];
        }

        return ['ok' => true, 'trends' => $data];
    }

    // ═══════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════

    /**
     * Parse JSON from AI response (handles markdown code blocks).
     */
    private static function parseJsonResponse(string $response): ?array
    {
        $response = trim($response);

        // Strip markdown code fences
        if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $m)) {
            $response = $m[1];
        }

        // Try direct parse
        $data = json_decode($response, true);
        if ($data !== null) return $data;

        // Try to find JSON object
        if (preg_match('/\{[\s\S]*\}/', $response, $m)) {
            $data = json_decode($m[0], true);
            if ($data !== null) return $data;
        }

        return null;
    }
}
