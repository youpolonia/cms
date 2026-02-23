<?php
declare(strict_types=1);

/**
 * Shop Social — Auto-post new products to social media
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
}
