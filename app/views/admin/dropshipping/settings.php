<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$settings = $settings ?? [];
ob_start();
?>
<style>
.ds-wrap{max-width:700px;margin:0 auto;padding:24px 20px}
.ds-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ds-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.ds-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.ds-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.form-group .hint{font-size:.75rem;color:var(--muted,#94a3b8);margin-top:4px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.toggle-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.toggle-row input[type="checkbox"]{width:18px;height:18px;accent-color:#6366f1}
.toggle-row label{font-size:.82rem;color:var(--text,#e2e8f0);cursor:pointer}
.btn-ds{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);cursor:pointer;text-decoration:none}
.btn-warning{background:#f59e0b;color:#000;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer}
.status-good{color:#34d399}
.status-warn{color:#fbbf24}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(51,65,85,.4);font-size:.85rem}
.info-row:last-child{border-bottom:none}
.info-row .label{color:var(--muted,#94a3b8)}
.info-row .value{color:var(--text,#e2e8f0);font-weight:600}
</style>

<div class="ds-wrap">
    <div class="ds-header">
        <h1>⚙️ Dropshipping Settings</h1>
        <a href="/admin/dropshipping" class="btn-secondary">← Dashboard</a>
    </div>

    <!-- System Status -->
    <div class="ds-card">
        <h3>📊 System Status</h3>
        <div class="info-row"><span class="label">Module Status</span><span class="value status-good">✅ Active</span></div>
        <div class="info-row"><span class="label">Database Tables</span><span class="value"><?= (int)($settings['table_count'] ?? 5) ?>/5</span></div>
        <div class="info-row"><span class="label">Total Suppliers</span><span class="value"><?= (int)($settings['suppliers'] ?? 0) ?></span></div>
        <div class="info-row"><span class="label">Linked Products</span><span class="value"><?= (int)($settings['linked_products'] ?? 0) ?></span></div>
        <div class="info-row"><span class="label">Active Price Rules</span><span class="value"><?= (int)($settings['price_rules'] ?? 0) ?></span></div>
        <div class="info-row"><span class="label">AI Provider</span><span class="value"><?= h($settings['ai_provider'] ?? 'auto') ?></span></div>
        <div class="info-row"><span class="label">Last Sync</span><span class="value"><?= h($settings['last_sync'] ?? 'Never') ?></span></div>
    </div>

    <form method="post" action="/admin/dropshipping/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <!-- Import Defaults -->
        <div class="ds-card">
            <h3>📥 Import Defaults</h3>
            <div class="toggle-row"><input type="checkbox" id="default-rewrite" name="default_ai_rewrite" value="1" <?= ($settings['default_ai_rewrite'] ?? true) ? 'checked' : '' ?>><label for="default-rewrite">✍️ AI Rewrite descriptions on import</label></div>
            <div class="toggle-row"><input type="checkbox" id="default-seo" name="default_ai_seo" value="1" <?= ($settings['default_ai_seo'] ?? true) ? 'checked' : '' ?>><label for="default-seo">🔍 AI Generate SEO on import</label></div>
            <div class="toggle-row"><input type="checkbox" id="default-images" name="default_ai_images" value="1" <?= ($settings['default_ai_images'] ?? false) ? 'checked' : '' ?>><label for="default-images">🖼️ AI Process images on import (remove BG + ALT)</label></div>
            <div class="form-row">
                <div class="form-group">
                    <label>Default Language</label>
                    <select name="default_language">
                        <?php foreach (['en' => 'English', 'pl' => 'Polish', 'de' => 'German', 'fr' => 'French', 'es' => 'Spanish'] as $code => $lang): ?>
                        <option value="<?= $code ?>" <?= ($settings['default_language'] ?? 'en') === $code ? 'selected' : '' ?>><?= $lang ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Default Tone</label>
                    <select name="default_tone">
                        <?php foreach (['professional' => 'Professional', 'casual' => 'Casual', 'luxury' => 'Luxury', 'friendly' => 'Friendly', 'technical' => 'Technical'] as $code => $label): ?>
                        <option value="<?= $code ?>" <?= ($settings['default_tone'] ?? 'professional') === $code ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Default Product Status</label>
                <select name="default_status">
                    <option value="draft" <?= ($settings['default_status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft (review before publish)</option>
                    <option value="active" <?= ($settings['default_status'] ?? 'draft') === 'active' ? 'selected' : '' ?>>Active (publish immediately)</option>
                </select>
                <div class="hint">Draft is recommended — review AI output before publishing.</div>
            </div>
        </div>

        <!-- Sync Settings -->
        <div class="ds-card">
            <h3>🔄 Auto-Sync Settings</h3>
            <div class="toggle-row"><input type="checkbox" id="auto-sync" name="auto_sync_enabled" value="1" <?= ($settings['auto_sync_enabled'] ?? false) ? 'checked' : '' ?>><label for="auto-sync">Enable automatic price/stock sync</label></div>
            <div class="form-row">
                <div class="form-group">
                    <label>Sync Interval</label>
                    <select name="sync_interval">
                        <option value="6h" <?= ($settings['sync_interval'] ?? '12h') === '6h' ? 'selected' : '' ?>>Every 6 hours</option>
                        <option value="12h" <?= ($settings['sync_interval'] ?? '12h') === '12h' ? 'selected' : '' ?>>Every 12 hours</option>
                        <option value="24h" <?= ($settings['sync_interval'] ?? '12h') === '24h' ? 'selected' : '' ?>>Every 24 hours</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Low Margin Alert (%)</label>
                    <input type="number" name="low_margin_threshold" step="1" min="0" max="100" value="<?= (int)($settings['low_margin_threshold'] ?? 10) ?>">
                    <div class="hint">Alert when margin drops below this %.</div>
                </div>
            </div>
            <div class="toggle-row"><input type="checkbox" id="auto-disable" name="auto_disable_oos" value="1" <?= ($settings['auto_disable_oos'] ?? true) ? 'checked' : '' ?>><label for="auto-disable">Auto-disable products when supplier out of stock</label></div>
        </div>

        <!-- Order Forwarding -->
        <div class="ds-card">
            <h3>🚚 Order Forwarding</h3>
            <div class="toggle-row"><input type="checkbox" id="auto-forward" name="auto_forward" value="1" <?= ($settings['auto_forward'] ?? false) ? 'checked' : '' ?>><label for="auto-forward">Auto-forward orders to suppliers</label></div>
            <div class="toggle-row"><input type="checkbox" id="email-notify" name="email_notify_supplier" value="1" <?= ($settings['email_notify_supplier'] ?? true) ? 'checked' : '' ?>><label for="email-notify">Email supplier on new order (manual suppliers)</label></div>
            <div class="toggle-row"><input type="checkbox" id="email-tracking" name="email_tracking_customer" value="1" <?= ($settings['email_tracking_customer'] ?? true) ? 'checked' : '' ?>><label for="email-tracking">Email customer when tracking updated</label></div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end">
            <a href="/admin/dropshipping" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-ds">💾 Save Settings</button>
        </div>
    </form>

    <!-- Manual Actions -->
    <div class="ds-card" style="margin-top:20px">
        <h3>🛠️ Manual Actions</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <button type="button" class="btn-warning" onclick="if(confirm('Run full sync now?'))fetch('/api/dropshipping/sync-all',{method:'POST',credentials:'same-origin'}).then(r=>r.json()).then(d=>alert('Sync: '+d.price_updated+' prices, '+d.stock_updated+' stock updated')).catch(e=>alert('Error'))">🔄 Run Full Sync Now</button>
            <button type="button" class="btn-secondary" onclick="if(confirm('Sync tracking for all orders?'))fetch('/api/dropshipping/sync-tracking',{method:'POST',credentials:'same-origin'}).then(r=>r.json()).then(d=>alert('Checked: '+d.checked+', Updated: '+d.updated))">📦 Sync Tracking</button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dropshipping Settings';
require CMS_APP . '/views/admin/layouts/topbar.php';
