<?php
declare(strict_types=1);

/**
 * Dropshipping Importer — URL scraper, CSV import, AI pipeline
 * The killer feature: paste URL → AI extracts → rewrites → SEO → removes BG → done
 */

require_once CMS_ROOT . '/core/dropshipping.php';
require_once CMS_ROOT . '/core/dropshipping-pricing.php';

class DSImporter
{
    // ═══════════════════════════════════════════
    //  AI ONE-CLICK IMPORT FROM URL
    // ═══════════════════════════════════════════

    /**
     * Full AI import pipeline from a product URL.
     *
     * Steps:
     * 1. Fetch page content
     * 2. AI extract product data (name, description, price, images, variants)
     * 3. AI rewrite description (unique, SEO)
     * 4. AI generate meta title + description
     * 5. Apply price rule
     * 6. Create product in CMS
     * 7. Create supplier link
     * 8. (Optional) AI remove background, generate ALT text
     *
     * @param string $url Product page URL
     * @param int|null $supplierId Supplier ID
     * @param array $options Options: ai_rewrite, ai_seo, ai_images, price_rule, language, tone
     * @return array ['ok' => bool, 'product_id' => int, 'data' => [...], 'error' => string]
     */
    public static function importFromUrl(string $url, ?int $supplierId = null, array $options = []): array
    {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return ['ok' => false, 'error' => 'Invalid URL'];
        }

        // Create import record
        $importId = \Dropshipping::createImport([
            'source_url'  => $url,
            'source_type' => 'url',
            'supplier_id' => $supplierId,
        ]);

