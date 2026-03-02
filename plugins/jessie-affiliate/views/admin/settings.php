<?php
/**
 * Affiliate Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `affiliate_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_affiliate(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `affiliate_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>🤝 Affiliate Settings</h1><a href="/admin/affiliate" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/affiliate/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Default Commission (%)</label>
                <input type="number" name="default_commission" value="<?= h(_gs_affiliate('default_commission', '10')) ?>">
            </div>
            <div class="j-form-group">
                <label>Cookie Duration (days)</label>
                <input type="number" name="cookie_days" value="<?= h(_gs_affiliate('cookie_days', '30')) ?>">
            </div>
            <div class="j-form-group">
                <label>Minimum Payout</label>
                <input type="number" name="min_payout" value="<?= h(_gs_affiliate('min_payout', '50')) ?>">
                <div class="hint">Minimum amount for withdrawal</div>
            </div>
            <div class="j-form-group">
                <label>Payout Schedule</label>
                <select name="payout_schedule">
                    <option value="manual" <?= _gs_affiliate('payout_schedule', 'monthly') === 'manual' ? 'selected' : '' ?>>Manual</option>
                    <option value="weekly" <?= _gs_affiliate('payout_schedule', 'monthly') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                    <option value="biweekly" <?= _gs_affiliate('payout_schedule', 'monthly') === 'biweekly' ? 'selected' : '' ?>>Bi-weekly</option>
                    <option value="monthly" <?= _gs_affiliate('payout_schedule', 'monthly') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="auto_approve" id="auto_approve" value="1" <?= _gs_affiliate('auto_approve', '0') === '1' ? 'checked' : '' ?>>
                <label for="auto_approve">Auto-Approve Affiliates</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="self_referral" id="self_referral" value="1" <?= _gs_affiliate('self_referral', '0') === '1' ? 'checked' : '' ?>>
                <label for="self_referral">Allow Self-Referral</label>
            </div>
            <div class="j-form-group">
                <label>Notification Email</label>
                <input type="email" name="notification_email" value="<?= h(_gs_affiliate('notification_email', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>Terms & Conditions URL</label>
                <input type="text" name="terms_url" value="<?= h(_gs_affiliate('terms_url', '')) ?>">
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
