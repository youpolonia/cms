<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Shop Settings';
ob_start();
?>
<style>
.shop-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.shop-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.shop-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.shop-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.shop-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group input:focus,.form-group select:focus{outline:none;border-color:#6366f1}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:600px){.form-row{grid-template-columns:1fr}}
.btn-primary{background:#6366f1;color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-primary:hover{background:#4f46e5}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:8px 16px;border-radius:8px;font-size:.85rem;border:1px solid var(--border,#334155);text-decoration:none}
.form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:24px}
</style>

<div class="shop-wrap">
    <div class="shop-header">
        <h1>⚙️ Shop Settings</h1>
        <a href="/admin/shop" class="btn-secondary">← Back to Shop</a>
    </div>

    <form method="post" action="/admin/shop/settings">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="shop-card">
            <h3>🏪 General</h3>
            <div class="form-group">
                <label>Shop Name</label>
                <input type="text" name="shop_name" value="<?= h($settings['shop_name'] ?? 'Shop') ?>">
            </div>
            <div class="form-group">
                <label>Currency</label>
                <select name="shop_currency">
                    <?php foreach (['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'PLN' => 'PLN (zł)'] as $code => $label): ?>
                        <option value="<?= $code ?>" <?= ($settings['shop_currency'] ?? 'USD') === $code ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="shop-card">
            <h3>💰 Tax & Shipping</h3>
            <div class="form-row">
                <div class="form-group">
                    <label>Tax Rate (%)</label>
                    <input type="number" name="shop_tax_rate" step="0.01" min="0" max="100" value="<?= h($settings['shop_tax_rate'] ?? '0') ?>">
                    <div class="hint">Enter percentage, e.g. 23 for 23%</div>
                </div>
                <div class="form-group">
                    <label>Shipping Cost</label>
                    <input type="number" name="shop_shipping_cost" step="0.01" min="0" value="<?= h($settings['shop_shipping_cost'] ?? '0') ?>">
                    <div class="hint">Flat rate shipping cost</div>
                </div>
            </div>
            <div class="form-group">
                <label>Free Shipping Threshold</label>
                <input type="number" name="shop_free_shipping_threshold" step="0.01" min="0" value="<?= h($settings['shop_free_shipping_threshold'] ?? '0') ?>">
                <div class="hint">Orders above this amount get free shipping. Set to 0 to disable.</div>
            </div>
        </div>

        <div class="shop-card">
            <h3>📧 Email Notifications</h3>
            <div class="form-group">
                <label>Notification Email</label>
                <input type="email" name="shop_notification_email" value="<?= h($settings['shop_notification_email'] ?? '') ?>" placeholder="admin@example.com">
                <div class="hint">Where admin alerts are sent (new orders, low stock). Falls back to main admin email.</div>
            </div>
            <div class="form-group">
                <label>Low Stock Threshold</label>
                <input type="number" name="shop_low_stock_threshold" min="0" max="1000" value="<?= h($settings['shop_low_stock_threshold'] ?? '5') ?>">
                <div class="hint">Products with stock at or below this number will trigger a low stock alert.</div>
            </div>
            <div class="form-group" style="margin-top:16px;">
                <label style="margin-bottom:12px;">Email Toggles</label>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:400;cursor:pointer;">
                        <input type="hidden" name="shop_email_order_confirm" value="0">
                        <input type="checkbox" name="shop_email_order_confirm" value="1" <?= ($settings['shop_email_order_confirm'] ?? '1') === '1' ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#6366f1;">
                        Send order confirmation to customer
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:400;cursor:pointer;">
                        <input type="hidden" name="shop_email_admin_notify" value="0">
                        <input type="checkbox" name="shop_email_admin_notify" value="1" <?= ($settings['shop_email_admin_notify'] ?? '1') === '1' ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#6366f1;">
                        Send admin notification on new order
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:400;cursor:pointer;">
                        <input type="hidden" name="shop_email_status_update" value="0">
                        <input type="checkbox" name="shop_email_status_update" value="1" <?= ($settings['shop_email_status_update'] ?? '1') === '1' ? 'checked' : '' ?> style="width:18px;height:18px;accent-color:#6366f1;">
                        Send status updates to customer
                    </label>
                </div>
            </div>
        </div>

        <div class="shop-card">
            <h3>🏢 Company Info (Invoices)</h3>
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= h($settings['company_name'] ?? '') ?>" placeholder="Your Company Ltd.">
                <div class="hint">Used on PDF invoices. Falls back to Shop Name if empty.</div>
            </div>
            <div class="form-group">
                <label>Company Address</label>
                <textarea name="company_address" rows="3" style="width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box;font-family:inherit;resize:vertical"><?= h($settings['company_address'] ?? '') ?></textarea>
                <div class="hint">Full address as it should appear on invoices</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Tax ID / VAT Number</label>
                    <input type="text" name="company_tax_id" value="<?= h($settings['company_tax_id'] ?? '') ?>" placeholder="e.g. PL1234567890">
                </div>
                <div class="form-group">
                    <label>Company Phone</label>
                    <input type="text" name="company_phone" value="<?= h($settings['company_phone'] ?? '') ?>" placeholder="+1 234 567 890">
                </div>
            </div>
            <div class="form-group">
                <label>Company Email</label>
                <input type="email" name="company_email" value="<?= h($settings['company_email'] ?? '') ?>" placeholder="billing@yourcompany.com">
                <div class="hint">Displayed on invoices. Falls back to notification email if empty.</div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">💾 Save Settings</button>
        </div>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
