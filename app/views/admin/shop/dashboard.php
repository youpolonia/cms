<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Shop Dashboard';
ob_start();
$fmt = fn($v) => number_format((float)$v, 0, '.', ',');
$fmtMoney = fn($v) => number_format((float)$v, 2);
$currency = get_setting('shop_currency', 'USD');
$symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł'];
$sym = $symbols[$currency] ?? $currency . ' ';
?>
<style>
.shop-wrap{max-width:1200px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:28px}
.shop-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px}
.shop-stat .num{font-size:1.6rem;font-weight:700;color:var(--text,#e2e8f0)}
.shop-stat .lbl{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.shop-stat.primary{border-color:#6366f1}
.shop-stat.success{border-color:#10b981}
.shop-stat.warning{border-color:#f59e0b}
.shop-grid{display:grid;grid-template-columns:2fr 1fr;gap:20px}
@media(max-width:768px){.shop-grid{grid-template-columns:1fr}}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.shop-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px}
.shop-tbl{width:100%;border-collapse:collapse;font-size:.85rem}
.shop-tbl th,.shop-tbl td{padding:8px 12px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.shop-tbl th{color:var(--muted,#94a3b8);font-weight:600;font-size:.75rem;text-transform:uppercase}
.shop-tbl tr:hover{background:rgba(99,102,241,.05)}
.shop-badge{display:inline-block;padding:2px 10px;border-radius:10px;font-size:.7rem;font-weight:600}
.shop-badge.pending{background:#f59e0b22;color:#f59e0b}
.shop-badge.processing{background:#3b82f622;color:#3b82f6}
.shop-badge.shipped{background:#8b5cf622;color:#8b5cf6}
.shop-badge.delivered{background:#10b98122;color:#10b981}
.shop-badge.cancelled{background:#ef444422;color:#ef4444}
.shop-badge.refunded{background:#94a3b822;color:#94a3b8}
.low-stock-item{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border,#334155)}
.low-stock-item:last-child{border:none}
.low-stock-name{font-size:.85rem;color:var(--text,#e2e8f0)}
.low-stock-qty{font-size:.8rem;font-weight:700;color:#ef4444;background:#ef444422;padding:2px 8px;border-radius:6px}
a.shop-link{color:#6366f1;text-decoration:none}
a.shop-link:hover{text-decoration:underline}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>🛒 Shop Dashboard</h1>
        <a href="/admin/shop/products/create" class="btn" style="background:#6366f1;color:#fff;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:.85rem;font-weight:600">+ Add Product</a>
    </div>

    <div class="shop-stats">
        <div class="shop-stat primary">
            <div class="num"><?= $fmt($stats['totalProducts'] ?? 0) ?></div>
            <div class="lbl">📦 Total Products</div>
        </div>
        <div class="shop-stat success">
            <div class="num"><?= $fmt($stats['activeProducts'] ?? 0) ?></div>
            <div class="lbl">✅ Active Products</div>
        </div>
        <div class="shop-stat">
            <div class="num"><?= $fmt($stats['totalOrders'] ?? 0) ?></div>
            <div class="lbl">🧾 Total Orders</div>
        </div>
        <div class="shop-stat warning">
            <div class="num"><?= $fmt($stats['pendingOrders'] ?? 0) ?></div>
            <div class="lbl">⏳ Pending Orders</div>
        </div>
        <div class="shop-stat">
            <div class="num"><?= h($sym) ?><?= $fmtMoney($stats['todayRevenue'] ?? 0) ?></div>
            <div class="lbl">💰 Revenue Today</div>
        </div>
        <div class="shop-stat">
            <div class="num"><?= h($sym) ?><?= $fmtMoney($stats['monthRevenue'] ?? 0) ?></div>
            <div class="lbl">📅 Revenue This Month</div>
        </div>
        <div class="shop-stat primary">
            <div class="num"><?= h($sym) ?><?= $fmtMoney($stats['totalRevenue'] ?? 0) ?></div>
            <div class="lbl">💎 Total Revenue</div>
        </div>
    </div>

    <div class="shop-grid">
        <div>
            <div class="shop-card">
                <h3>🕐 Recent Orders</h3>
                <?php if (empty($recentOrders)): ?>
                    <p style="color:var(--muted,#94a3b8);font-size:.85rem">No orders yet.</p>
                <?php else: ?>
                    <table class="shop-tbl">
                        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                            <tr>
                                <td><a href="/admin/shop/orders/<?= (int)$o['id'] ?>" class="shop-link"><?= h($o['order_number'] ?? '') ?></a></td>
                                <td><?= h($o['customer_name'] ?? 'N/A') ?></td>
                                <td><?= h($sym) ?><?= number_format((float)($o['total'] ?? 0), 2) ?></td>
                                <td><span class="shop-badge <?= h($o['status'] ?? 'pending') ?>"><?= h(ucfirst($o['status'] ?? 'pending')) ?></span></td>
                                <td style="color:var(--muted)"><?= date('M j, H:i', strtotime($o['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div style="margin-top:12px;text-align:right"><a href="/admin/shop/orders" class="shop-link">View all orders →</a></div>
                <?php endif; ?>
            </div>

            <!-- Recent Reviews -->
            <div class="shop-card">
                <h3>⭐ Recent Reviews</h3>
                <?php
                require_once CMS_ROOT . '/core/shop-reviews.php';
                $recentReviews = \ShopReviews::getRecent(5);
                $reviewStats = \ShopReviews::getStats();
                ?>
                <?php if (empty($recentReviews)): ?>
                    <p style="color:var(--muted,#94a3b8);font-size:.85rem">No reviews yet.</p>
                <?php else: ?>
                    <?php if ($reviewStats['pending'] > 0): ?>
                        <div style="margin-bottom:12px;padding:8px 12px;background:#f59e0b22;border-radius:8px;font-size:.8rem;color:#f59e0b">
                            ⏳ <?= (int)$reviewStats['pending'] ?> review<?= $reviewStats['pending'] !== 1 ? 's' : '' ?> pending moderation
                        </div>
                    <?php endif; ?>
                    <?php foreach ($recentReviews as $rv): ?>
                        <div style="padding:10px 0;border-bottom:1px solid var(--border,#334155);display:flex;align-items:center;justify-content:space-between;gap:10px">
                            <div style="flex:1;min-width:0">
                                <div style="font-size:.85rem;color:var(--text,#e2e8f0);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <span style="color:#f59e0b"><?php for ($i = 1; $i <= 5; $i++) echo $i <= (int)$rv['rating'] ? '★' : '☆'; ?></span>
                                    <?= h($rv['product_name'] ?? 'Unknown') ?> — <em><?= h($rv['customer_name']) ?></em>
                                </div>
                                <div style="font-size:.75rem;color:var(--muted,#94a3b8);margin-top:2px">
                                    <span class="shop-badge <?= h($rv['status']) ?>"><?= h(ucfirst($rv['status'])) ?></span>
                                    <?= date('M j', strtotime($rv['created_at'])) ?>
                                </div>
                            </div>
                            <?php if ($rv['status'] === 'pending'): ?>
                                <div style="display:flex;gap:4px;flex-shrink:0">
                                    <form method="POST" action="/admin/shop/reviews/<?= (int)$rv['id'] ?>/approve" style="display:inline"><?= csrf_token_html() ?><button type="submit" style="padding:4px 8px;border-radius:4px;border:none;background:#10b981;color:#fff;cursor:pointer;font-size:.7rem">✓</button></form>
                                    <form method="POST" action="/admin/shop/reviews/<?= (int)$rv['id'] ?>/reject" style="display:inline"><?= csrf_token_html() ?><button type="submit" style="padding:4px 8px;border-radius:4px;border:none;background:#ef4444;color:#fff;cursor:pointer;font-size:.7rem">✗</button></form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top:12px;text-align:right"><a href="/admin/shop/reviews" class="shop-link">Manage reviews →</a></div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="shop-card">
                <h3>⚠️ Low Stock Alerts</h3>
                <?php if (empty($stats['lowStock'])): ?>
                    <p style="color:var(--muted,#94a3b8);font-size:.85rem">All products well stocked.</p>
                <?php else: ?>
                    <?php foreach ($stats['lowStock'] as $ls): ?>
                        <div class="low-stock-item">
                            <a href="/admin/shop/products/<?= (int)$ls['id'] ?>/edit" class="low-stock-name shop-link"><?= h($ls['name']) ?></a>
                            <span class="low-stock-qty"><?= (int)$ls['stock'] ?> left</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="shop-card">
                <h3>🔗 Quick Links</h3>
                <div style="display:flex;flex-direction:column;gap:8px">
                    <a href="/admin/shop/products" class="shop-link">📦 Manage Products</a>
                    <a href="/admin/shop/categories" class="shop-link">📁 Manage Categories</a>
                    <a href="/admin/shop/orders" class="shop-link">🧾 View Orders</a>
                    <a href="/admin/shop/settings" class="shop-link">⚙️ Shop Settings</a>
                    <a href="/shop" class="shop-link" target="_blank">🌐 View Storefront</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
