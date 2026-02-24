<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-restaurant-menu.php';
$s = \RestaurantMenu::getAllSettings();
$g = fn($k, $d = '') => h($s[$k] ?? $d);
ob_start();
?>
<style>
.rw{max-width:700px;margin:0 auto;padding:24px 20px}
.rh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.rh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.fg{margin-bottom:14px}.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:5px}
.fg input,.fg select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.fr{grid-template-columns:1fr}}
</style>
<div class="rw">
    <div class="rh"><h1>⚙️ Restaurant Settings</h1><a href="/admin/restaurant" class="btn-s">← Dashboard</a></div>
    <form method="post" action="/admin/restaurant/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="card">
            <h3>🍕 General</h3>
            <div class="fg"><label>Restaurant Name</label><input type="text" name="restaurant_name" value="<?= $g('restaurant_name', 'My Restaurant') ?>"></div>
            <div class="fr">
                <div class="fg"><label>Currency</label><select name="currency"><option value="GBP" <?= ($s['currency']??'')==='GBP'?'selected':'' ?>>GBP (£)</option><option value="USD" <?= ($s['currency']??'')==='USD'?'selected':'' ?>>USD ($)</option><option value="EUR" <?= ($s['currency']??'')==='EUR'?'selected':'' ?>>EUR (€)</option><option value="PLN" <?= ($s['currency']??'')==='PLN'?'selected':'' ?>>PLN (zł)</option></select></div>
                <div class="fg"><label>Currency Symbol</label><input type="text" name="currency_symbol" value="<?= $g('currency_symbol', '£') ?>" maxlength="5"></div>
            </div>
            <div class="fg"><label>Order Types</label><input type="text" name="order_types" value="<?= $g('order_types', 'delivery,pickup') ?>" placeholder="delivery,pickup,dine-in"></div>
            <div class="fg"><label>Accept Orders</label><select name="accept_orders"><option value="1" <?= ($s['accept_orders']??'1')==='1'?'selected':'' ?>>✅ Yes</option><option value="0" <?= ($s['accept_orders']??'1')==='0'?'selected':'' ?>>❌ No (Closed)</option></select></div>
        </div>
        <div class="card">
            <h3>💰 Pricing</h3>
            <div class="fr">
                <div class="fg"><label>Minimum Order Amount</label><input type="number" name="min_order_amount" step="0.01" value="<?= $g('min_order_amount', '10.00') ?>"></div>
                <div class="fg"><label>Delivery Fee</label><input type="number" name="delivery_fee" step="0.01" value="<?= $g('delivery_fee', '3.50') ?>"></div>
            </div>
            <div class="fg"><label>Tax Rate (%)</label><input type="number" name="tax_rate" step="0.01" value="<?= $g('tax_rate', '20') ?>"></div>
        </div>
        <div class="card">
            <h3>⏱️ Estimated Times</h3>
            <div class="fr">
                <div class="fg"><label>Delivery Time</label><input type="text" name="estimated_delivery_time" value="<?= $g('estimated_delivery_time', '30-45') ?>" placeholder="30-45"></div>
                <div class="fg"><label>Pickup Time</label><input type="text" name="estimated_pickup_time" value="<?= $g('estimated_pickup_time', '15-20') ?>" placeholder="15-20"></div>
            </div>
        </div>
        <div style="display:flex;justify-content:flex-end"><button type="submit" class="btn-p">💾 Save Settings</button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = 'Restaurant Settings'; require CMS_APP . '/views/admin/layouts/topbar.php';
