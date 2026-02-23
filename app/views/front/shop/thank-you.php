<?php
$items = json_decode($order['items'] ?? '[]', true);
if (!is_array($items)) $items = [];
?>
<div class="thankyou-page">
    <div class="thankyou-card">
        <div class="thankyou-icon">🎉</div>
        <h1 class="thankyou-title">Thank You!</h1>
        <p class="thankyou-subtitle">Your order has been placed successfully.</p>

        <div class="order-summary">
            <div class="order-summary-grid">
                <div class="order-summary-item">
                    <div class="order-summary-label">Order Number</div>
                    <div class="order-summary-value"><?= htmlspecialchars($order['order_number'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="order-summary-item">
                    <div class="order-summary-label">Total</div>
                    <div class="order-summary-value order-summary-total"><?= \Shop::formatPrice((float)($order['total'] ?? 0)) ?></div>
                </div>
                <div class="order-summary-item">
                    <div class="order-summary-label">Status</div>
                    <div class="order-summary-value order-summary-status"><?= htmlspecialchars(ucfirst($order['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div class="order-summary-item">
                    <div class="order-summary-label">Payment</div>
                    <div class="order-summary-value"><?= htmlspecialchars(ucfirst($order['payment_method'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>

            <?php if (!empty($items)): ?>
            <div class="order-items">
                <div class="order-items-label">Items Ordered:</div>
                <?php foreach ($items as $item): ?>
                <div class="order-items-row">
                    <span><?= htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8') ?> × <?= (int)($item['quantity'] ?? 1) ?></span>
                    <span class="order-items-price"><?= \Shop::formatPrice((float)($item['line_total'] ?? 0)) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php
        // Check for digital downloads
        require_once CMS_ROOT . '/core/shop-digital.php';
        $downloads = \ShopDigital::getOrderDownloads((int)$order['id']);
        if (!empty($downloads)):
        ?>
        <div class="download-links">
            <div class="download-links-title">💾 Your Digital Downloads</div>
            <p class="download-links-desc">Your digital products are ready to download. Links expire after the set period.</p>
            <?php foreach ($downloads as $dl): ?>
            <div class="download-item">
                <span class="download-item-name"><?= htmlspecialchars($dl['product_name'] ?? 'Digital Product', ENT_QUOTES, 'UTF-8') ?></span>
                <a href="/shop/download/<?= htmlspecialchars($dl['token'], ENT_QUOTES, 'UTF-8') ?>" class="download-item-btn">⬇️ Download</a>
            </div>
            <div class="download-item-meta">
                Downloads: <?= (int)$dl['downloads_count'] ?>/<?= (int)$dl['max_downloads'] ?>
                · Expires: <?= date('M j, Y H:i', strtotime($dl['expires_at'])) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <p class="thankyou-email">A confirmation has been sent to <strong><?= htmlspecialchars($order['customer_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></p>
        <a href="/shop" class="thankyou-btn">Continue Shopping</a>
    </div>
</div>

<style>
/* Thank you page fallback styles */
.thankyou-page { max-width: 700px; margin: 0 auto; padding: 60px 20px; text-align: center; }
.thankyou-icon { font-size: 4rem; margin-bottom: 16px; }
.thankyou-title { font-size: 2rem; margin-bottom: 8px; }
.thankyou-subtitle { color: #666; font-size: 1.1rem; margin-bottom: 30px; }
.order-summary { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; text-align: left; margin-bottom: 30px; }
.order-summary-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px; }
.order-summary-label { font-size: .8rem; color: #999; text-transform: uppercase; margin-bottom: 4px; }
.order-summary-value { font-weight: 700; font-size: 1.1rem; }
.order-summary-total { color: var(--primary-color, #6366f1); }
.order-summary-status { color: #f59e0b; }
.order-items { border-top: 1px solid #e2e8f0; padding-top: 16px; }
.order-items-label { font-size: .85rem; font-weight: 600; margin-bottom: 10px; }
.order-items-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: .85rem; }
.order-items-price { font-weight: 600; }
.download-links { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 24px; text-align: left; margin-bottom: 30px; }
.download-links-title { font-size: .85rem; font-weight: 700; margin-bottom: 12px; color: #166534; }
.download-links-desc { font-size: .8rem; color: #15803d; margin-bottom: 16px; }
.download-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #fff; border: 1px solid #bbf7d0; border-radius: 8px; margin-bottom: 8px; }
.download-item-name { font-weight: 600; font-size: .9rem; }
.download-item-btn { display: inline-block; padding: 6px 16px; background: var(--primary-color, #6366f1); color: #fff; border-radius: 6px; text-decoration: none; font-size: .85rem; font-weight: 600; }
.download-item-meta { font-size: .75rem; color: #6b7280; margin-bottom: 12px; padding-left: 14px; }
.thankyou-email { color: #666; font-size: .9rem; margin-bottom: 20px; }
.thankyou-btn { display: inline-block; padding: 12px 24px; background: var(--primary-color, #6366f1); color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; }
</style>
