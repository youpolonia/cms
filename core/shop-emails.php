<?php
declare(strict_types=1);

/**
 * ShopEmails — Transactional email templates for the Shop module
 * Sends via cms_send_email() from core/mailer.php
 *
 * @package JessieCMS
 * @since 2026-02-23
 */

require_once CMS_ROOT . '/core/mailer.php';
require_once CMS_ROOT . '/core/shop.php';

class ShopEmails
{
    // ─── ORDER CONFIRMATION (to customer) ───

    public static function sendOrderConfirmation(int $orderId): bool
    {
        if (get_setting('shop_email_order_confirm', '1') !== '1') {
            return false;
        }

        $order = Shop::getOrder($orderId);
        if (!$order || empty($order['customer_email'])) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $items = json_decode($order['items'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $subject = "Order Confirmed — #{$order['order_number']}";

        $itemsHtml = '';
        foreach ($items as $item) {
            $img = !empty($item['image'])
                ? '<img src="' . self::e($item['image']) . '" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:6px;">'
                : '<div style="width:50px;height:50px;background:#f1f5f9;border-radius:6px;text-align:center;line-height:50px;font-size:20px;">📦</div>';
            $itemsHtml .= '<tr>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;">' . $img . '</td>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;font-weight:500;color:#1e293b;">' . self::e($item['name'] ?? 'Product') . '</td>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;text-align:center;color:#64748b;">' . (int)($item['quantity'] ?? 1) . '</td>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:600;color:#1e293b;">' . self::money((float)($item['line_total'] ?? 0), $order['currency'] ?? '') . '</td>
            </tr>';
        }

        $totalsHtml = self::buildTotals($order);
        $billing = json_decode($order['billing_address'] ?? '{}', true);
        $billingHtml = '';
        if (is_array($billing) && !empty($billing)) {
            $addr = implode(', ', array_filter([
                $billing['line1'] ?? '',
                $billing['line2'] ?? '',
                $billing['city'] ?? '',
                $billing['state'] ?? '',
                $billing['zip'] ?? '',
                $billing['country'] ?? '',
            ]));
            if ($addr) {
                $billingHtml = '
                <div style="margin-top:24px;padding:16px;background:#f8fafc;border-radius:8px;">
                    <h3 style="margin:0 0 8px;font-size:14px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Billing Address</h3>
                    <p style="margin:0;color:#1e293b;font-size:14px;">' . self::e($order['customer_name'] ?? '') . '<br>' . self::e($addr) . '</p>
                </div>';
            }
        }

        $body = self::wrap($shopName, '
            <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">Thank you for your order! 🎉</h1>
            <p style="margin:0 0 24px;color:#64748b;font-size:15px;">Hi ' . self::e($order['customer_name'] ?? 'there') . ', we\'ve received your order and it\'s being processed.</p>

            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:16px;margin-bottom:24px;">
                <p style="margin:0;font-size:14px;color:#166534;"><strong>Order Number:</strong> ' . self::e($order['order_number']) . '<br>
                <strong>Date:</strong> ' . date('M j, Y', strtotime($order['created_at'] ?? 'now')) . '</p>
            </div>

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.05em;width:60px;"></th>
                        <th style="padding:10px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.05em;">Product</th>
                        <th style="padding:10px 8px;text-align:center;font-size:11px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.05em;">Qty</th>
                        <th style="padding:10px 8px;text-align:right;font-size:11px;text-transform:uppercase;color:#94a3b8;letter-spacing:0.05em;">Total</th>
                    </tr>
                </thead>
                <tbody>' . $itemsHtml . '</tbody>
            </table>

            ' . $totalsHtml . $billingHtml . '

            <p style="margin:24px 0 0;color:#64748b;font-size:13px;">If you have any questions about your order, simply reply to this email.</p>
        ');

        return cms_send_email($order['customer_email'], $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── ORDER NOTIFICATION (to admin) ───

    public static function sendOrderNotificationToAdmin(int $orderId): bool
    {
        if (get_setting('shop_email_admin_notify', '1') !== '1') {
            return false;
        }

        $adminEmail = get_setting('shop_notification_email', get_setting('admin_email', ''));
        if (empty($adminEmail)) {
            return false;
        }

        $order = Shop::getOrder($orderId);
        if (!$order) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $siteUrl = get_setting('site_url', '');
        $items = json_decode($order['items'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $subject = "New Order #{$order['order_number']} — " . self::money((float)($order['total'] ?? 0), $order['currency'] ?? '');

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= '<tr>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;color:#1e293b;">' . self::e($item['name'] ?? 'Product') . '</td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;color:#64748b;">' . (int)($item['quantity'] ?? 1) . '</td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:right;color:#1e293b;font-weight:600;">' . self::money((float)($item['line_total'] ?? 0), $order['currency'] ?? '') . '</td>
            </tr>';
        }

        $adminLink = $siteUrl ? $siteUrl . '/admin/shop/orders/' . (int)$order['id'] : '#';

        $body = self::wrap($shopName, '
            <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">New Order Received! 🛍️</h1>
            <p style="margin:0 0 24px;color:#64748b;font-size:15px;">A new order has been placed on your store.</p>

            <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:16px;margin-bottom:24px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                    <tr><td style="padding:4px 0;color:#64748b;">Order Number:</td><td style="padding:4px 0;font-weight:600;color:#1e293b;text-align:right;">' . self::e($order['order_number']) . '</td></tr>
                    <tr><td style="padding:4px 0;color:#64748b;">Customer:</td><td style="padding:4px 0;font-weight:600;color:#1e293b;text-align:right;">' . self::e($order['customer_name'] ?? 'N/A') . '</td></tr>
                    <tr><td style="padding:4px 0;color:#64748b;">Email:</td><td style="padding:4px 0;color:#1e293b;text-align:right;">' . self::e($order['customer_email'] ?? '') . '</td></tr>
                    <tr><td style="padding:4px 0;color:#64748b;">Total:</td><td style="padding:4px 0;font-weight:700;color:#6366f1;text-align:right;font-size:16px;">' . self::money((float)($order['total'] ?? 0), $order['currency'] ?? '') . '</td></tr>
                </table>
            </div>

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:24px;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;">Product</th>
                        <th style="padding:10px 8px;text-align:center;font-size:11px;text-transform:uppercase;color:#94a3b8;">Qty</th>
                        <th style="padding:10px 8px;text-align:right;font-size:11px;text-transform:uppercase;color:#94a3b8;">Total</th>
                    </tr>
                </thead>
                <tbody>' . $itemsHtml . '</tbody>
            </table>

            <div style="text-align:center;">
                <a href="' . self::e($adminLink) . '" style="display:inline-block;background:#6366f1;color:#ffffff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">View Order in Admin</a>
            </div>
        ');

        return cms_send_email($adminEmail, $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── STATUS UPDATE (to customer) ───

    public static function sendStatusUpdate(int $orderId, string $newStatus): bool
    {
        if (get_setting('shop_email_status_update', '1') !== '1') {
            return false;
        }

        $order = Shop::getOrder($orderId);
        if (!$order || empty($order['customer_email'])) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');

        $statusConfig = [
            'processing' => ['subject' => 'Order Processing', 'icon' => '⚙️', 'color' => '#3b82f6', 'bgColor' => '#eff6ff', 'borderColor' => '#bfdbfe', 'message' => 'Great news! Your order is now being processed and will be shipped soon.'],
            'shipped'    => ['subject' => 'Order Shipped', 'icon' => '🚚', 'color' => '#8b5cf6', 'bgColor' => '#f5f3ff', 'borderColor' => '#ddd6fe', 'message' => 'Your order has been shipped and is on its way to you!'],
            'delivered'  => ['subject' => 'Order Delivered', 'icon' => '✅', 'color' => '#10b981', 'bgColor' => '#f0fdf4', 'borderColor' => '#bbf7d0', 'message' => 'Your order has been delivered. We hope you enjoy your purchase!'],
            'cancelled'  => ['subject' => 'Order Cancelled', 'icon' => '❌', 'color' => '#ef4444', 'bgColor' => '#fef2f2', 'borderColor' => '#fecaca', 'message' => 'Your order has been cancelled. If you did not request this, please contact us immediately.'],
            'refunded'   => ['subject' => 'Order Refunded', 'icon' => '💰', 'color' => '#64748b', 'bgColor' => '#f8fafc', 'borderColor' => '#e2e8f0', 'message' => 'Your order has been refunded. The refund will appear in your account within 5-10 business days.'],
            'pending'    => ['subject' => 'Order Received', 'icon' => '📋', 'color' => '#f59e0b', 'bgColor' => '#fffbeb', 'borderColor' => '#fde68a', 'message' => 'Your order has been received and is awaiting processing.'],
        ];

        $cfg = $statusConfig[$newStatus] ?? $statusConfig['pending'];
        $subject = "{$cfg['subject']} — #{$order['order_number']}";

        $trackingHtml = '';
        if ($newStatus === 'shipped' && !empty($order['tracking_number'])) {
            $trackingHtml = '
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:16px;margin-top:16px;">
                <p style="margin:0;font-size:14px;color:#64748b;">Tracking Number:</p>
                <p style="margin:4px 0 0;font-size:18px;font-weight:700;color:#1e293b;letter-spacing:0.05em;">' . self::e($order['tracking_number']) . '</p>
            </div>';
        }

        $body = self::wrap($shopName, '
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:48px;margin-bottom:12px;">' . $cfg['icon'] . '</div>
                <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">' . self::e($cfg['subject']) . '</h1>
                <p style="margin:0;color:#64748b;font-size:15px;">Order #' . self::e($order['order_number']) . '</p>
            </div>

            <div style="background:' . $cfg['bgColor'] . ';border:1px solid ' . $cfg['borderColor'] . ';border-radius:8px;padding:20px;margin:24px 0;">
                <p style="margin:0;color:' . $cfg['color'] . ';font-size:15px;line-height:1.6;">' . self::e($cfg['message']) . '</p>
            </div>

            ' . $trackingHtml . '

            <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-top:24px;">
                <table width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                    <tr><td style="padding:4px 0;color:#64748b;">Order Number:</td><td style="padding:4px 0;font-weight:600;color:#1e293b;text-align:right;">' . self::e($order['order_number']) . '</td></tr>
                    <tr><td style="padding:4px 0;color:#64748b;">Date:</td><td style="padding:4px 0;color:#1e293b;text-align:right;">' . date('M j, Y', strtotime($order['created_at'] ?? 'now')) . '</td></tr>
                    <tr><td style="padding:4px 0;color:#64748b;">Total:</td><td style="padding:4px 0;font-weight:700;color:#1e293b;text-align:right;">' . self::money((float)($order['total'] ?? 0), $order['currency'] ?? '') . '</td></tr>
                </table>
            </div>

            <p style="margin:24px 0 0;color:#64748b;font-size:13px;">If you have any questions, simply reply to this email.</p>
        ');

        return cms_send_email($order['customer_email'], $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── SHIPPING NOTIFICATION (to customer) ───

    public static function sendShippingNotification(int $orderId, string $trackingNumber): bool
    {
        $order = Shop::getOrder($orderId);
        if (!$order || empty($order['customer_email'])) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $items = json_decode($order['items'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $subject = "Your Order Has Shipped! — #{$order['order_number']}";

        $itemsHtml = '';
        foreach ($items as $item) {
            $itemsHtml .= '<tr>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;color:#1e293b;">' . self::e($item['name'] ?? 'Product') . '</td>
                <td style="padding:8px;border-bottom:1px solid #e2e8f0;text-align:center;color:#64748b;">' . (int)($item['quantity'] ?? 1) . '</td>
            </tr>';
        }

        $body = self::wrap($shopName, '
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:48px;margin-bottom:12px;">🚚</div>
                <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">Your Order is On Its Way!</h1>
                <p style="margin:0;color:#64748b;font-size:15px;">Hi ' . self::e($order['customer_name'] ?? 'there') . ', your order has been shipped.</p>
            </div>

            <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:8px;padding:20px;margin:24px 0;text-align:center;">
                <p style="margin:0 0 4px;font-size:13px;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">Tracking Number</p>
                <p style="margin:0;font-size:22px;font-weight:700;color:#6366f1;letter-spacing:0.05em;">' . self::e($trackingNumber) . '</p>
            </div>

            <p style="margin:0 0 24px;color:#64748b;font-size:14px;text-align:center;">Estimated delivery: 3–7 business days. You can track your package using the tracking number above.</p>

            <div style="background:#f8fafc;border-radius:8px;padding:16px;margin-bottom:24px;">
                <h3 style="margin:0 0 12px;font-size:13px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.05em;">Items in Your Order</h3>
                <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                    <thead>
                        <tr>
                            <th style="padding:8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;">Product</th>
                            <th style="padding:8px;text-align:center;font-size:11px;text-transform:uppercase;color:#94a3b8;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>' . $itemsHtml . '</tbody>
                </table>
            </div>

            <p style="margin:0;color:#64748b;font-size:13px;">If you have any questions about your delivery, simply reply to this email.</p>
        ');

        return cms_send_email($order['customer_email'], $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── LOW STOCK ALERT (to admin) ───

    public static function sendLowStockAlert(array $products): bool
    {
        $adminEmail = get_setting('shop_notification_email', get_setting('admin_email', ''));
        if (empty($adminEmail) || empty($products)) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $count = count($products);
        $subject = "⚠️ Low Stock Alert — {$count} product" . ($count !== 1 ? 's' : '');

        $rowsHtml = '';
        foreach ($products as $p) {
            $stockColor = ((int)($p['stock'] ?? 0) <= 2) ? '#ef4444' : '#f59e0b';
            $rowsHtml .= '<tr>
                <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;color:#1e293b;font-weight:500;">' . self::e($p['name'] ?? 'Unknown') . '</td>
                <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;color:#64748b;">' . self::e($p['sku'] ?? '—') . '</td>
                <td style="padding:10px 8px;border-bottom:1px solid #e2e8f0;text-align:center;font-weight:700;color:' . $stockColor . ';">' . (int)($p['stock'] ?? 0) . '</td>
            </tr>';
        }

        $siteUrl = get_setting('site_url', '');
        $adminLink = $siteUrl ? $siteUrl . '/admin/shop/products' : '#';

        $body = self::wrap($shopName, '
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:48px;margin-bottom:12px;">⚠️</div>
                <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">Low Stock Alert</h1>
                <p style="margin:0;color:#64748b;font-size:15px;">' . $count . ' product' . ($count !== 1 ? 's' : '') . ' need' . ($count === 1 ? 's' : '') . ' restocking.</p>
            </div>

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:24px 0;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th style="padding:10px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;">Product</th>
                        <th style="padding:10px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#94a3b8;">SKU</th>
                        <th style="padding:10px 8px;text-align:center;font-size:11px;text-transform:uppercase;color:#94a3b8;">Stock</th>
                    </tr>
                </thead>
                <tbody>' . $rowsHtml . '</tbody>
            </table>

            <div style="text-align:center;">
                <a href="' . self::e($adminLink) . '" style="display:inline-block;background:#6366f1;color:#ffffff;padding:12px 32px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;">Manage Products</a>
            </div>
        ');

        return cms_send_email($adminEmail, $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── ABANDONED CART (to customer) ───

    public static function sendAbandonedCartEmail(string $email, string $customerName, array $items, ?string $couponCode = null): bool
    {
        if (empty($email) || empty($items)) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $siteUrl = get_setting('site_url', '');
        $currency = get_setting('shop_currency', 'USD');

        $subject = "You left something behind! 🛒";

        $itemsHtml = '';
        $cartTotal = 0.0;
        foreach ($items as $item) {
            $lineTotal = (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
            $cartTotal += $lineTotal;
            $img = !empty($item['image'])
                ? '<img src="' . self::e($item['image']) . '" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">'
                : '<div style="width:60px;height:60px;background:#f1f5f9;border-radius:8px;text-align:center;line-height:60px;font-size:24px;">🛒</div>';
            $itemsHtml .= '<tr>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;">' . $img . '</td>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;"><strong style="color:#1e293b;">' . self::e($item['name'] ?? 'Product') . '</strong><br><span style="color:#64748b;font-size:13px;">Qty: ' . (int)($item['quantity'] ?? 1) . '</span></td>
                <td style="padding:12px 8px;border-bottom:1px solid #e2e8f0;text-align:right;font-weight:600;color:#1e293b;">' . self::money($lineTotal, $currency) . '</td>
            </tr>';
        }

        $couponHtml = '';
        if ($couponCode) {
            $couponHtml = '
            <div style="background:#fffbeb;border:2px dashed #f59e0b;border-radius:8px;padding:16px;margin:24px 0;text-align:center;">
                <p style="margin:0 0 4px;font-size:13px;color:#92400e;">Use this special code for a discount:</p>
                <p style="margin:0;font-size:24px;font-weight:700;color:#f59e0b;letter-spacing:0.1em;">' . self::e($couponCode) . '</p>
            </div>';
        }

        $cartLink = $siteUrl ? $siteUrl . '/cart' : '#';

        $body = self::wrap($shopName, '
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:48px;margin-bottom:12px;">🛒</div>
                <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">You left something behind!</h1>
                <p style="margin:0;color:#64748b;font-size:15px;">Hi ' . self::e($customerName ?: 'there') . ', it looks like you didn\'t finish your purchase.</p>
            </div>

            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:24px 0;">
                <tbody>' . $itemsHtml . '</tbody>
            </table>

            <div style="text-align:right;padding:8px 0;font-size:16px;font-weight:700;color:#1e293b;">
                Cart Total: ' . self::money($cartTotal, $currency) . '
            </div>

            ' . $couponHtml . '

            <div style="text-align:center;margin:24px 0;">
                <a href="' . self::e($cartLink) . '" style="display:inline-block;background:#6366f1;color:#ffffff;padding:14px 40px;border-radius:8px;text-decoration:none;font-weight:600;font-size:16px;">Complete Your Purchase →</a>
            </div>

            <p style="margin:0;color:#94a3b8;font-size:12px;text-align:center;">Items in your cart are not reserved and may sell out.</p>
        ');

        return cms_send_email($email, $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── REVIEW REQUEST (to customer) ───

    public static function sendReviewRequest(int $orderId): bool
    {
        $order = Shop::getOrder($orderId);
        if (!$order || empty($order['customer_email'])) {
            return false;
        }

        $shopName = get_setting('shop_name', 'Our Shop');
        $siteUrl = get_setting('site_url', '');
        $items = json_decode($order['items'] ?? '[]', true);
        if (!is_array($items)) $items = [];

        $subject = "How was your purchase? ⭐";

        $itemsHtml = '';
        foreach ($items as $item) {
            $productLink = $siteUrl ? $siteUrl . '/shop/' . urlencode((string)($item['product_id'] ?? '')) : '#';
            $img = !empty($item['image'])
                ? '<img src="' . self::e($item['image']) . '" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">'
                : '<div style="width:60px;height:60px;background:#f1f5f9;border-radius:8px;text-align:center;line-height:60px;font-size:24px;">📦</div>';
            $itemsHtml .= '
            <div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid #e2e8f0;">
                <div style="flex-shrink:0;margin-right:12px;">' . $img . '</div>
                <div style="flex:1;">
                    <p style="margin:0;font-weight:500;color:#1e293b;">' . self::e($item['name'] ?? 'Product') . '</p>
                    <a href="' . self::e($productLink) . '" style="display:inline-block;margin-top:6px;color:#6366f1;font-size:13px;font-weight:600;text-decoration:none;">Leave a Review →</a>
                </div>
            </div>';
        }

        $body = self::wrap($shopName, '
            <div style="text-align:center;padding:20px 0;">
                <div style="font-size:48px;margin-bottom:12px;">⭐</div>
                <h1 style="margin:0 0 8px;font-size:24px;color:#1e293b;">How was your purchase?</h1>
                <p style="margin:0;color:#64748b;font-size:15px;">Hi ' . self::e($order['customer_name'] ?? 'there') . ', we\'d love to hear about your experience!</p>
            </div>

            <p style="margin:24px 0 16px;color:#64748b;font-size:14px;">Your feedback helps us improve and helps other customers make informed decisions. Please take a moment to review the items from your order:</p>

            <div style="margin-bottom:24px;">' . $itemsHtml . '</div>

            <div style="text-align:center;padding:16px;background:#f8fafc;border-radius:8px;">
                <p style="margin:0;color:#64748b;font-size:13px;">Thank you for shopping with ' . self::e($shopName) . '!</p>
            </div>
        ');

        return cms_send_email($order['customer_email'], $subject, $body, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─── HELPER: Email wrapper ───

    private static function wrap(string $shopName, string $content): string
    {
        $siteUrl = get_setting('site_url', '');
        $year = date('Y');

        return '<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">

<!-- Header -->
<tr><td style="background:#6366f1;padding:24px 32px;border-radius:12px 12px 0 0;text-align:center;">
    <h2 style="margin:0;color:#ffffff;font-size:20px;font-weight:700;">' . self::e($shopName) . '</h2>
</td></tr>

<!-- Body -->
<tr><td style="background:#ffffff;padding:32px;border-left:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">
    ' . $content . '
</td></tr>

<!-- Footer -->
<tr><td style="background:#f8fafc;padding:20px 32px;border-radius:0 0 12px 12px;border:1px solid #e2e8f0;border-top:none;text-align:center;">
    <p style="margin:0 0 4px;color:#94a3b8;font-size:12px;">Powered by <strong>Jessie CMS</strong></p>
    <p style="margin:0;color:#cbd5e1;font-size:11px;">&copy; ' . $year . ' ' . self::e($shopName) . ($siteUrl ? ' — <a href="' . self::e($siteUrl) . '" style="color:#94a3b8;">' . self::e($siteUrl) . '</a>' : '') . '</p>
</td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
    }

    // ─── HELPER: Build totals block ───

    private static function buildTotals(array $order): string
    {
        $currency = $order['currency'] ?? '';
        $html = '<div style="max-width:280px;margin-left:auto;padding-top:16px;">';
        $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b;"><span>Subtotal</span><span style="color:#1e293b;">' . self::money((float)($order['subtotal'] ?? 0), $currency) . '</span></div>';
        if ((float)($order['tax'] ?? 0) > 0) {
            $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b;"><span>Tax</span><span style="color:#1e293b;">' . self::money((float)$order['tax'], $currency) . '</span></div>';
        }
        if ((float)($order['shipping'] ?? 0) > 0) {
            $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b;"><span>Shipping</span><span style="color:#1e293b;">' . self::money((float)$order['shipping'], $currency) . '</span></div>';
        }
        if ((float)($order['discount'] ?? 0) > 0) {
            $html .= '<div style="display:flex;justify-content:space-between;padding:6px 0;font-size:14px;color:#64748b;"><span>Discount</span><span style="color:#10b981;">-' . self::money((float)$order['discount'], $currency) . '</span></div>';
        }
        $html .= '<div style="display:flex;justify-content:space-between;padding:10px 0 0;margin-top:6px;border-top:2px solid #e2e8f0;font-size:16px;font-weight:700;"><span style="color:#1e293b;">Total</span><span style="color:#6366f1;">' . self::money((float)($order['total'] ?? 0), $currency) . '</span></div>';
        $html .= '</div>';
        return $html;
    }

    // ─── HELPER: Format price with currency symbol ───

    private static function money(float $amount, string $currency = ''): string
    {
        if (!$currency) {
            $currency = get_setting('shop_currency', 'USD');
        }
        $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'PLN' => 'zł', 'JPY' => '¥', 'CAD' => 'CA$', 'AUD' => 'A$'];
        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    // ─── HELPER: HTML-escape ───

    private static function e(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }
}
