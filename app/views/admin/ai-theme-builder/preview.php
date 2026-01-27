<?php
/**
 * AI Theme Builder - Preview Theme
 */
ob_start();
?>

<style>
.preview-container {
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
}
.preview-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: var(--bg-tertiary);
    border-bottom: 1px solid var(--border);
}
.preview-toolbar h2 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.preview-actions {
    display: flex;
    gap: 8px;
}
.device-switcher {
    display: flex;
    gap: 4px;
    background: var(--bg-secondary);
    padding: 4px;
    border-radius: 8px;
}
.device-btn {
    padding: 8px 12px;
    background: transparent;
    border: none;
    border-radius: 6px;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.2s;
}
.device-btn.active {
    background: var(--bg-tertiary);
    color: var(--text-primary);
}
.preview-frame-wrapper {
    padding: 24px;
    background: var(--bg-primary);
    display: flex;
    justify-content: center;
    min-height: 600px;
}
.preview-iframe {
    border: 1px solid var(--border);
    border-radius: 8px;
    background: white;
    transition: width 0.3s ease;
}
.preview-iframe.desktop {
    width: 100%;
    max-width: 1200px;
}
.preview-iframe.tablet {
    width: 768px;
}
.preview-iframe.mobile {
    width: 375px;
}
.theme-info {
    padding: 20px;
    border-top: 1px solid var(--border);
}
.theme-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}
.theme-info-item {
    background: var(--bg-tertiary);
    padding: 16px;
    border-radius: 8px;
}
.theme-info-item label {
    font-size: 11px;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.theme-info-item .value {
    font-size: 14px;
    font-weight: 500;
    margin-top: 4px;
}
.color-swatches {
    display: flex;
    gap: 8px;
    margin-top: 8px;
}
.color-swatch {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    border: 1px solid rgba(255,255,255,0.1);
}
</style>

<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="/admin/ai-theme-builder" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">
            ← Back to Themes
        </a>
        <h1 style="font-size: 24px; font-weight: 700; margin: 8px 0 0 0;">
            👁️ Preview: <?= esc($themeConfig['title'] ?? $themeName) ?>
        </h1>
    </div>
    <div style="display: flex; gap: 12px;">
        <form method="post" action="/admin/ai-theme-builder/activate/<?= esc($themeName) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">✓ Activate This Theme</button>
        </form>
    </div>
</div>

<div class="preview-container">
    <div class="preview-toolbar">
        <h2>🖥️ Live Preview</h2>
        <div class="preview-actions">
            <div class="device-switcher">
                <button type="button" class="device-btn active" data-device="desktop" title="Desktop">🖥️</button>
                <button type="button" class="device-btn" data-device="tablet" title="Tablet">📱</button>
                <button type="button" class="device-btn" data-device="mobile" title="Mobile">📲</button>
            </div>
            <a href="/?preview_theme=<?= esc($themeName) ?>" target="_blank" class="btn btn-secondary btn-sm">
                🔗 Open in New Tab
            </a>
        </div>
    </div>
    
    <div class="preview-frame-wrapper">
        <iframe 
            src="/?preview_theme=<?= esc($themeName) ?>" 
            class="preview-iframe desktop"
            id="preview-iframe"
            height="600"
        ></iframe>
    </div>
    
    <?php if (!empty($themeConfig)): ?>
    <div class="theme-info">
        <div class="theme-info-grid">
            <div class="theme-info-item">
                <label>Theme Name</label>
                <div class="value"><?= esc($themeConfig['title'] ?? $themeName) ?></div>
            </div>
            <div class="theme-info-item">
                <label>Type</label>
                <div class="value"><?= esc(ucfirst($themeConfig['type'] ?? 'Custom')) ?></div>
            </div>
            <div class="theme-info-item">
                <label>Style</label>
                <div class="value"><?= esc(ucfirst($themeConfig['style'] ?? 'Modern')) ?></div>
            </div>
            <div class="theme-info-item">
                <label>Version</label>
                <div class="value"><?= esc($themeConfig['version'] ?? '1.0.0') ?></div>
            </div>
            <?php if (!empty($themeConfig['colors'])): ?>
            <div class="theme-info-item">
                <label>Colors</label>
                <div class="color-swatches">
                    <?php foreach ($themeConfig['colors'] as $name => $color): ?>
                    <div class="color-swatch" style="background: <?= esc($color) ?>" title="<?= esc($name) ?>"></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php if (!empty($themeConfig['created'])): ?>
            <div class="theme-info-item">
                <label>Created</label>
                <div class="value"><?= esc($themeConfig['created']) ?></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Device switcher
document.querySelectorAll('.device-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.device-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const iframe = document.getElementById('preview-iframe');
        iframe.className = 'preview-iframe ' + btn.dataset.device;
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
