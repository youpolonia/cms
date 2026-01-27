<?php
/**
 * Theme Customizer View - Professional UI
 */
$title = 'Customize: ' . esc($theme['name']);
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/themes" class="back-link">← Back to Themes</a>
        <h1 class="page-title"><?= esc($theme['name']) ?> Settings</h1>
        <p class="page-description">v<?= esc($theme['version']) ?></p>
    </div>
    <div class="page-header-actions">
        <a href="/?preview_theme=<?= urlencode($theme['slug']) ?>" target="_blank" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Preview
        </a>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<?php if (!$hasCustomize): ?>
<div class="card">
    <div class="card-body" style="text-align:center;padding:60px 20px;">
        <div style="font-size:48px;margin-bottom:16px;opacity:0.5;">🎨</div>
        <h3 style="margin-bottom:8px;">No Customization Options</h3>
        <p style="color:var(--text-muted);">This theme does not have customizable options.</p>
    </div>
</div>
<?php else: ?>

<form method="POST" action="/admin/themes/<?= esc($theme['slug']) ?>/customize" class="customize-form">
    <?= csrf_field() ?>
    
    <div class="customize-grid">
        <div class="customize-main">
            <!-- Layout Options -->
            <div class="card">
                <div class="card-header">
                    <h3>Layout Options</h3>
                </div>
                <div class="card-body">
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Show Header</strong>
                            <span>Display the default minimal header on all pages</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_header" value="1" <?= !empty($options['show_header']) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Show Footer</strong>
                            <span>Display the default minimal footer on all pages</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="show_footer" value="1" <?= !empty($options['show_footer']) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Appearance -->
            <div class="card">
                <div class="card-header">
                    <h3>Appearance</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="body_background">Body Background Color</label>
                        <div class="color-picker">
                            <input type="color" id="bg_picker" value="<?= esc($options['body_background'] ?? '#ffffff') ?>" onchange="document.getElementById('body_background').value=this.value">
                            <input type="text" id="body_background" name="body_background" value="<?= esc($options['body_background'] ?? '#ffffff') ?>" class="form-control" placeholder="#ffffff">
                        </div>
                    </div>
                    <div class="setting-row">
                        <div class="setting-info">
                            <strong>Preload Google Fonts</strong>
                            <span>Improve font loading performance</span>
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="preload_fonts" value="1" <?= !empty($options['preload_fonts']) ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="customize-sidebar">
            <!-- Theme Info -->
            <div class="card">
                <div class="card-header">
                    <h3>Theme Info</h3>
                </div>
                <div class="card-body">
                    <div class="info-list">
                        <div class="info-item">
                            <span class="info-label">Theme</span>
                            <span class="info-value"><?= esc($theme['name']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Version</span>
                            <span class="info-value"><?= esc($theme['version']) ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blank Canvas</span>
                            <span class="info-value badge <?= !empty($supports['blank-canvas']) ? 'badge-success' : 'badge-secondary' ?>"><?= !empty($supports['blank-canvas']) ? 'Yes' : 'No' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Theme Builder</span>
                            <span class="info-value badge <?= !empty($supports['theme-builder']) ? 'badge-success' : 'badge-secondary' ?>"><?= !empty($supports['theme-builder']) ? 'Yes' : 'No' ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save Changes
                    </button>
                    <a href="/admin/themes" class="btn btn-secondary btn-block" style="margin-top:10px;">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<style>
.back-link {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 8px;
}
.back-link:hover { color: var(--accent); }

.customize-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 900px) {
    .customize-grid { grid-template-columns: 1fr; }
}

.customize-main { display: flex; flex-direction: column; gap: 20px; }
.customize-sidebar { display: flex; flex-direction: column; gap: 20px; }

.card {
    background: var(--bg-primary);
    border: 1px solid var(--border);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    background: rgba(255,255,255,0.02);
}
.card-header h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 600;
}
.card-body { padding: 20px; }

.setting-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 0;
    border-bottom: 1px solid var(--border);
}
.setting-row:last-child { border-bottom: none; padding-bottom: 0; }
.setting-row:first-child { padding-top: 0; }

.setting-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.setting-info strong {
    font-size: 14px;
    font-weight: 500;
}
.setting-info span {
    font-size: 12px;
    color: var(--text-muted);
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute;
    cursor: pointer;
    top: 0; left: 0; right: 0; bottom: 0;
    background-color: var(--border);
    transition: 0.3s;
    border-radius: 26px;
}
.slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
input:checked + .slider { background-color: var(--accent); }
input:checked + .slider:before { transform: translateX(22px); }

/* Form Elements */
.form-group { margin-bottom: 20px; }
.form-group:last-child { margin-bottom: 0; }
.form-group label {
    display: block;
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 8px;
}
.form-control {
    width: 100%;
    padding: 10px 14px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent);
}

.color-picker {
    display: flex;
    gap: 12px;
    align-items: center;
}
.color-picker input[type="color"] {
    width: 48px;
    height: 42px;
    padding: 2px;
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    background: transparent;
}
.color-picker input[type="text"] {
    flex: 1;
    max-width: 140px;
}

/* Info List */
.info-list { display: flex; flex-direction: column; gap: 12px; }
.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.info-label {
    font-size: 13px;
    color: var(--text-muted);
}
.info-value {
    font-size: 13px;
    font-weight: 500;
}

/* Buttons */
.btn-block { width: 100%; justify-content: center; }
.btn svg { margin-right: 6px; }

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}
.badge-success { background: rgba(16,185,129,0.15); color: #10b981; }
.badge-secondary { background: var(--bg-tertiary); color: var(--text-muted); }
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
