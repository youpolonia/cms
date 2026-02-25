<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-order.php';
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \RestaurantOrder::getAll($_GET, $page);
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.rw{max-width:1100px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.tb{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.tb th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.tb td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.tb tr:last-child td{border-bottom:none}
.sb{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.st-new{background:rgba(59,130,246,.15);color:#60a5fa}.st-confirmed{background:rgba(245,158,11,.15);color:#fbbf24}
.st-preparing{background:rgba(168,85,247,.15);color:#c084fc}.st-ready{background:rgba(16,185,129,.15);color:#34d399}
.st-delivering{background:rgba(99,102,241,.15);color:#a5b4fc}.st-completed{background:rgba(100,116,139,.1);color:#94a3b8}
.st-cancelled{background:rgba(239,68,68,.1);color:#fca5a5}
.fb{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.fb select{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.type-badge{font-size:.7rem;padding:2px 6px;border-radius:3px;font-weight:600}
.tp-delivery{background:rgba(59,130,246,.1);color:#60a5fa}.tp-pickup{background:rgba(16,185,129,.1);color:#34d399}.tp-dine-in{background:rgba(245,158,11,.1);color:#fbbf24}
</style>
<div class="rw">
    <div class="rh"><h1>📋 Orders</h1><a href="/admin/restaurant" class="btn-s">← Dashboard</a></div>
    <div class="fb">
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['new','confirmed','preparing','ready','delivering','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?order_type='+this.value"><option value="">All Types</option><?php foreach (['delivery','pickup','dine-in'] as $t): ?><option value="<?= $t ?>" <?= ($_GET['order_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select>
        <span style="padding:8px;color:var(--muted);font-size:.82rem"><?= $result['total'] ?> orders</span>
    </div>
    <table class="tb"><thead><tr><th>Order</th><th>Customer</th><th>Type</th><th>Items</th><th>Total</th><th>Status</th><th>Time</th></tr></thead><tbody>
        <?php foreach ($result['orders'] as $o): ?>
        <tr>
            <td><a href="/admin/restaurant/orders/<?= $o['id'] ?>" style="color:#a5b4fc;font-weight:700;text-decoration:none"><?= h($o['order_number']) ?></a></td>
            <td><?= h($o['customer_name']) ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($o['customer_phone']) ?></span></td>
            <td><span class="type-badge tp-<?= h($o['order_type']) ?>"><?= ucfirst(h($o['order_type'])) ?></span></td>
            <td style="font-size:.8rem"><?= count($o['items_json']) ?> items</td>
            <td style="font-weight:600"><?= $sym ?><?= number_format((float)$o['total'], 2) ?></td>
            <td><span class="sb st-<?= h($o['status']) ?>"><?= h($o['status']) ?></span></td>
            <td style="font-size:.78rem;color:var(--muted)"><?= date('M j H:i', strtotime($o['created_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['orders'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No orders.</td></tr><?php endif; ?>
    </tbody></table>
</div>
<?php $content = ob_get_clean(); $title = 'Orders'; require CMS_APP . '/views/admin/layouts/topbar.php';
