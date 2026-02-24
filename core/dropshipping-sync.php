<?php
declare(strict_types=1);

/**
 * Dropshipping Sync — Cron-based stock/price synchronization with suppliers
 * Faza 2: Auto-update product prices & stock from supplier feeds
 */

require_once CMS_ROOT . '/core/dropshipping.php';
require_once CMS_ROOT . '/core/dropshipping-pricing.php';

class DSSync
{
    private static string $logFile = '/tmp/ds-sync.log';

    // ═══════════════════════════════════════════
    //  FULL SYNC (CRON ENTRY POINT)
    // ═══════════════════════════════════════════

    /**
     * Run full sync for all auto-sync enabled product links.
     * Called by cron or manually from dashboard.
     *
     * @return array Stats: checked, updated, errors, skipped
     */
    public static function syncAll(): array
    {
        $pdo = db();
        self::log('=== Starting full sync ===');

        $links = $pdo->query("
            SELECT dl.*, s.name AS supplier_name, s.type AS supplier_type,
                   s.api_key, s.api_base_url, s.settings AS supplier_settings,
                   p.name AS product_name, p.price AS current_price, p.stock AS current_stock
            FROM ds_product_links dl
            JOIN ds_suppliers s ON dl.supplier_id = s.id
            JOIN products p ON dl.product_id = p.id
            WHERE dl.auto_sync = 1 AND s.status = 'active'
            ORDER BY dl.last_sync_at ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $stats = ['checked' => 0, 'price_updated' => 0, 'stock_updated' => 0, 'errors' => 0, 'skipped' => 0];

        foreach ($links as $link) {
            $stats['checked']++;
            $result = self::syncProduct($link);

            if ($result['ok']) {
                if ($result['price_changed']) $stats['price_updated']++;
                if ($result['stock_changed']) $stats['stock_updated']++;
            } else {
                $stats['errors']++;
                self::log("ERROR sync product #{$link['product_id']}: {$result['error']}");
            }

            usleep(300000); // 0.3s between products
        }

        self::log("=== Sync complete: " . json_encode($stats) . " ===");

        // Fire event
        if (function_exists('cms_event')) {
            cms_event('dropshipping.sync.completed', $stats);
        }

        return $stats;
    }

    /**
     * Sync a single product link (price + stock).
     */
    public static function syncProduct(array $link): array
    {
        $pdo = db();
        $type = $link['supplier_type'] ?? 'manual';

        // Fetch updated data from supplier
        $supplierData = null;

        switch ($type) {
            case 'generic_api':
            case 'aliexpress':
            case 'cjdropshipping':
                $supplierData = self::fetchFromApi($link);
                break;

            case 'csv':
                // CSV suppliers don't auto-sync — skip
                return ['ok' => true, 'price_changed' => false, 'stock_changed' => false, 'skipped' => true];

            case 'manual':
                // Manual suppliers: try to re-scrape the product URL
                if (!empty($link['supplier_product_url'])) {
                    $supplierData = self::fetchFromUrl($link);
                } else {
                    return ['ok' => true, 'price_changed' => false, 'stock_changed' => false, 'skipped' => true];
                }
                break;
        }

        if (!$supplierData || !$supplierData['ok']) {
            // Update sync status to error
            $pdo->prepare("UPDATE ds_product_links SET sync_status = 'error', last_sync_at = NOW() WHERE id = ?")
                ->execute([$link['id']]);
            return ['ok' => false, 'error' => $supplierData['error'] ?? 'Failed to fetch supplier data'];
        }

        $priceChanged = false;
        $stockChanged = false;

        // Check price change
        $newSupplierPrice = $supplierData['price'] ?? null;
        if ($newSupplierPrice !== null && abs((float)$newSupplierPrice - (float)$link['supplier_price']) > 0.01) {
            // Recalculate selling price using price rules
            $pricing = \DSPricing::calculatePrice(
                (float)$newSupplierPrice,
                null,
                (int)$link['supplier_id']
            );

            // Update product price
            $pdo->prepare("UPDATE products SET price = ? WHERE id = ?")
                ->execute([$pricing['price'], $link['product_id']]);

            // Update link
            $pdo->prepare("UPDATE ds_product_links SET supplier_price = ?, our_price = ?, profit_margin = ? WHERE id = ?")
                ->execute([$newSupplierPrice, $pricing['price'], $pricing['margin'], $link['id']]);

            $priceChanged = true;
            self::log("Price updated: product #{$link['product_id']} {$link['supplier_price']} → {$newSupplierPrice} (sell: {$pricing['price']})");

            // Alert if margin drops below threshold
            if ($pricing['margin_pct'] < 10) {
                self::log("⚠️ LOW MARGIN ALERT: product #{$link['product_id']} margin only {$pricing['margin_pct']}%");
                if (function_exists('cms_event')) {
                    cms_event('dropshipping.margin.low', [
                        'product_id'  => $link['product_id'],
                        'product_name' => $link['product_name'],
                        'margin_pct'  => $pricing['margin_pct'],
                        'old_price'   => $link['supplier_price'],
                        'new_price'   => $newSupplierPrice,
                    ]);
                }
            }
        }

        // Check stock change
        $newStock = $supplierData['stock'] ?? null;
        if ($newStock !== null) {
            $currentStock = (int)$link['current_stock'];
            if ($newStock !== $currentStock) {
                // If supplier out of stock — set CMS product to 0
                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                    ->execute([$newStock, $link['product_id']]);
                $stockChanged = true;
                self::log("Stock updated: product #{$link['product_id']} {$currentStock} → {$newStock}");

                // Out of stock alert
                if ($newStock <= 0 && $currentStock > 0) {
                    if (function_exists('cms_event')) {
                        cms_event('dropshipping.stock.out', [
                            'product_id'   => $link['product_id'],
                            'product_name' => $link['product_name'],
                            'supplier_id'  => $link['supplier_id'],
                        ]);
                    }
                }
            }
        }

        // Update sync status
        $pdo->prepare("UPDATE ds_product_links SET sync_status = 'synced', last_sync_at = NOW() WHERE id = ?")
            ->execute([$link['id']]);

        return ['ok' => true, 'price_changed' => $priceChanged, 'stock_changed' => $stockChanged];
    }

    // ═══════════════════════════════════════════
    //  FETCH FROM SUPPLIER SOURCES
    // ═══════════════════════════════════════════

    /**
     * Fetch product data from supplier API.
     */
    private static function fetchFromApi(array $link): array
    {
        $baseUrl = $link['api_base_url'] ?? '';
        $apiKey = $link['api_key'] ?? '';
        $productId = $link['supplier_product_id'] ?? '';

        if (empty($baseUrl) || empty($apiKey)) {
            return ['ok' => false, 'error' => 'API not configured'];
        }

        $url = rtrim($baseUrl, '/') . '/products/' . urlencode($productId);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'X-Api-Key: ' . $apiKey,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['ok' => false, 'error' => "HTTP {$httpCode}"];
        }

        $data = json_decode($response, true);
        if (!$data) {
            return ['ok' => false, 'error' => 'Invalid JSON response'];
        }

        return [
            'ok'    => true,
            'price' => $data['price'] ?? $data['sale_price'] ?? null,
            'stock' => isset($data['stock']) ? (int)$data['stock'] : (isset($data['in_stock']) ? ($data['in_stock'] ? 999 : 0) : null),
            'name'  => $data['name'] ?? $data['title'] ?? null,
        ];
    }

