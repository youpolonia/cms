<?php
/**
 * Directory Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `directory_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_directory(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `directory_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>📂 Directory Settings</h1><a href="/admin/directory" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/directory/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Moderation Mode</label>
                <select name="moderation">
                    <option value="auto" <?= _gs_directory('moderation', 'manual') === 'auto' ? 'selected' : '' ?>>Auto-Approve</option>
                    <option value="manual" <?= _gs_directory('moderation', 'manual') === 'manual' ? 'selected' : '' ?>>Manual Review</option>
                    <option value="none" <?= _gs_directory('moderation', 'manual') === 'none' ? 'selected' : '' ?>>No Submissions</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Map Provider</label>
                <select name="map_provider">
                    <option value="none" <?= _gs_directory('map_provider', 'leaflet') === 'none' ? 'selected' : '' ?>>None</option>
                    <option value="leaflet" <?= _gs_directory('map_provider', 'leaflet') === 'leaflet' ? 'selected' : '' ?>>Leaflet (Free)</option>
                    <option value="google" <?= _gs_directory('map_provider', 'leaflet') === 'google' ? 'selected' : '' ?>>Google Maps</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Map API Key</label>
                <input type="text" name="map_api_key" value="<?= h(_gs_directory('map_api_key', '')) ?>">
                <div class="hint">Required for Google Maps</div>
            </div>
            <div class="j-form-group">
                <label>Listings Per Page</label>
                <input type="number" name="items_per_page" value="<?= h(_gs_directory('items_per_page', '12')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="allow_reviews" id="allow_reviews" value="1" <?= _gs_directory('allow_reviews', '1') === '1' ? 'checked' : '' ?>>
                <label for="allow_reviews">Allow Reviews</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="allow_photos" id="allow_photos" value="1" <?= _gs_directory('allow_photos', '1') === '1' ? 'checked' : '' ?>>
                <label for="allow_photos">Allow Photo Uploads</label>
            </div>
            <div class="j-form-group">
                <label>Max Photos Per Listing</label>
                <input type="number" name="max_photos" value="<?= h(_gs_directory('max_photos', '10')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="claim_enabled" id="claim_enabled" value="1" <?= _gs_directory('claim_enabled', '1') === '1' ? 'checked' : '' ?>>
                <label for="claim_enabled">Allow Business Claiming</label>
            </div>
            <div class="j-form-group">
                <label>Featured Listing Fee</label>
                <input type="number" name="featured_fee" value="<?= h(_gs_directory('featured_fee', '0')) ?>">
                <div class="hint">0 = free</div>
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