        try {
            // Step 1: Fetch page
            \Dropshipping::updateImport($importId, ['status' => 'processing']);

            $pageContent = self::fetchPage($url);
            if ($pageContent === null) {
                \Dropshipping::updateImport($importId, ['status' => 'failed', 'error_message' => 'Failed to fetch URL']);
                return ['ok' => false, 'error' => 'Failed to fetch the product page. Check URL and try again.'];
            }

            // Step 2: AI extract product data
            $extracted = self::aiExtractProductData($pageContent, $url);
            if (!$extracted['ok']) {
                \Dropshipping::updateImport($importId, ['status' => 'failed', 'error_message' => $extracted['error'] ?? 'AI extraction failed']);
                return ['ok' => false, 'error' => $extracted['error'] ?? 'Failed to extract product data from page'];
            }

            $product = $extracted['data'];
            \Dropshipping::updateImport($importId, ['imported_data' => $product]);

            // Step 3: AI rewrite description (unique)
            $doRewrite = ($options['ai_rewrite'] ?? true);
            $language = $options['language'] ?? 'en';
            $tone = $options['tone'] ?? 'professional';

            if ($doRewrite && !empty($product['description'])) {
                $rewritten = self::aiRewriteForDropshipping($product, $language, $tone);
                if ($rewritten['ok']) {
                    $product['description'] = $rewritten['description'];
                    $product['short_description'] = $rewritten['short_description'] ?? ($product['short_description'] ?? '');
                }
            }

            // Step 4: AI SEO meta
            $doSeo = ($options['ai_seo'] ?? true);
            if ($doSeo) {
                $seoResult = self::aiGenerateSeoMeta($product, $language);
                if ($seoResult['ok']) {
                    $product['meta_title'] = $seoResult['meta_title'];
                    $product['meta_description'] = $seoResult['meta_description'];
                }
            }

            // Step 5: Apply price rule
            $supplierPrice = (float)($product['price'] ?? 0);
            if ($supplierPrice > 0) {
                $pricing = \DSPricing::calculatePrice(
                    $supplierPrice,
                    null,
                    $supplierId,
                    null
                );
                $sellPrice = $pricing['price'];
            } else {
                $sellPrice = 0;
                $pricing = ['rule_name' => 'No price found', 'margin' => 0, 'margin_pct' => 0];
            }

            // Step 6: Create product in CMS
            require_once CMS_ROOT . '/core/shop.php';

            $productData = [
                'name'              => $product['name'] ?? 'Imported Product',
                'slug'              => self::generateSlug($product['name'] ?? 'imported-product'),
                'short_description' => mb_substr($product['short_description'] ?? '', 0, 255),
                'description'       => $product['description'] ?? '',
                'price'             => $sellPrice,
                'image'             => $product['images'][0] ?? '',
                'meta_title'        => $product['meta_title'] ?? '',
                'meta_description'  => $product['meta_description'] ?? '',
                'status'            => 'draft', // Always draft — user reviews before publishing
                'type'              => 'physical',
                'stock'             => -1, // Unlimited (dropship)
            ];

            $productId = \Shop::createProduct($productData);
            if (!$productId) {
                \Dropshipping::updateImport($importId, ['status' => 'failed', 'error_message' => 'Failed to create product']);
                return ['ok' => false, 'error' => 'Failed to create product in CMS'];
            }

            // Step 7: Link to supplier
            if ($supplierId) {
                \Dropshipping::linkProduct($productId, $supplierId, [
                    'supplier_product_url' => $url,
                    'supplier_price'       => $supplierPrice,
                    'supplier_currency'    => $product['currency'] ?? 'USD',
                    'our_price'            => $sellPrice,
                ]);
            }

            // Step 8: AI image processing (optional, async-friendly)
            $aiResults = [
                'rewrite' => $doRewrite,
                'seo'     => $doSeo,
                'pricing' => $pricing,
            ];

            $doImages = ($options['ai_images'] ?? false);
            if ($doImages && !empty($product['images'][0])) {
                // Background removal
                require_once CMS_ROOT . '/core/shop-ai-images.php';
                $bgResult = \ShopAIImages::removeBackground($product['images'][0], $productId);
                $aiResults['remove_bg'] = $bgResult;

                if ($bgResult['ok']) {
                    // Update product image to the BG-removed version
                    $pdo = db();
                    $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")->execute([$bgResult['path'], $productId]);
                    $productData['image'] = $bgResult['path'];
                }

                // ALT text
                $altResult = \ShopAIImages::generateAltText(
                    $productData['image'],
                    $product['name'] ?? ''
                );
                $aiResults['alt_text'] = $altResult;
            }

            // Update import record
            \Dropshipping::updateImport($importId, [
                'status'       => 'completed',
                'product_id'   => $productId,
                'ai_processed' => 1,
                'ai_results'   => $aiResults,
            ]);

            return [
                'ok'         => true,
                'product_id' => $productId,
                'import_id'  => $importId,
                'data'       => [
                    'name'             => $productData['name'],
                    'supplier_price'   => $supplierPrice,
                    'sell_price'       => $sellPrice,
                    'margin'           => $pricing['margin'] ?? 0,
                    'margin_pct'       => $pricing['margin_pct'] ?? 0,
                    'rule_name'        => $pricing['rule_name'] ?? '',
                    'image'            => $productData['image'],
                    'images_count'     => count($product['images'] ?? []),
                    'ai_rewritten'     => $doRewrite,
                    'ai_seo'           => $doSeo,
                    'ai_images'        => $doImages,
                ],
            ];

        } catch (\Exception $e) {
            \Dropshipping::updateImport($importId, ['status' => 'failed', 'error_message' => $e->getMessage()]);
            return ['ok' => false, 'error' => 'Import error: ' . $e->getMessage()];
        }
    }

    /**
     * Batch import from multiple URLs.
     */
    public static function importBatch(array $urls, ?int $supplierId = null, array $options = []): array
    {
        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($urls as $url) {
            $url = trim($url);
            if ($url === '') continue;

            $result = self::importFromUrl($url, $supplierId, $options);
            if ($result['ok']) {
                $success++;
            } else {
                $failed++;
            }
            $results[] = array_merge(['url' => $url], $result);

            usleep(1000000); // 1s between imports (rate limit AI calls)
        }

        return [
            'ok'      => true,
            'total'   => count($results),
            'success' => $success,
            'failed'  => $failed,
            'results' => $results,
        ];
    }

    // ═══════════════════════════════════════════
    //  CSV IMPORT
    // ═══════════════════════════════════════════

    /**
     * Import products from CSV data.
     *
     * @param string $csvContent Raw CSV content
     * @param array $columnMap Maps CSV columns to product fields: ['0' => 'name', '1' => 'price', ...]
     * @param int|null $supplierId Supplier ID
     * @param array $options Import options
     * @return array Results
     */
    public static function importFromCsv(string $csvContent, array $columnMap, ?int $supplierId = null, array $options = []): array
    {
        $lines = str_getcsv_lines($csvContent);
        if (empty($lines)) {
            return ['ok' => false, 'error' => 'Empty or invalid CSV'];
        }

        // Skip header row
        $skipHeader = ($options['skip_header'] ?? true);
        if ($skipHeader) {
            array_shift($lines);
        }

        require_once CMS_ROOT . '/core/shop.php';

        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($lines as $lineNum => $row) {
            if (empty($row) || count($row) < 2) continue;

            $product = [];
            foreach ($columnMap as $colIdx => $fieldName) {
                $product[$fieldName] = $row[(int)$colIdx] ?? '';
            }

            $name = trim($product['name'] ?? '');
            if ($name === '') {
                $failed++;
                $results[] = ['line' => $lineNum + 2, 'status' => 'error', 'error' => 'Missing product name'];
                continue;
            }

            $supplierPrice = (float)($product['price'] ?? 0);
            $sellPrice = $supplierPrice;
            if ($supplierPrice > 0) {
                $pricing = \DSPricing::calculatePrice($supplierPrice, null, $supplierId);
                $sellPrice = $pricing['price'];
            }

            $productData = [
                'name'              => $name,
                'slug'              => self::generateSlug($name),
                'short_description' => mb_substr($product['short_description'] ?? '', 0, 255),
                'description'       => $product['description'] ?? '',
                'price'             => $sellPrice,
                'image'             => $product['image'] ?? '',
                'sku'               => $product['sku'] ?? '',
                'status'            => 'draft',
                'type'              => 'physical',
                'stock'             => -1,
            ];

            $productId = \Shop::createProduct($productData);
            if ($productId) {
                if ($supplierId) {
                    \Dropshipping::linkProduct($productId, $supplierId, [
                        'supplier_price' => $supplierPrice,
                        'our_price'      => $sellPrice,
                        'supplier_sku'   => $product['sku'] ?? null,
                    ]);
                }
                $success++;
                $results[] = ['line' => $lineNum + 2, 'status' => 'ok', 'product_id' => $productId, 'name' => $name];
            } else {
                $failed++;
                $results[] = ['line' => $lineNum + 2, 'status' => 'error', 'error' => 'Failed to create product'];
            }
        }

        return [
            'ok'      => true,
            'total'   => count($results),
            'success' => $success,
            'failed'  => $failed,
            'results' => $results,
        ];
    }

    // ═══════════════════════════════════════════
    //  AI METHODS
    // ═══════════════════════════════════════════

    /**
     * Fetch page content from URL.
     */
    private static function fetchPage(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
        ]);

        $html = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($html === false || $status < 200 || $status >= 400) {
            return null;
        }

        // Strip scripts, styles, and limit content for AI
        $html = preg_replace('/<script[^>]*>.*?<\/script>/si', '', $html);
        $html = preg_replace('/<style[^>]*>.*?<\/style>/si', '', $html);
        $html = preg_replace('/<!--.*?-->/s', '', $html);

        // Limit to reasonable size for AI (keep first ~15K chars)
        if (mb_strlen($html) > 15000) {
            $html = mb_substr($html, 0, 15000);
        }

        return $html;
    }

    /**
     * AI extract product data from HTML page content.
     */
    private static function aiExtractProductData(string $html, string $url): array
    {
        require_once CMS_ROOT . '/core/ai_content.php';

        $aiSettings = ai_config_load_full();
        $provider = '';
        $model = '';
        if (!empty($aiSettings['providers'])) {
            foreach ($aiSettings['providers'] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? '';
                    break;
                }
            }
        }
        if (empty($provider)) {
            return ['ok' => false, 'error' => 'No AI provider configured'];
        }

        $systemPrompt = 'You are an expert e-commerce data extraction assistant. Extract product information from web pages. Return ONLY valid JSON, no markdown.';

        $userPrompt = <<<PROMPT