    /**
     * Fetch product data by re-scraping the product URL with AI.
     */
    private static function fetchFromUrl(array $link): array
    {
        $url = $link['supplier_product_url'];
        if (empty($url)) {
            return ['ok' => false, 'error' => 'No product URL'];
        }

        // Fetch page
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_HTTPHEADER     => ['Accept-Language: en-US,en;q=0.9'],
        ]);
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$html || $httpCode !== 200) {
            return ['ok' => false, 'error' => "Failed to fetch URL (HTTP {$httpCode})"];
        }

        // Quick extraction — try structured data first (JSON-LD, meta tags)
        $price = self::extractPriceFromHtml($html);
        $stock = self::extractStockFromHtml($html);

        if ($price === null) {
            // Fallback: AI extraction (expensive, use sparingly)
            $price = self::aiExtractPrice($html, $url);
        }

        return [
            'ok'    => true,
            'price' => $price,
            'stock' => $stock,
        ];
    }

    /**
     * Extract price from HTML using structured data (no AI needed).
     */
    private static function extractPriceFromHtml(string $html): ?float
    {
        // Try JSON-LD Product schema
        if (preg_match('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $m)) {
            $jsonBlocks = [];
            preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/si', $html, $allMatches);
            foreach ($allMatches[1] as $jsonStr) {
                $data = json_decode(trim($jsonStr), true);
                if ($data) $jsonBlocks[] = $data;
            }

            foreach ($jsonBlocks as $data) {
                // Direct Product
                if (($data['@type'] ?? '') === 'Product') {
                    $offers = $data['offers'] ?? [];
                    if (isset($offers['price'])) return (float)$offers['price'];
                    if (isset($offers[0]['price'])) return (float)$offers[0]['price'];
                    if (isset($offers['lowPrice'])) return (float)$offers['lowPrice'];
                }
            }
        }

        // Try og:price meta
        if (preg_match('/property=["\']product:price:amount["\'].*?content=["\']([0-9.,]+)["\']/i', $html, $m)) {
            return (float)str_replace(',', '', $m[1]);
        }

        // Try itemprop price
        if (preg_match('/itemprop=["\']price["\'].*?content=["\']([0-9.,]+)["\']/i', $html, $m)) {
            return (float)str_replace(',', '', $m[1]);
        }

        return null;
    }

    /**
     * Extract stock availability from HTML.
     */
    private static function extractStockFromHtml(string $html): ?int
    {
        // JSON-LD availability
        if (preg_match('/InStock/i', $html)) return 999; // In stock (unknown qty)
        if (preg_match('/OutOfStock/i', $html)) return 0;

        // Common patterns
        if (preg_match('/out[- ]?of[- ]?stock/i', $html)) return 0;
        if (preg_match('/sold[- ]?out/i', $html)) return 0;
        if (preg_match('/(\d+)\s*(?:in stock|available|left)/i', $html, $m)) return (int)$m[1];

        return null; // Unknown
    }

    /**
     * AI-based price extraction (fallback when structured data not available).
     */
    private static function aiExtractPrice(string $html, string $url): ?float
    {
        // Trim HTML to manageable size
        $text = strip_tags($html);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = mb_substr($text, 0, 3000);

        if (!function_exists('ai_universal_generate')) {
            require_once CMS_ROOT . '/core/ai_content.php';
        }

        $prompt = "Extract the current product price from this webpage text. "
            . "URL: {$url}\n\nPage text:\n{$text}\n\n"
            . "Return ONLY the numeric price (e.g. 29.99). If you cannot find a price, return 'null'.";

        $response = ai_universal_generate($prompt, ['max_tokens' => 50, 'temperature' => 0]);
        $price = trim($response);

        if ($price === 'null' || !is_numeric($price)) return null;
        return (float)$price;
    }

    // ═══════════════════════════════════════════
    //  SYNC SINGLE PRODUCT (API)
    // ═══════════════════════════════════════════

    /**
     * Sync a single product by link ID.
     */
    public static function syncSingle(int $linkId): array
    {
        $pdo = db();
        $link = $pdo->prepare("
            SELECT dl.*, s.name AS supplier_name, s.type AS supplier_type,
                   s.api_key, s.api_base_url, s.settings AS supplier_settings,
                   p.name AS product_name, p.price AS current_price, p.stock AS current_stock
            FROM ds_product_links dl
            JOIN ds_suppliers s ON dl.supplier_id = s.id
            JOIN products p ON dl.product_id = p.id
            WHERE dl.id = ?
        ");
        $link->execute([$linkId]);
        $link = $link->fetch(\PDO::FETCH_ASSOC);

        if (!$link) {
            return ['ok' => false, 'error' => 'Product link not found'];
        }

        return self::syncProduct($link);
    }

    // ═══════════════════════════════════════════
    //  PRICE MONITORING
    // ═══════════════════════════════════════════

    /**
     * Get products with significant price changes since last sync.
     */
    public static function getPriceAlerts(float $thresholdPct = 10.0): array
    {
        $pdo = db();
        return $pdo->query("
            SELECT dl.*, p.name AS product_name, p.price AS current_sell_price,
                   s.name AS supplier_name,
                   ROUND(((dl.our_price - dl.supplier_price) / dl.supplier_price) * 100, 1) AS margin_pct
            FROM ds_product_links dl
            JOIN products p ON dl.product_id = p.id
            JOIN ds_suppliers s ON dl.supplier_id = s.id
            WHERE dl.sync_status = 'synced'
              AND ((dl.our_price - dl.supplier_price) / NULLIF(dl.supplier_price, 0)) * 100 < {$thresholdPct}
            ORDER BY margin_pct ASC
        ")->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get products that are out of sync (not synced in X hours).
     */
    public static function getStaleProducts(int $hoursThreshold = 24): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT dl.*, p.name AS product_name, s.name AS supplier_name
            FROM ds_product_links dl
            JOIN products p ON dl.product_id = p.id
            JOIN ds_suppliers s ON dl.supplier_id = s.id
            WHERE dl.auto_sync = 1
              AND (dl.last_sync_at IS NULL OR dl.last_sync_at < DATE_SUB(NOW(), INTERVAL ? HOUR))
            ORDER BY dl.last_sync_at ASC
        ");
        $stmt->execute([$hoursThreshold]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // ═══════════════════════════════════════════
    //  LOGGING
    // ═══════════════════════════════════════════

    private static function log(string $message): void
    {
        $line = '[' . date('Y-m-d H:i:s') . '] ' . $message . "\n";
        @file_put_contents(self::$logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
