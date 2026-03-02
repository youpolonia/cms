<?php
/**
 * Membership Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `membership_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_membership(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `membership_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>🎫 Membership Settings</h1><a href="/admin/membership" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/membership/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Trial Period (days)</label>
                <input type="number" name="trial_days" value="<?= h(_gs_membership('trial_days', '0')) ?>">
                <div class="hint">0 = no trial</div>
            </div>
            <div class="j-form-group">
                <label>Grace Period (days)</label>
                <input type="number" name="grace_period_days" value="<?= h(_gs_membership('grace_period_days', '3')) ?>">
                <div class="hint">Extra days after expiry</div>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="auto_renew" id="auto_renew" value="1" <?= _gs_membership('auto_renew', '1') === '1' ? 'checked' : '' ?>>
                <label for="auto_renew">Auto-Renew</label>
            </div>
            <div class="j-form-group">
                <label>After Signup Redirect</label>
                <input type="text" name="signup_redirect" value="<?= h(_gs_membership('signup_redirect', '/membership/portal')) ?>">
            </div>
            <div class="j-form-group">
                <label>Expiry Reminder (days before)</label>
                <input type="number" name="expiry_reminder_days" value="<?= h(_gs_membership('expiry_reminder_days', '7')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="welcome_email" id="welcome_email" value="1" <?= _gs_membership('welcome_email', '1') === '1' ? 'checked' : '' ?>>
                <label for="welcome_email">Send Welcome Email</label>
            </div>
            <div class="j-form-group">
                <label>Restricted Content Message</label>
                <textarea name="content_restriction_msg" rows="3"><?= h(_gs_membership('content_restriction_msg', 'This content is for members only.')) ?></textarea>
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
