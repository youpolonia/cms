<?php
/**
 * Themes Management View
 * DO NOT add closing ?> tag
 */
$title = 'Themes';

// Protected themes that cannot be deleted
$protectedThemes = ['jessie', 'default', 'default_public', 'core', 'presets', 'current'];

ob_start();
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="info-box">
    <p>🎨 Themes control the visual appearance of your website. The active theme is used for all public pages.</p>
</div>

<div class="upload-theme-box">
    <h3>📦 Install Theme from ZIP</h3>
    <form method="POST" action="/admin/themes/upload" enctype="multipart/form-data" class="upload-form">
        <?= csrf_field() ?>
        <div class="upload-area">
            <input type="file" name="theme_zip" id="theme_zip" accept=".zip" required>
            <label for="theme_zip">
                <span class="upload-icon">📁</span>
                <span class="upload-text">Choose ZIP file or drag & drop</span>
            </label>
        </div>
        <button type="submit" class="btn btn-primary">
            <span>⬆️</span> Install Theme
        </button>
    </form>
</div>

<div class="themes-header">
    <h2>Available Themes</h2>
    <div class="themes-stats">
        <div class="stat-box">
            <div class="value"><?= count($themes) ?></div>
            <div class="label">Themes</div>
        </div>
        <div class="stat-box">
            <div class="value"><?= esc($activeTheme) ?></div>
            <div class="label">Active</div>
        </div>
    </div>
</div>

<?php if (empty($themes)): ?>
<div class="empty-state">
    <div class="icon">🎨</div>
    <h2>No themes found</h2>
    <p style="color: var(--text-muted);">Create a theme folder in /themes with a theme.json file.</p>
</div>
<?php else: ?>
<div class="themes-grid">
    <?php foreach ($themes as $theme): 
        $isActive = ($theme['slug'] === $activeTheme);
    ?>
    <div class="theme-card <?= $isActive ? 'active' : '' ?>">
        <div class="theme-preview">
            <?php if ($theme['screenshot']): ?>
            <img src="<?= esc($theme['screenshot']) ?>" alt="<?= esc($theme['name']) ?>">
            <?php else: ?>
            <span class="placeholder">🎨</span>
            <?php endif; ?>
            <div class="theme-overlay">
                <a href="/admin/themes/<?= urlencode($theme['slug']) ?>/customize">Customize</a>
                <a href="/admin/theme-editor/<?= urlencode($theme['slug']) ?>">Edit</a>
                <a href="/?preview_theme=<?= urlencode($theme['slug']) ?>" target="_blank">Preview</a>
            </div>
        </div>
        <div class="theme-info">
            <div class="theme-name"><?= esc($theme['name']) ?></div>
            <div class="theme-desc"><?= esc($theme['description'] ?: 'No description available.') ?></div>
            <div class="theme-meta">
                <span>v<?= esc($theme['version']) ?></span>
                <?php if (!empty($theme['author'])): ?>
                <span>by <?= esc($theme['author']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="theme-footer">
            <?php if ($isActive): ?>
            <button class="btn btn-secondary btn-sm" disabled>Active Theme</button>
            <?php if (function_exists('theme_has_demo_content') && theme_has_demo_content($theme['slug'])): ?>
            <form method="POST" action="/admin/themes/install-demo" style="margin: 4px 0 0; display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Install demo content (pages, articles, menu) for this theme?')">📦 Demo Content</button>
            </form>
            <?php endif; ?>
            <?php else: ?>
            <form method="POST" action="/admin/themes/activate" style="margin: 0; display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn btn-primary btn-sm">Activate</button>
            </form>
            <?php if (function_exists('theme_has_demo_content') && theme_has_demo_content($theme['slug'])): ?>
            <form method="POST" action="/admin/themes/install-demo" style="margin: 0; display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Install demo content (pages, articles, menu) for this theme?')">📦 Demo Content</button>
            </form>
            <?php endif; ?>
            <?php if (!in_array($theme['slug'], $protectedThemes)): ?>
            <form method="POST" action="/admin/themes/delete" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete theme \'<?= esc($theme['slug']) ?>\'? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<style>
.info-box {
    background: var(--accent-muted, rgba(139, 92, 246, 0.1));
    border: 1px solid var(--accent, #8b5cf6);
    border-radius: var(--radius, 8px);
    padding: 1rem;
    margin-bottom: 1.5rem;
}
.info-box p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-primary);
}
.themes-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}
.themes-header h2 {
    margin: 0;
}
.themes-stats {
    display: flex;
    gap: 1rem;
}
.stat-box {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius, 8px);
    padding: 0.75rem 1rem;
    text-align: center;
}
.stat-box .value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--accent, #8b5cf6);
}
.stat-box .label {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.themes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}
.theme-card {
    background: var(--bg-primary);
    border: 2px solid var(--border);
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    transition: all 0.2s;
    position: relative;
}
.theme-card:hover {
    border-color: var(--accent, #8b5cf6);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.theme-card.active {
    border-color: var(--success, #10b981);
}
.theme-card.active::before {
    content: '✓ Active';
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--success, #10b981);
    color: #fff;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    z-index: 10;
}
.theme-preview {
    height: 160px;
    background: linear-gradient(135deg, var(--accent, #8b5cf6) 0%, var(--accent-hover, #7c3aed) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.theme-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.theme-preview .placeholder {
    font-size: 3.5rem;
    opacity: 0.3;
}
.theme-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.6);
    opacity: 0;
    transition: opacity 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
}
.theme-card:hover .theme-overlay {
    opacity: 1;
}
.theme-overlay a {
    padding: 0.5rem 1rem;
    background: #fff;
    color: #1e1e2e;
    border-radius: var(--radius, 8px);
    text-decoration: none;
    font-size: 0.8125rem;
    font-weight: 500;
}
.theme-overlay a:hover {
    background: var(--accent, #8b5cf6);
    color: #fff;
}
.theme-info {
    padding: 1rem;
}
.theme-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.25rem;
    color: var(--text-primary);
}
.theme-desc {
    font-size: 0.8125rem;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    line-height: 1.4;
    min-height: 36px;
}
.theme-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.75rem;
    color: var(--text-muted);
}
.theme-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
.btn-danger {
    background: #ef4444 !important;
    color: #fff !important;
    border: none;
}
.btn-success {
    background: #10b981;
    color: #fff;
    border: 1px solid #10b981;
}
.btn-success:hover {
    background: #059669;
    border-color: #059669;
}
.btn-danger:hover {
    background: #dc2626 !important;
}
.empty-state {
    text-align: center;
    padding: 3rem;
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg, 12px);
}
.empty-state .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}
.upload-theme-box {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg, 12px);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}
.upload-theme-box h3 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    color: var(--text-primary);
}
.upload-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}
.upload-area {
    flex: 1;
    min-width: 250px;
}
.upload-area input[type="file"] {
    display: none;
}
.upload-area label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary, rgba(255,255,255,0.05));
    border: 2px dashed var(--border);
    border-radius: var(--radius, 8px);
    cursor: pointer;
    transition: all 0.2s;
}
.upload-area label:hover {
    border-color: var(--accent, #8b5cf6);
    background: var(--accent-muted, rgba(139, 92, 246, 0.1));
}
.upload-icon {
    font-size: 1.5rem;
}
.upload-text {
    color: var(--text-muted);
    font-size: 0.875rem;
}
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
