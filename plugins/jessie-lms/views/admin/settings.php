<?php
/**
 * LMS Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `lms_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_lms(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `lms_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>🎓 LMS Settings</h1><a href="/admin/lms" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/lms/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Enrollment Mode</label>
                <select name="enrollment_mode">
                    <option value="open" <?= _gs_lms('enrollment_mode', 'open') === 'open' ? 'selected' : '' ?>>Open (anyone)</option>
                    <option value="approval" <?= _gs_lms('enrollment_mode', 'open') === 'approval' ? 'selected' : '' ?>>Requires Approval</option>
                    <option value="invite" <?= _gs_lms('enrollment_mode', 'open') === 'invite' ? 'selected' : '' ?>>Invite Only</option>
                </select>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="certificate_enabled" id="certificate_enabled" value="1" <?= _gs_lms('certificate_enabled', '1') === '1' ? 'checked' : '' ?>>
                <label for="certificate_enabled">Enable Certificates</label>
            </div>
            <div class="j-form-group">
                <label>Certificate Template</label>
                <select name="certificate_template">
                    <option value="classic" <?= _gs_lms('certificate_template', 'modern') === 'classic' ? 'selected' : '' ?>>Classic</option>
                    <option value="modern" <?= _gs_lms('certificate_template', 'modern') === 'modern' ? 'selected' : '' ?>>Modern</option>
                    <option value="minimal" <?= _gs_lms('certificate_template', 'modern') === 'minimal' ? 'selected' : '' ?>>Minimal</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Default Passing Grade (%)</label>
                <input type="number" name="passing_grade" value="<?= h(_gs_lms('passing_grade', '70')) ?>">
                <div class="hint">Minimum score to pass quizzes</div>
            </div>
            <div class="j-form-group">
                <label>Max Quiz Attempts</label>
                <input type="number" name="max_attempts" value="<?= h(_gs_lms('max_attempts', '3')) ?>">
                <div class="hint">0 = unlimited</div>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="show_answers" id="show_answers" value="1" <?= _gs_lms('show_answers', '1') === '1' ? 'checked' : '' ?>>
                <label for="show_answers">Show Correct Answers After Quiz</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="progress_tracking" id="progress_tracking" value="1" <?= _gs_lms('progress_tracking', '1') === '1' ? 'checked' : '' ?>>
                <label for="progress_tracking">Track Progress</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="course_reviews" id="course_reviews" value="1" <?= _gs_lms('course_reviews', '1') === '1' ? 'checked' : '' ?>>
                <label for="course_reviews">Allow Course Reviews</label>
            </div>
            <div class="j-form-group">
                <label>Courses Per Page</label>
                <input type="number" name="items_per_page" value="<?= h(_gs_lms('items_per_page', '12')) ?>">
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