Extract product information from this e-commerce page. Source URL: {$url}

HTML content:
{$html}

Return ONLY valid JSON with these exact keys:
{
  "name": "Product name (clean, no store name prefix)",
  "description": "Full product description in HTML (keep useful <p>, <ul>, <li> tags). Clean up, remove store-specific text, make it generic.",
  "short_description": "1-2 sentence summary, max 255 chars",
  "price": 29.99,
  "currency": "USD",
  "images": ["https://image-url-1.jpg", "https://image-url-2.jpg"],
  "variants": [
    {"name": "Color", "options": ["Red", "Blue", "Black"]},
    {"name": "Size", "options": ["S", "M", "L", "XL"]}
  ],
  "specifications": {"Material": "Cotton", "Weight": "200g"},
  "category": "Best matching category name",
  "brand": "Brand name if found",
  "sku": "SKU if found",
  "shipping_info": "Shipping details if found",
  "rating": 4.5,
  "reviews_count": 123
}

Rules:
- Extract ALL available images (product photos only, not icons/logos)
- Price must be a number (no currency symbols)
- If info is not found, use null or empty string
- Clean the description: remove store-specific content, policies, shipping info
- Keep the description informative and product-focused
PROMPT;

        $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
            'max_tokens'  => 3000,
            'temperature' => 0.2,
        ]);

        if (empty($result['ok'])) {
            return ['ok' => false, 'error' => $result['error'] ?? 'AI extraction failed'];
        }

        $content = $result['content'] ?? '';
        $parsed = self::parseJson($content);

        if ($parsed === null) {
            return ['ok' => false, 'error' => 'Failed to parse AI extraction result'];
        }

        // Validate minimum data
        if (empty($parsed['name'])) {
            return ['ok' => false, 'error' => 'AI could not extract product name from page'];
        }

        // Normalize
        $parsed['price'] = is_numeric($parsed['price'] ?? null) ? (float)$parsed['price'] : 0;
        $parsed['images'] = is_array($parsed['images'] ?? null) ? $parsed['images'] : [];
        $parsed['variants'] = is_array($parsed['variants'] ?? null) ? $parsed['variants'] : [];

        return ['ok' => true, 'data' => $parsed];
    }

    /**
     * AI rewrite product description for unique dropshipping listing.
     */
    private static function aiRewriteForDropshipping(array $product, string $language = 'en', string $tone = 'professional'): array
    {
        require_once CMS_ROOT . '/core/ai_content.php';

        $aiSettings = ai_config_load_full();
        $provider = '';
        $model = '';
        if (!empty($aiSettings['providers'])) {
            foreach ($aiSettings['providers'] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? '';
                    break;
                }
            }
        }
        if (empty($provider)) {
            return ['ok' => false, 'error' => 'No AI provider configured'];
        }

        $name = $product['name'] ?? '';
        $description = $product['description'] ?? '';
        $category = $product['category'] ?? '';

        $langNames = ['en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French', 'es' => 'Spanish'];
        $langLabel = $langNames[$language] ?? $language;

        $systemPrompt = 'You are a world-class e-commerce copywriter specializing in creating unique, SEO-optimized product descriptions. Return ONLY valid JSON.';

        $userPrompt = <<<PROMPT
