<?php
/**
 * Newsletter+ Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_newsletter(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `newsletter_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>📧 Newsletter+ Settings</h1><a href="/admin/newsletter" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/newsletter/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>From Name</label>
                <input type="text" name="from_name" value="<?= h(_gs_newsletter('from_name', '')) ?>">
                <div class="hint">Sender name for emails</div>
            </div>
            <div class="j-form-group">
                <label>From Email</label>
                <input type="email" name="from_email" value="<?= h(_gs_newsletter('from_email', '')) ?>">
                <div class="hint">Sender email address</div>
            </div>
            <div class="j-form-group">
                <label>Reply-To Email</label>
                <input type="email" name="reply_to" value="<?= h(_gs_newsletter('reply_to', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>SMTP Host</label>
                <input type="text" name="smtp_host" value="<?= h(_gs_newsletter('smtp_host', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>SMTP Port</label>
                <input type="number" name="smtp_port" value="<?= h(_gs_newsletter('smtp_port', '587')) ?>">
            </div>
            <div class="j-form-group">
                <label>SMTP Username</label>
                <input type="text" name="smtp_user" value="<?= h(_gs_newsletter('smtp_user', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>SMTP Password</label>
                <input type="password" name="smtp_pass" value="<?= h(_gs_newsletter('smtp_pass', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>Encryption</label>
                <select name="smtp_encryption">
                    <option value="tls" <?= _gs_newsletter('smtp_encryption', 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
                    <option value="ssl" <?= _gs_newsletter('smtp_encryption', 'tls') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    <option value="none" <?= _gs_newsletter('smtp_encryption', 'tls') === 'none' ? 'selected' : '' ?>>None</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Email Footer Text</label>
                <textarea name="footer_text" rows="3"><?= h(_gs_newsletter('footer_text', '')) ?></textarea>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="double_optin" id="double_optin" value="1" <?= _gs_newsletter('double_optin', '0') === '1' ? 'checked' : '' ?>>
                <label for="double_optin">Double Opt-in</label>
            </div>
            <div class="j-form-group">
                <label>Unsubscribe URL</label>
                <input type="text" name="unsubscribe_page" value="<?= h(_gs_newsletter('unsubscribe_page', '/newsletter/unsubscribe')) ?>">
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
