<?php
/**
 * Job Board Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `jobs_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_jobs(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `jobs_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>💼 Job Board Settings</h1><a href="/admin/jobs" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/jobs/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Application Mode</label>
                <select name="application_mode">
                    <option value="internal" <?= _gs_jobs('application_mode', 'internal') === 'internal' ? 'selected' : '' ?>>Internal Form</option>
                    <option value="email" <?= _gs_jobs('application_mode', 'internal') === 'email' ? 'selected' : '' ?>>Email Only</option>
                    <option value="external" <?= _gs_jobs('application_mode', 'internal') === 'external' ? 'selected' : '' ?>>External URL</option>
                </select>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="resume_upload" id="resume_upload" value="1" <?= _gs_jobs('resume_upload', '1') === '1' ? 'checked' : '' ?>>
                <label for="resume_upload">Allow Resume Upload</label>
            </div>
            <div class="j-form-group">
                <label>Job Moderation</label>
                <select name="moderation">
                    <option value="auto" <?= _gs_jobs('moderation', 'auto') === 'auto' ? 'selected' : '' ?>>Auto-Approve</option>
                    <option value="manual" <?= _gs_jobs('moderation', 'auto') === 'manual' ? 'selected' : '' ?>>Manual Review</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Jobs Per Page</label>
                <input type="number" name="items_per_page" value="<?= h(_gs_jobs('items_per_page', '15')) ?>">
            </div>
            <div class="j-form-group">
                <label>Default Job Expiry (days)</label>
                <input type="number" name="expiry_days" value="<?= h(_gs_jobs('expiry_days', '30')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="salary_display" id="salary_display" value="1" <?= _gs_jobs('salary_display', '1') === '1' ? 'checked' : '' ?>>
                <label for="salary_display">Show Salary</label>
            </div>
            <div class="j-form-group">
                <label>New Application Alert Email</label>
                <input type="email" name="alert_email" value="<?= h(_gs_jobs('alert_email', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>Job Categories</label>
                <textarea name="categories" rows="3"><?= h(_gs_jobs('categories', 'Technology\\nMarketing\\nSales\\nDesign\\nFinance\\nHR\\nOther')) ?></textarea>
                <div class="hint">One per line</div>
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