Rewrite this product listing to make it COMPLETELY UNIQUE (no duplicate content with source). Use a {$tone} tone. Write in {$langLabel}.

Product name: {$name}
Category: {$category}
Original description: {$description}

Return ONLY valid JSON:
{
  "description": "Completely rewritten HTML description (2-4 paragraphs with <p> tags + feature <ul><li> list). Unique wording, SEO-optimized, compelling. Include benefits, not just features.",
  "short_description": "New compelling 1-2 sentence summary (max 255 chars)"
}

CRITICAL: Make it COMPLETELY different from the original. Same information, totally different words. A plagiarism checker should find 0% match.
PROMPT;

        $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
            'max_tokens'  => 2000,
            'temperature' => 0.7,
        ]);

        if (empty($result['ok'])) {
            return ['ok' => false, 'error' => 'AI rewrite failed'];
        }

        $parsed = self::parseJson($result['content'] ?? '');
        if ($parsed === null || empty($parsed['description'])) {
            return ['ok' => false, 'error' => 'Failed to parse AI rewrite'];
        }

        return [
            'ok'                => true,
            'description'       => $parsed['description'],
            'short_description' => mb_substr($parsed['short_description'] ?? '', 0, 255),
        ];
    }

    /**
     * AI generate SEO meta for imported product.
     */
    private static function aiGenerateSeoMeta(array $product, string $language = 'en'): array
    {
        require_once CMS_ROOT . '/core/ai_content.php';

        $aiSettings = ai_config_load_full();
        $provider = '';
        $model = '';
        if (!empty($aiSettings['providers'])) {
            foreach ($aiSettings['providers'] as $pName => $pConfig) {
                if (!empty($pConfig['enabled']) && !empty($pConfig['api_key'])) {
                    $provider = $pName;
                    $model = $pConfig['default_model'] ?? '';
                    break;
                }
            }
        }
        if (empty($provider)) {
            return ['ok' => false];
        }

        $name = $product['name'] ?? '';
        $desc = strip_tags($product['description'] ?? '');

        $systemPrompt = 'You are an SEO expert. Return ONLY valid JSON.';
        $userPrompt = "Generate SEO meta for this product:\nName: {$name}\nDescription: {$desc}\n\nReturn JSON: {\"meta_title\": \"max 60 chars\", \"meta_description\": \"max 160 chars, include CTA\"}";

        $result = ai_universal_generate($provider, $model, $systemPrompt, $userPrompt, [
            'max_tokens' => 500,
            'temperature' => 0.5,
        ]);

        if (empty($result['ok'])) return ['ok' => false];

        $parsed = self::parseJson($result['content'] ?? '');
        if ($parsed === null) return ['ok' => false];

        return [
            'ok'               => true,
            'meta_title'       => mb_substr($parsed['meta_title'] ?? $name, 0, 60),
            'meta_description' => mb_substr($parsed['meta_description'] ?? '', 0, 160),
        ];
    }

    // ═══════════════════════════════════════════
    //  HELPERS
    // ═══════════════════════════════════════════

    private static function parseJson(string $text): ?array
    {
        $text = trim($text);
        $data = json_decode($text, true);
        if (is_array($data)) return $data;

        // Strip markdown code blocks
        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?\s*```/s', $text, $m)) {
            $data = json_decode(trim($m[1]), true);
            if (is_array($data)) return $data;
        }

        // Find JSON object
        if (preg_match('/\{[\s\S]*\}/m', $text, $m)) {
            $data = json_decode($m[0], true);
            if (is_array($data)) return $data;
        }

        return null;
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if (mb_strlen($slug) > 60) {
            $slug = mb_substr($slug, 0, 60);
            $slug = rtrim($slug, '-');
        }
        // Add random suffix for uniqueness
        $slug .= '-' . substr(md5(uniqid()), 0, 4);
        return $slug;
    }
}

/**
 * Helper: parse CSV content into array of rows.
 */
function str_getcsv_lines(string $csv): array
{
    $lines = [];
    $rows = explode("\n", str_replace("\r\n", "\n", $csv));
    foreach ($rows as $row) {
        $row = trim($row);
        if ($row === '') continue;
        $lines[] = str_getcsv($row);
    }
    return $lines;
}
