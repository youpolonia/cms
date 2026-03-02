<?php
/**
 * Real Estate Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `realestate_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_realestate(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `realestate_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>🏠 Real Estate Settings</h1><a href="/admin/realestate" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/realestate/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Currency Symbol</label>
                <input type="text" name="currency" value="<?= h(_gs_realestate('currency', '$')) ?>">
            </div>
            <div class="j-form-group">
                <label>Area Unit</label>
                <select name="area_unit">
                    <option value="sqft" <?= _gs_realestate('area_unit', 'sqft') === 'sqft' ? 'selected' : '' ?>>sq ft</option>
                    <option value="sqm" <?= _gs_realestate('area_unit', 'sqft') === 'sqm' ? 'selected' : '' ?>>sq m</option>
                    <option value="acre" <?= _gs_realestate('area_unit', 'sqft') === 'acre' ? 'selected' : '' ?>>Acres</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Map Provider</label>
                <select name="map_provider">
                    <option value="none" <?= _gs_realestate('map_provider', 'leaflet') === 'none' ? 'selected' : '' ?>>None</option>
                    <option value="leaflet" <?= _gs_realestate('map_provider', 'leaflet') === 'leaflet' ? 'selected' : '' ?>>Leaflet (Free)</option>
                    <option value="google" <?= _gs_realestate('map_provider', 'leaflet') === 'google' ? 'selected' : '' ?>>Google Maps</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Map API Key</label>
                <input type="text" name="map_api_key" value="<?= h(_gs_realestate('map_api_key', '')) ?>">
            </div>
            <div class="j-form-group">
                <label>Properties Per Page</label>
                <input type="number" name="items_per_page" value="<?= h(_gs_realestate('items_per_page', '12')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="contact_form" id="contact_form" value="1" <?= _gs_realestate('contact_form', '1') === '1' ? 'checked' : '' ?>>
                <label for="contact_form">Show Contact Form</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="mortgage_calc" id="mortgage_calc" value="1" <?= _gs_realestate('mortgage_calc', '1') === '1' ? 'checked' : '' ?>>
                <label for="mortgage_calc">Show Mortgage Calculator</label>
            </div>
            <div class="j-form-group">
                <label>Default Interest Rate (%)</label>
                <input type="number" name="default_interest" value="<?= h(_gs_realestate('default_interest', '5.5')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="featured_badge" id="featured_badge" value="1" <?= _gs_realestate('featured_badge', '1') === '1' ? 'checked' : '' ?>>
                <label for="featured_badge">Show Featured Badge</label>
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
