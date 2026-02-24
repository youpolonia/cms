<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-menu.php';
require_once $pluginDir . '/includes/class-restaurant-order.php';
$stats = \RestaurantMenu::getStats();
$today = \RestaurantOrder::getTodayStats();
$activeOrders = \RestaurantOrder::getActiveOrders();
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.rw{max-width:1100px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.rs{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.rs .s{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.rs .v{font-size:1.8rem;font-weight:800;line-height:1}
.rs .l{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-p{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.ql{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px}
.ql a{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:.2s;display:flex;align-items:center;gap:12px}
.ql a:hover{border-color:#6366f1;transform:translateY(-2px)}
.ql .i{font-size:1.5rem}.ql .t{font-weight:600;font-size:.9rem}.ql .d{font-size:.75rem;color:var(--muted,#94a3b8)}
.order-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem}
.order-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.st-new{background:rgba(59,130,246,.15);color:#60a5fa}
.st-confirmed{background:rgba(245,158,11,.15);color:#fbbf24}
.st-preparing{background:rgba(168,85,247,.15);color:#c084fc}
.st-ready{background:rgba(16,185,129,.15);color:#34d399}
.st-delivering{background:rgba(99,102,241,.15);color:#a5b4fc}
</style>
<div class="rw">
    <div class="rh"><h1>🍕 Restaurant Dashboard</h1><div style="display:flex;gap:10px"><a href="/admin/restaurant/kitchen" class="btn-p">👨‍🍳 Kitchen</a><a href="/admin/restaurant/menu/create" class="btn-s">➕ Add Item</a></div></div>
    <div class="rs">
        <div class="s"><div class="v" style="color:#10b981"><?= $sym ?><?= number_format($today['revenue'], 2) ?></div><div class="l">Today Revenue</div></div>
        <div class="s"><div class="v" style="color:#6366f1"><?= $today['count'] ?></div><div class="l">Today Orders</div></div>
        <div class="s"><div class="v" style="color:#f59e0b"><?= $stats['orders_new'] ?></div><div class="l">New Orders</div></div>
        <div class="s"><div class="v" style="color:#a855f7"><?= $stats['orders_preparing'] ?></div><div class="l">Preparing</div></div>
        <div class="s"><div class="v" style="color:var(--text)"><?= $stats['items_active'] ?></div><div class="l">Menu Items</div></div>
        <div class="s"><div class="v" style="color:#10b981"><?= $sym ?><?= number_format($stats['revenue_total'], 0) ?></div><div class="l">Total Revenue</div></div>
    </div>
    <div class="ql">
        <a href="/admin/restaurant/menu"><span class="i">🍽️</span><div><div class="t">Menu</div><div class="d"><?= $stats['items_active'] ?> items</div></div></a>
        <a href="/admin/restaurant/categories"><span class="i">📁</span><div><div class="t">Categories</div><div class="d"><?= $stats['categories'] ?> active</div></div></a>
        <a href="/admin/restaurant/orders"><span class="i">📋</span><div><div class="t">Orders</div><div class="d"><?= $stats['orders_total'] ?> total</div></div></a>
        <a href="/admin/restaurant/kitchen"><span class="i">👨‍🍳</span><div><div class="t">Kitchen</div><div class="d"><?= $stats['orders_new'] + $stats['orders_preparing'] ?> active</div></div></a>
        <a href="/admin/restaurant/settings"><span class="i">⚙️</span><div><div class="t">Settings</div><div class="d">Hours, fees</div></div></a>
    </div>
    <?php if (!empty($activeOrders)): ?>
    <div class="card">
        <h3>🔴 Active Orders</h3>
        <?php foreach ($activeOrders as $o): ?>
        <div class="order-row">
            <a href="/admin/restaurant/orders/<?= $o['id'] ?>" style="color:#a5b4fc;font-weight:700;text-decoration:none"><?= h($o['order_number']) ?></a>
            <span class="status-badge st-<?= h($o['status']) ?>"><?= h($o['status']) ?></span>
            <span><?= h($o['customer_name']) ?></span>
            <span style="color:var(--muted)"><?= ucfirst(h($o['order_type'])) ?></span>
            <span style="margin-left:auto;font-weight:600"><?= $sym ?><?= number_format((float)$o['total'], 2) ?></span>
            <span style="color:var(--muted);font-size:.72rem"><?= date('H:i', strtotime($o['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Restaurant Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
