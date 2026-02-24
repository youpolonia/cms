<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$sym = \RestaurantMenu::getSetting('currency_symbol', '£');
$statuses = ['new'=>'🆕','confirmed'=>'✅','preparing'=>'👨‍🍳','ready'=>'🔔','delivering'=>'🚗','completed'=>'✔️','cancelled'=>'❌'];
ob_start();
?>
<style>
.rw{max-width:800px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:22px;margin-bottom:20px}
.card h3{font-size:.82rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted,#94a3b8);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.status-flow{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
.status-flow button{padding:8px 14px;border-radius:8px;font-size:.8rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;background:var(--bg,#0f172a);color:var(--text,#e2e8f0);transition:.15s}
.status-flow button:hover{border-color:#6366f1}
.status-flow button.active{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;border-color:transparent}
.info-row{display:flex;justify-content:space-between;padding:6px 0;font-size:.85rem;border-bottom:1px solid rgba(51,65,85,.3)}
.info-row:last-child{border-bottom:none}
.info-row .label{color:var(--muted,#94a3b8)}.info-row .val{font-weight:600}
.item-row{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid rgba(51,65,85,.3);font-size:.85rem}
.item-row:last-child{border-bottom:none}
.item-qty{background:rgba(99,102,241,.1);color:#a5b4fc;padding:2px 8px;border-radius:4px;font-weight:700;font-size:.78rem}
</style>
<div class="rw">
    <div class="rh"><h1>📋 <?= h($order['order_number']) ?></h1><a href="/admin/restaurant/orders" class="btn-s">← Orders</a></div>
    <div class="card">
        <h3>🔄 Status</h3>
        <div class="status-flow">
            <?php foreach ($statuses as $st => $icon): ?>
            <button class="<?= $order['status']===$st?'active':'' ?>" onclick="updateStatus(<?= $order['id'] ?>,'<?= $st ?>')"><?= $icon ?> <?= ucfirst($st) ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div class="card">
            <h3>👤 Customer</h3>
            <div class="info-row"><span class="label">Name</span><span class="val"><?= h($order['customer_name']) ?></span></div>
            <div class="info-row"><span class="label">Phone</span><span class="val"><a href="tel:<?= h($order['customer_phone']) ?>" style="color:#a5b4fc"><?= h($order['customer_phone']) ?></a></span></div>
            <?php if ($order['customer_email']): ?><div class="info-row"><span class="label">Email</span><span class="val"><?= h($order['customer_email']) ?></span></div><?php endif; ?>
            <div class="info-row"><span class="label">Type</span><span class="val"><?= ucfirst(h($order['order_type'])) ?></span></div>
            <?php if ($order['delivery_address']): ?><div class="info-row"><span class="label">Address</span><span class="val"><?= h($order['delivery_address']) ?></span></div><?php endif; ?>
            <?php if ($order['delivery_notes']): ?><div class="info-row"><span class="label">Notes</span><span class="val"><?= h($order['delivery_notes']) ?></span></div><?php endif; ?>
        </div>
        <div class="card">
            <h3>💰 Payment</h3>
            <div class="info-row"><span class="label">Subtotal</span><span class="val"><?= $sym ?><?= number_format((float)$order['subtotal'], 2) ?></span></div>
            <?php if ((float)$order['delivery_fee'] > 0): ?><div class="info-row"><span class="label">Delivery</span><span class="val"><?= $sym ?><?= number_format((float)$order['delivery_fee'], 2) ?></span></div><?php endif; ?>
            <div class="info-row"><span class="label">Tax</span><span class="val"><?= $sym ?><?= number_format((float)$order['tax'], 2) ?></span></div>
            <?php if ((float)$order['tip'] > 0): ?><div class="info-row"><span class="label">Tip</span><span class="val"><?= $sym ?><?= number_format((float)$order['tip'], 2) ?></span></div><?php endif; ?>
            <div class="info-row" style="font-size:1rem"><span class="label" style="font-weight:700">Total</span><span class="val" style="color:#10b981;font-size:1.1rem"><?= $sym ?><?= number_format((float)$order['total'], 2) ?></span></div>
            <div class="info-row"><span class="label">Payment</span><span class="val"><?= ucfirst(h($order['payment_method'])) ?></span></div>
            <div class="info-row"><span class="label">Time</span><span class="val"><?= date('M j, Y H:i', strtotime($order['created_at'])) ?></span></div>
        </div>
    </div>
    <div class="card">
        <h3>🍽️ Items</h3>
        <?php foreach ($order['items_json'] as $item): ?>
        <div class="item-row">
            <span class="item-qty"><?= (int)($item['quantity'] ?? 1) ?>×</span>
            <span style="flex:1"><strong><?= h($item['name'] ?? 'Item') ?></strong>
                <?php if (!empty($item['options'])): ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h(is_string($item['options']) ? $item['options'] : json_encode($item['options'])) ?></span><?php endif; ?>
                <?php if (!empty($item['extras'])): ?><br><span style="font-size:.72rem;color:#a5b4fc">+ <?= h(implode(', ', array_map(fn($e) => $e['name'] ?? '', $item['extras'] ?? []))) ?></span><?php endif; ?>
            </span>
            <span style="font-weight:600"><?= $sym ?><?= number_format((float)($item['line_total'] ?? $item['unit_price'] ?? 0), 2) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
function updateStatus(id, status) {
    fetch('/api/restaurant/update-status/' + id, {method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({status:status}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok)location.reload();else alert('Error');});
}
</script>
<?php $content = ob_get_clean(); $title = 'Order ' . h($order['order_number']); require CMS_APP . '/views/admin/layouts/topbar.php';
