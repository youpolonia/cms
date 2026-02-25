<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-order.php';
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$activeOrders = \RestaurantOrder::getActiveOrders();
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
ob_start();
?>
<style>
.rw{max-width:1200px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.kitchen-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
.order-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;position:relative}
.order-card.st-new{border-left:4px solid #3b82f6}
.order-card.st-confirmed{border-left:4px solid #f59e0b}
.order-card.st-preparing{border-left:4px solid #a855f7}
.order-card.st-ready{border-left:4px solid #10b981}
.order-card.st-delivering{border-left:4px solid #6366f1}
.oc-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.oc-header .num{font-weight:800;font-size:1rem;color:#a5b4fc}
.oc-header .type{font-size:.7rem;padding:2px 8px;border-radius:4px;font-weight:700;text-transform:uppercase}
.tp-delivery{background:rgba(59,130,246,.1);color:#60a5fa}.tp-pickup{background:rgba(16,185,129,.1);color:#34d399}
.oc-customer{font-size:.82rem;color:var(--muted,#94a3b8);margin-bottom:10px}
.oc-items{border-top:1px solid var(--border,#334155);padding-top:10px;margin-bottom:12px}
.oc-item{display:flex;gap:8px;padding:4px 0;font-size:.85rem}
.oc-item .qty{background:rgba(99,102,241,.15);color:#a5b4fc;padding:1px 6px;border-radius:3px;font-weight:700;font-size:.75rem;min-width:22px;text-align:center}
.oc-actions{display:flex;gap:6px}
.oc-actions button{flex:1;padding:8px;border-radius:6px;font-size:.78rem;font-weight:600;border:none;cursor:pointer}
.btn-next{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff}
.btn-cancel{background:rgba(239,68,68,.1);color:#fca5a5}
.timer{font-size:.7rem;color:var(--muted);text-align:right}
.empty-kitchen{text-align:center;padding:80px 20px;color:var(--muted)}
.empty-kitchen p{font-size:1.2rem;margin-bottom:8px}
</style>
<div class="rw">
    <div class="rh"><h1>👨‍🍳 Kitchen Display</h1><div><a href="/admin/restaurant" class="btn-s">← Dashboard</a> <button onclick="location.reload()" class="btn-s">🔄 Refresh</button></div></div>
    <?php if (empty($activeOrders)): ?>
    <div class="empty-kitchen"><p>🎉 All caught up!</p><span>No active orders right now.</span></div>
    <?php else: ?>
    <div class="kitchen-grid">
        <?php foreach ($activeOrders as $o):
            $nextStatus = match($o['status']) { 'new'=>'confirmed', 'confirmed'=>'preparing', 'preparing'=>'ready', 'ready'=>$o['order_type']==='delivery'?'delivering':'completed', 'delivering'=>'completed', default=>'completed' };
        ?>
        <div class="order-card st-<?= h($o['status']) ?>">
            <div class="oc-header">
                <span class="num"><?= h($o['order_number']) ?></span>
                <span class="type tp-<?= h($o['order_type']) ?>"><?= ucfirst(h($o['order_type'])) ?></span>
            </div>
            <div class="oc-customer"><?= h($o['customer_name']) ?> · <?= h($o['customer_phone']) ?><?php if($o['delivery_notes']): ?><br>📝 <?= h($o['delivery_notes']) ?><?php endif; ?></div>
            <div class="oc-items">
                <?php foreach ($o['items_json'] as $item): ?>
                <div class="oc-item"><span class="qty"><?= (int)($item['quantity']??1) ?></span><span><?= h($item['name']??'Item') ?></span></div>
                <?php endforeach; ?>
            </div>
            <div class="timer">⏱ <?= date('H:i', strtotime($o['created_at'])) ?> · <?= round((time()-strtotime($o['created_at']))/60) ?>min ago</div>
            <div class="oc-actions">
                <button class="btn-next" onclick="updateStatus(<?= $o['id'] ?>,'<?= $nextStatus ?>')">→ <?= ucfirst($nextStatus) ?></button>
                <?php if ($o['status'] !== 'cancelled'): ?><button class="btn-cancel" onclick="if(confirm('Cancel?'))updateStatus(<?= $o['id'] ?>,'cancelled')">✕</button><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function updateStatus(id,status){fetch('/api/restaurant/update-status/'+id,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({status:status}),credentials:'same-origin'}).then(function(r){return r.json()}).then(function(d){if(d.ok)location.reload()});}
// Auto-refresh every 30s
setTimeout(function(){location.reload()},30000);
</script>
<?php $content = ob_get_clean(); $title = 'Kitchen Display'; require CMS_APP . '/views/admin/layouts/topbar.php';
