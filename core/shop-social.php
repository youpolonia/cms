<?php
declare(strict_types=1);

/**
 * Shop Social — Social media integration for the shop
 * Auto-posting, sharing, engagement tracking
 * Jessie AI CMS
 */

class ShopSocial
{
    /**
     * Auto-post when a new product is created
     */
    public static function autoPostNewProduct(array $product): void
    {
        try {
            require_once CMS_ROOT . '/core/shop.php';

            $productId = (int)($product['id'] ?? 0);
            if ($productId < 1) {
                return;
            }

            $fullProduct = \Shop::getProduct($productId);
            if (!$fullProduct) {
                return;
            }

            $name = $fullProduct['name'] ?? '';
            $price = \Shop::formatPrice((float)($fullProduct['price'] ?? 0));
            $shortDesc = $fullProduct['short_description'] ?? '';
            $slug = $fullProduct['slug'] ?? '';
            $siteUrl = rtrim(get_setting('site_url', ''), '/');
            $productUrl = $siteUrl . '/shop/' . $slug;

            // Build default post content
            $postContent = "🆕 New in our shop! {$name} — {$price}";
            if ($shortDesc) {
                $postContent .= "\n" . mb_substr(strip_tags($shortDesc), 0, 200);
            }
            $postContent .= "\n🛒 Shop now: {$productUrl}";

            // Try AI-enhanced generation via SocialManager
            $aiContent = null;
            if (file_exists(CMS_ROOT . '/core/social_manager.php')) {
                require_once CMS_ROOT . '/core/social_manager.php';
                $result = \SocialManager::generateFromText(
                    $name,
                    $shortDesc ?: ($fullProduct['description'] ?? $name),
                    $productUrl
                );
                if (!empty($result['posts'])) {
                    $aiContent = $result['posts'];
                }
            }

            // Schedule for next day 10:00 AM or post based on setting
            $scheduleTime = get_setting('shop_social_auto_post_time', 'schedule');
            if ($scheduleTime === 'immediate') {
                $scheduledAt = date('Y-m-d H:i:s');
            } else {
                $tomorrow = new \DateTime('tomorrow 10:00:00');
                $scheduledAt = $tomorrow->format('Y-m-d H:i:s');
            }

            $pdo = db();
            $platforms = ['twitter', 'facebook', 'linkedin', 'instagram'];

            foreach ($platforms as $platform) {
                $content = $postContent;
                if ($aiContent && isset($aiContent[$platform])) {
                    $content = $aiContent[$platform];
                }

                $stmt = $pdo->prepare(
                    "INSERT INTO social_posts (platform, content, link_url, status, scheduled_at, created_at) 
                     VALUES (?, ?, ?, 'scheduled', ?, NOW())"
                );
                $stmt->execute([$platform, $content, $productUrl, $scheduledAt]);
            }

            if (function_exists('cms_event')) {
                cms_event('shop.social.product_posted', [
                    'product_id' => $productId,
                    'product_name' => $name,
                    'scheduled_at' => $scheduledAt,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('ShopSocial::autoPostNewProduct error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-post a sale/discount announcement
     */
    public static function autoPostSale(array $coupon): void
    {
        try {
            $code = $coupon['code'] ?? '';
            $type = $coupon['type'] ?? 'percentage';
            $value = (float)($coupon['value'] ?? 0);
            $validUntil = $coupon['valid_until'] ?? '';
            $siteUrl = rtrim(get_setting('site_url', ''), '/');

            $discountText = $type === 'percentage'
                ? "{$value}% OFF"
                : (($type === 'free_shipping') ? 'FREE SHIPPING' : number_format($value, 2) . ' OFF');

            $content = "🔥 SALE ALERT! Use code {$code} for {$discountText}!";
            if ($validUntil) {
                $content .= "\n⏰ Hurry — expires " . date('M j', strtotime($validUntil));
            }
            $content .= "\n🛒 Shop now: {$siteUrl}/shop";

            $pdo = db();
            $platforms = ['twitter', 'facebook', 'instagram'];
            foreach ($platforms as $platform) {
                $pdo->prepare(
                    "INSERT INTO social_posts (platform, content, link_url, status, scheduled_at, created_at)
                     VALUES (?, ?, ?, 'scheduled', NOW(), NOW())"
                )->execute([$platform, $content, "{$siteUrl}/shop"]);
            }
        } catch (\Throwable $e) {
            error_log('ShopSocial::autoPostSale error: ' . $e->getMessage());
        }
    }

    /**
     * Generate social share URLs for a product (frontend sharing buttons)
     */
    public static function getShareUrls(array $product): array
    {
        $siteUrl = rtrim(get_setting('site_url', ''), '/');
        $productUrl = $siteUrl . '/shop/' . ($product['slug'] ?? '');
        $name = rawurlencode($product['name'] ?? '');
        $desc = rawurlencode(mb_substr(strip_tags($product['short_description'] ?? $product['name'] ?? ''), 0, 160));

        return [
            'facebook'  => "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($productUrl),
            'twitter'   => "https://twitter.com/intent/tweet?text={$name}&url=" . rawurlencode($productUrl),
            'linkedin'  => "https://www.linkedin.com/shareArticle?mini=true&url=" . rawurlencode($productUrl) . "&title={$name}&summary={$desc}",
            'pinterest' => "https://pinterest.com/pin/create/button/?url=" . rawurlencode($productUrl) . "&description={$name}",
            'whatsapp'  => "https://wa.me/?text={$name}%20" . rawurlencode($productUrl),
            'telegram'  => "https://t.me/share/url?url=" . rawurlencode($productUrl) . "&text={$name}",
            'email'     => "mailto:?subject={$name}&body={$desc}%0A%0A" . rawurlencode($productUrl),
        ];
    }

    /**
     * Track a social share event
     */
    public static function trackShare(int $productId, string $platform): void
    {
        try {
            if (file_exists(CMS_ROOT . '/core/shop-analytics.php')) {
                require_once CMS_ROOT . '/core/shop-analytics.php';
                \ShopAnalytics::track('social_share', $productId, null, null, [
                    'platform' => $platform,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('ShopSocial::trackShare error: ' . $e->getMessage());
        }
    }

    /**
     * Get social share stats for a product
     */
    public static function getShareStats(int $productId): array
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT JSON_UNQUOTE(JSON_EXTRACT(meta, '$.platform')) AS platform, COUNT(*) AS shares
                 FROM shop_analytics
                 WHERE event_type = 'social_share' AND product_id = ?
                 GROUP BY platform
                 ORDER BY shares DESC"
            );
            $stmt->execute([$productId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $total = 0;
            $byPlatform = [];
            foreach ($rows as $row) {
                $byPlatform[$row['platform']] = (int)$row['shares'];
                $total += (int)$row['shares'];
            }

            return [
                'total' => $total,
                'by_platform' => $byPlatform,
            ];
        } catch (\Throwable $e) {
            return ['total' => 0, 'by_platform' => []];
        }
    }

    /**
     * Get top shared products
     */
    public static function getTopShared(int $limit = 10, int $days = 30): array
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare(
                "SELECT sa.product_id, p.name AS product_name, p.slug AS product_slug,
                        COUNT(*) AS share_count
                 FROM shop_analytics sa
                 LEFT JOIN products p ON sa.product_id = p.id
                 WHERE sa.event_type = 'social_share'
                   AND sa.created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                   AND sa.product_id IS NOT NULL
                 GROUP BY sa.product_id, p.name, p.slug
                 ORDER BY share_count DESC
                 LIMIT ?"
            );
            $stmt->execute([$days, $limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            return [];
        }
    }
}

// ─── Event: Auto-post new products ───
if (function_exists('cms_on')) {
    cms_on('shop.product.created', function ($data) {
        $autoPost = get_setting('shop_social_auto_post', '0');
        if ($autoPost === '1') {
            ShopSocial::autoPostNewProduct($data);
        }
    });
    cms_on('shop.coupon.created', function ($data) {
        $autoPost = get_setting('shop_social_auto_post_sales', '0');
        if ($autoPost === '1') {
            ShopSocial::autoPostSale($data);
        }
    });
}
