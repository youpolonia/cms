<?php
/**
 * Portfolio Settings
 */
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
// Ensure settings table
$pdo->exec("CREATE TABLE IF NOT EXISTS `portfolio_settings` (`key` VARCHAR(100) PRIMARY KEY, `value` TEXT NOT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

function _gs_portfolio(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query("SELECT `key`,`value` FROM `portfolio_settings`")->fetchAll(\PDO::FETCH_ASSOC) as $r) { $cache[$r['key']] = $r['value']; } }
    return $cache[$key] ?? $default;
}

ob_start();
?>
<link rel="stylesheet" href="/plugins/shared/jessie-frontend.css">
<div class="j-settings-wrap">
    <div class="j-settings-header"><h1>🎨 Portfolio Settings</h1><a href="/admin/portfolio" class="j-btn-secondary">← Dashboard</a></div>

    <?php if (!empty($_GET['saved'])): ?><div class="j-alert j-alert-success">✅ Settings saved!</div><?php endif; ?>

    <form method="post" action="/admin/portfolio/settings/save">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">

        <div class="j-card">
            <h3>General</h3>
            <div class="j-form-group">
                <label>Gallery Layout</label>
                <select name="layout">
                    <option value="grid" <?= _gs_portfolio('layout', 'grid') === 'grid' ? 'selected' : '' ?>>Grid</option>
                    <option value="masonry" <?= _gs_portfolio('layout', 'grid') === 'masonry' ? 'selected' : '' ?>>Masonry</option>
                    <option value="carousel" <?= _gs_portfolio('layout', 'grid') === 'carousel' ? 'selected' : '' ?>>Carousel</option>
                </select>
            </div>
            <div class="j-form-group">
                <label>Projects Per Page</label>
                <input type="number" name="items_per_page" value="<?= h(_gs_portfolio('items_per_page', '12')) ?>">
            </div>
            <div class="j-form-group">
                <label>Grid Columns</label>
                <select name="columns">
                    <option value="2" <?= _gs_portfolio('columns', '3') === '2' ? 'selected' : '' ?>>2</option>
                    <option value="3" <?= _gs_portfolio('columns', '3') === '3' ? 'selected' : '' ?>>3</option>
                    <option value="4" <?= _gs_portfolio('columns', '3') === '4' ? 'selected' : '' ?>>4</option>
                </select>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="lightbox" id="lightbox" value="1" <?= _gs_portfolio('lightbox', '1') === '1' ? 'checked' : '' ?>>
                <label for="lightbox">Enable Lightbox</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="show_filters" id="show_filters" value="1" <?= _gs_portfolio('show_filters', '1') === '1' ? 'checked' : '' ?>>
                <label for="show_filters">Show Category Filters</label>
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="show_testimonials" id="show_testimonials" value="1" <?= _gs_portfolio('show_testimonials', '1') === '1' ? 'checked' : '' ?>>
                <label for="show_testimonials">Show Testimonials</label>
            </div>
            <div class="j-form-group">
                <label>Max Images Per Project</label>
                <input type="number" name="max_images" value="<?= h(_gs_portfolio('max_images', '20')) ?>">
            </div>
            <div class="j-toggle">
                <input type="checkbox" name="video_embed" id="video_embed" value="1" <?= _gs_portfolio('video_embed', '1') === '1' ? 'checked' : '' ?>>
                <label for="video_embed">Allow Video Embeds</label>
            </div>
        </div>

        <button type="submit" class="j-btn">💾 Save Settings</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require CMS_ROOT . '/app/views/admin/layouts/main.php';
