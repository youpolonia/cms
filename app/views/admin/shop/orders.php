<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Orders';
ob_start();
$currency = get_setting('shop_currency', 'USD');
$symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł'];
$sym = $symbols[$currency] ?? $currency . ' ';
$statusColors = [
    'pending' => '#f59e0b',
    'processing' => '#3b82f6',
    'shipped' => '#8b5cf6',
    'delivered' => '#10b981',
    'cancelled' => '#ef4444',
    'refunded' => '#94a3b8',
];
?>
<style>
.shop-wrap{max-width:1200px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:20px}
.shop-toolbar input,.shop-toolbar select{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.85rem}
.shop-toolbar input{min-width:200px}
.btn-primary{background:#6366f1;color:#fff;padding:8px 14px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:10px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase;background:var(--bg,#0f172a)}
.shop-tbl tr:hover{background:rgba(99,102,241,.04)}
.order-badge{display:inline-block;padding:3px 10px;border-radius:10px;font-size:.7rem;font-weight:700;text-transform:uppercase}
.payment-badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600}
.payment-badge.paid{background:#10b98122;color:#10b981}
.payment-badge.unpaid{background:#f59e0b22;color:#f59e0b}
.payment-badge.refunded{background:#94a3b822;color:#94a3b8}
a.shop-link{color:#6366f1;text-decoration:none}
a.shop-link:hover{text-decoration:underline}
.text-muted{color:var(--muted,#94a3b8)}
.shop-pagination{display:flex;justify-content:center;gap:6px;margin-top:20px}
.shop-pagination a,.shop-pagination span{padding:6px 12px;border-radius:6px;font-size:.85rem;text-decoration:none;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.shop-pagination span.current{background:#6366f1;color:#fff;border-color:#6366f1}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>🧾 Orders <span class="text-muted" style="font-size:.9rem;font-weight:400">(<?= (int)$total ?>)</span></h1>
    </div>

    <form method="get" action="/admin/shop/orders" class="shop-toolbar">
        <input type="text" name="q" placeholder="Search orders..." value="<?= h($filters['search'] ?? '') ?>">
        <select name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            <?php foreach (['pending','processing','shipped','delivered','cancelled','refunded'] as $s): ?>
                <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-primary">🔍 Search</button>
    </form>

    <div class="shop-card">
        <?php if (empty($orders)): ?>
            <div style="padding:40px;text-align:center;color:var(--muted,#94a3b8)">
                <div style="font-size:2rem;margin-bottom:8px">🧾</div>
                <p>No orders found.</p>
            </div>
        <?php else: ?>
            <table class="shop-tbl">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $o):
                    $items = json_decode($o['items'] ?? '[]', true);
                    $itemCount = is_array($items) ? count($items) : 0;
                    $sc = $statusColors[$o['status'] ?? 'pending'] ?? '#94a3b8';
                ?>
                    <tr>
                        <td><a href="/admin/shop/orders/<?= (int)$o['id'] ?>" class="shop-link" style="font-weight:600"><?= h($o['order_number'] ?? '') ?></a></td>
                        <td>
                            <div style="font-weight:500"><?= h($o['customer_name'] ?? 'N/A') ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= h($o['customer_email'] ?? '') ?></div>
                        </td>
                        <td><?= $itemCount ?> item<?= $itemCount !== 1 ? 's' : '' ?></td>
                        <td style="font-weight:600"><?= h($sym) ?><?= number_format((float)($o['total'] ?? 0), 2) ?></td>
                        <td>
                            <span class="order-badge" style="background:<?= $sc ?>22;color:<?= $sc ?>"><?= h(ucfirst($o['status'] ?? 'pending')) ?></span>
                        </td>
                        <td>
                            <span class="payment-badge <?= h($o['payment_status'] ?? 'unpaid') ?>"><?= h(ucfirst($o['payment_status'] ?? 'unpaid')) ?></span>
                        </td>
                        <td class="text-muted"><?= date('M j, Y H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="shop-pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php
                    $qs = $_GET;
                    $qs['page'] = $i;
                    $url = '/admin/shop/orders?' . http_build_query($qs);
                ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= h($url) ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
