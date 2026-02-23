<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Order #' . h($order['order_number'] ?? '');
ob_start();
$currency = get_setting('shop_currency', 'USD');
$symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł'];
$sym = $symbols[$currency] ?? $currency . ' ';
$items = json_decode($order['items'] ?? '[]', true);
if (!is_array($items)) $items = [];
$billing = json_decode($order['billing_address'] ?? '{}', true);
if (!is_array($billing)) $billing = [];
$statusColors = [
    'pending' => '#f59e0b',
    'processing' => '#3b82f6',
    'shipped' => '#8b5cf6',
    'delivered' => '#10b981',
    'cancelled' => '#ef4444',
    'refunded' => '#94a3b8',
];
$sc = $statusColors[$order['status'] ?? 'pending'] ?? '#94a3b8';
?>
<style>
.shop-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px}
@media(max-width:768px){.shop-grid{grid-template-columns:1fr}}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.shop-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border,#334155);font-size:.85rem}
.info-row:last-child{border:none}
.info-label{color:var(--muted,#94a3b8)}
.info-value{color:var(--text,#e2e8f0);font-weight:500}
.order-badge{display:inline-block;padding:4px 12px;border-radius:10px;font-size:.75rem;font-weight:700;text-transform:uppercase}
.totals-row{display:flex;justify-content:space-between;padding:6px 0;font-size:.85rem}
.totals-row.total{font-size:1rem;font-weight:700;padding-top:10px;border-top:2px solid var(--border,#334155);margin-top:6px}
.btn-primary{background:#6366f1;color:#fff;padding:8px 16px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer}
.btn-primary:hover{background:#4f46e5}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:8px 16px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group select,.form-group textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.85rem;box-sizing:border-box;font-family:inherit}
.form-group textarea{min-height:60px;resize:vertical}
.text-muted{color:var(--muted,#94a3b8)}
.item-img{width:40px;height:40px;border-radius:6px;object-fit:cover;background:var(--bg,#0f172a)}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>🧾 Order <?= h($order['order_number'] ?? '') ?></h1>
        <div style="display:flex;gap:10px;align-items:center">
            <a href="/admin/shop/orders/<?= (int)$order['id'] ?>/invoice" class="btn-primary" style="text-decoration:none" target="_blank">📄 Download Invoice</a>
            <a href="/admin/shop/orders" class="btn-secondary">← Back to Orders</a>
        </div>
    </div>

    <div class="shop-grid">
        <div class="shop-card">
            <h3>👤 Customer Info</h3>
            <div class="info-row">
                <span class="info-label">Name</span>
                <span class="info-value"><?= h($order['customer_name'] ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email</span>
                <span class="info-value"><?= h($order['customer_email'] ?? 'N/A') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Phone</span>
                <span class="info-value"><?= h($order['customer_phone'] ?? '—') ?></span>
            </div>
            <?php if (!empty($billing)): ?>
                <div class="info-row">
                    <span class="info-label">Address</span>
                    <span class="info-value">
                        <?= h(implode(', ', array_filter([
                            $billing['line1'] ?? '',
                            $billing['line2'] ?? '',
                            $billing['city'] ?? '',
                            $billing['state'] ?? '',
                            $billing['zip'] ?? '',
                            $billing['country'] ?? '',
                        ]))) ?: '—' ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <div class="shop-card">
            <h3>📋 Order Info</h3>
            <div class="info-row">
                <span class="info-label">Order Number</span>
                <span class="info-value"><?= h($order['order_number'] ?? '') ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Date</span>
                <span class="info-value"><?= date('M j, Y H:i', strtotime($order['created_at'] ?? 'now')) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value"><span class="order-badge" style="background:<?= $sc ?>22;color:<?= $sc ?>"><?= h(ucfirst($order['status'] ?? 'pending')) ?></span></span>
            </div>
            <div class="info-row">
                <span class="info-label">Payment</span>
                <span class="info-value"><?= h(ucfirst($order['payment_status'] ?? 'unpaid')) ?> (<?= h($order['payment_method'] ?? 'N/A') ?>)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Currency</span>
                <span class="info-value"><?= h($order['currency'] ?? $currency) ?></span>
            </div>
        </div>
    </div>

    <div class="shop-card">
        <h3>📦 Order Items</h3>
        <table class="shop-tbl">
            <thead>
                <tr>
                    <th style="width:50px"></th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th style="text-align:right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <?php if (!empty($item['image'])): ?>
                            <img src="<?= h($item['image']) ?>" class="item-img" alt="">
                        <?php else: ?>
                            <div style="width:40px;height:40px;background:var(--bg,#0f172a);border-radius:6px;display:flex;align-items:center;justify-content:center">📦</div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:500"><?= h($item['name'] ?? 'Unknown') ?></td>
                    <td><?= h($sym) ?><?= number_format((float)($item['price'] ?? 0), 2) ?></td>
                    <td><?= (int)($item['quantity'] ?? 1) ?></td>
                    <td style="text-align:right;font-weight:600"><?= h($sym) ?><?= number_format((float)($item['line_total'] ?? 0), 2) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div style="max-width:300px;margin-left:auto;padding-top:16px">
            <div class="totals-row">
                <span class="text-muted">Subtotal</span>
                <span><?= h($sym) ?><?= number_format((float)($order['subtotal'] ?? 0), 2) ?></span>
            </div>
            <div class="totals-row">
                <span class="text-muted">Tax</span>
                <span><?= h($sym) ?><?= number_format((float)($order['tax'] ?? 0), 2) ?></span>
            </div>
            <div class="totals-row">
                <span class="text-muted">Shipping</span>
                <span><?= h($sym) ?><?= number_format((float)($order['shipping'] ?? 0), 2) ?></span>
            </div>
            <?php if ((float)($order['discount'] ?? 0) > 0): ?>
            <div class="totals-row">
                <span class="text-muted">Discount</span>
                <span style="color:#10b981">-<?= h($sym) ?><?= number_format((float)$order['discount'], 2) ?></span>
            </div>
            <?php endif; ?>
            <div class="totals-row total">
                <span>Total</span>
                <span style="color:#6366f1"><?= h($sym) ?><?= number_format((float)($order['total'] ?? 0), 2) ?></span>
            </div>
        </div>
    </div>

    <div class="shop-grid">
        <div class="shop-card">
            <h3>🔄 Update Status</h3>
            <form method="post" action="/admin/shop/orders/<?= (int)$order['id'] ?>/status">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <div class="form-group">
                    <label>Order Status</label>
                    <select name="status">
                        <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($order['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status">
                        <?php foreach (['unpaid','paid','refunded'] as $ps): ?>
                            <option value="<?= $ps ?>" <?= ($order['payment_status'] ?? '') === $ps ? 'selected' : '' ?>><?= ucfirst($ps) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary">💾 Save Changes</button>
            </form>
        </div>

        <div class="shop-card">
            <h3>📝 Notes</h3>
            <p style="font-size:.85rem;color:var(--text,#e2e8f0);white-space:pre-wrap"><?= h($order['notes'] ?? 'No notes.') ?></p>
        </div>
    </div>

    <div class="shop-card">
        <h3>📦 Shipping & Tracking</h3>
        <?php if (!empty($order['tracking_number'])): ?>
            <div style="background:var(--bg,#0f172a);border:1px solid var(--border,#334155);border-radius:8px;padding:12px;margin-bottom:16px;">
                <span class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;">Current Tracking Number</span>
                <div style="font-size:1.1rem;font-weight:700;color:#6366f1;margin-top:4px;letter-spacing:.05em;"><?= h($order['tracking_number']) ?></div>
            </div>
            <form method="post" action="/admin/shop/orders/<?= (int)$order['id'] ?>/tracking" style="margin-bottom:16px;">
                <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="tracking_number" value="<?= h($order['tracking_number']) ?>">
                <button type="submit" class="btn-secondary" style="width:100%;text-align:center;">📧 Resend Shipping Notification</button>
            </form>
            <hr style="border:none;border-top:1px solid var(--border,#334155);margin:16px 0;">
        <?php endif; ?>
        <form method="post" action="/admin/shop/orders/<?= (int)$order['id'] ?>/tracking">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-group">
                <label><?= !empty($order['tracking_number']) ? 'Update Tracking Number' : 'Add Tracking Number' ?></label>
                <input type="text" name="tracking_number" value="" placeholder="Enter tracking number" required style="width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.85rem;box-sizing:border-box">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;">🚚 <?= !empty($order['tracking_number']) ? 'Update' : 'Add' ?> & Notify Customer</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
