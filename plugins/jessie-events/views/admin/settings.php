<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-event-manager.php';
$s = \EventManager::getAllSettings();
$g = fn($k, $d = '') => h($s[$k] ?? $d);
ob_start();
?>
<style>
.ew{max-width:700px;margin:0 auto;padding:24px 20px}
.eh{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.eh h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-s{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.btn-p{background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.fg{margin-bottom:14px}.fg label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:5px}
.fg input,.fg select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.fr{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:600px){.fr{grid-template-columns:1fr}}
</style>
<div class="ew">
    <div class="eh"><h1>⚙️ Event Settings</h1><a href="/admin/events" class="btn-s">← Dashboard</a></div>
    <form method="post" action="/admin/events/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="card">
            <h3>💰 Currency</h3>
            <div class="fr">
                <div class="fg"><label>Currency</label><select name="currency"><option value="GBP" <?= ($s['currency']??'')==='GBP'?'selected':'' ?>>GBP (£)</option><option value="USD" <?= ($s['currency']??'')==='USD'?'selected':'' ?>>USD ($)</option><option value="EUR" <?= ($s['currency']??'')==='EUR'?'selected':'' ?>>EUR (€)</option><option value="PLN" <?= ($s['currency']??'')==='PLN'?'selected':'' ?>>PLN (zł)</option></select></div>
                <div class="fg"><label>Currency Symbol</label><input type="text" name="currency_symbol" value="<?= $g('currency_symbol', '£') ?>" maxlength="5"></div>
            </div>
        </div>
        <div class="card">
            <h3>👤 Default Organizer</h3>
            <div class="fr">
                <div class="fg"><label>Organizer Name</label><input type="text" name="organizer_name" value="<?= $g('organizer_name') ?>"></div>
                <div class="fg"><label>Organizer Email</label><input type="email" name="organizer_email" value="<?= $g('organizer_email') ?>"></div>
            </div>
        </div>
        <div class="card">
            <h3>🎫 Event Defaults</h3>
            <div class="fr">
                <div class="fg"><label>Default Max Capacity</label><input type="number" name="default_max_capacity" min="1" value="<?= $g('default_max_capacity', '500') ?>"></div>
                <div class="fg"><label>Events per Page</label><input type="number" name="events_per_page" min="1" value="<?= $g('events_per_page', '12') ?>"></div>
            </div>
            <div class="fr">
                <div class="fg"><label>Require Phone</label><select name="require_phone"><option value="1" <?= ($s['require_phone']??'0')==='1'?'selected':'' ?>>Yes</option><option value="0" <?= ($s['require_phone']??'0')==='0'?'selected':'' ?>>No</option></select></div>
                <div class="fg"><label>Allow Free Events</label><select name="allow_free_events"><option value="1" <?= ($s['allow_free_events']??'1')==='1'?'selected':'' ?>>Yes</option><option value="0" <?= ($s['allow_free_events']??'1')==='0'?'selected':'' ?>>No</option></select></div>
            </div>
        </div>
        <div class="card">
            <h3>🔐 Check-in</h3>
            <div class="fg"><label>Check-in Salt (used for QR hash)</label><input type="text" name="checkin_salt" value="<?= $g('checkin_salt') ?>"></div>
        </div>
        <div style="display:flex;justify-content:flex-end"><button type="submit" class="btn-p">💾 Save Settings</button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = 'Event Settings'; require CMS_APP . '/views/admin/layouts/topbar.php';
