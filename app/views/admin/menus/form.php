<?php
/**
 * Menu Form (Create/Edit) - Professional Design
 */
$title = $menu ? 'Edit Menu' : 'New Menu';
$isEdit = $menu !== null;
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/menus" class="back-link">← Back to Menus</a>
        <h1><?= $isEdit ? 'Edit Menu' : 'Create New Menu' ?></h1>
        <p class="page-subtitle"><?= $isEdit ? 'Update menu settings and configuration' : 'Set up a new navigation menu for your site' ?></p>
    </div>
</div>

<?php 
$flashSuccess = \Core\Session::getFlash('success');
$flashError = \Core\Session::getFlash('error');
?>

<?php if ($flashSuccess): ?>
    <div class="alert alert-success"><?= esc($flashSuccess) ?></div>
<?php endif; ?>

<?php if ($flashError): ?>
    <div class="alert alert-error"><?= esc($flashError) ?></div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? '/admin/menus/' . (int)$menu['id'] : '/admin/menus' ?>" class="menu-form">
    <?= csrf_field() ?>

    <div class="form-layout">
        <!-- Main Settings Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📋 Basic Information</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="name">Menu Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" required 
                           value="<?= esc($menu['name'] ?? '') ?>" 
                           placeholder="e.g. Main Navigation"
                           class="form-control">
                    <p class="form-hint">Choose a descriptive name for this menu</p>
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <div class="input-with-prefix">
                        <span class="input-prefix">menu/</span>
                        <input type="text" id="slug" name="slug" 
                               value="<?= esc($menu['slug'] ?? '') ?>" 
                               placeholder="main-navigation"
                               class="form-control">
                    </div>
                    <p class="form-hint">
                        Use in templates: <code>&lt;?= render_menu('<span id="slugPreview"><?= esc($menu['slug'] ?? 'slug') ?></span>') ?&gt;</code>
                    </p>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" 
                              placeholder="Describe where and how this menu will be used..."
                              class="form-control"><?= esc($menu['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Location Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📍 Display Location</h2>
            </div>
            <div class="card-body">
                <p class="section-intro">Choose where this menu should appear on your site:</p>
                
                <div class="location-grid">
                    <?php 
                    $currentLocation = $menu['location'] ?? '';
                    $locations = [
                        'header' => ['icon' => '🏠', 'label' => 'Header', 'desc' => 'Main site navigation'],
                        'footer' => ['icon' => '📋', 'label' => 'Footer', 'desc' => 'Footer links'],
                        'sidebar' => ['icon' => '📁', 'label' => 'Sidebar', 'desc' => 'Side navigation'],
                    ];
                    ?>
                    <?php foreach ($locations as $value => $loc): ?>
                        <label class="location-card <?= $currentLocation === $value ? 'selected' : '' ?>">
                            <input type="radio" name="location" value="<?= $value ?>" 
                                   <?= $currentLocation === $value ? 'checked' : '' ?>>
                            <div class="location-card-inner">
                                <span class="location-icon"><?= $loc['icon'] ?></span>
                                <span class="location-label"><?= $loc['label'] ?></span>
                                <span class="location-desc"><?= $loc['desc'] ?></span>
                            </div>
                            <span class="check-indicator">✓</span>
                        </label>
                    <?php endforeach; ?>
                    
                    <label class="location-card <?= empty($currentLocation) ? 'selected' : '' ?>">
                        <input type="radio" name="location" value="" <?= empty($currentLocation) ? 'checked' : '' ?>>
                        <div class="location-card-inner">
                            <span class="location-icon">🚫</span>
                            <span class="location-label">None</span>
                            <span class="location-desc">Manual placement</span>
                        </div>
                        <span class="check-indicator">✓</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">⚙️ Settings</h2>
            </div>
            <div class="card-body">
                <div class="settings-grid">
                    <div class="form-group">
                        <label for="max_depth">Maximum Depth</label>
                        <select id="max_depth" name="max_depth" class="form-control">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>" <?= ($menu['max_depth'] ?? 3) == $i ? 'selected' : '' ?>>
                                    <?= $i ?> level<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p class="form-hint">How many levels of nesting to allow</p>
                    </div>

                    <div class="form-group">
                        <label>Menu Status</label>
                        <input type="hidden" name="is_active" value="0">
                        <label class="switch-toggle">
                            <input type="checkbox" name="is_active" value="1" 
                                   <?= ($menu['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                            <span class="switch-text"></span>
                        </label>
                        <p class="form-hint">Inactive menus won't display on frontend</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="form-actions-bar">
        <a href="/admin/menus" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary btn-lg">
            <?= $isEdit ? '💾 Save Changes' : '✨ Create Menu' ?>
        </button>
    </div>
</form>

<style>
/* Alerts */
.alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    font-weight: 500;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #22c55e;
}
.alert-error {
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #ef4444;
}

/* Page Header */
.page-header {
    margin: -1.5rem -1.5rem 1.5rem -1.5rem;
    padding: 2rem 2rem 1.5rem 2rem;
    background: linear-gradient(135deg, var(--bg-secondary) 0%, var(--bg-primary) 100%);
    border-bottom: 1px solid var(--border-color);
}
.back-link {
    display: inline-block;
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    transition: color 0.2s;
}
.back-link:hover { color: var(--accent-color); }
.page-header h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--text-primary);
}
.page-subtitle {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.95rem;
}

/* Form Layout */
.form-layout {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    max-width: 800px;
}

/* Cards */
.card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
}
.card-header {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}
.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
}
.card-body {
    padding: 1.5rem;
}

/* Form Controls */
.form-group {
    margin-bottom: 1.25rem;
}
.form-group:last-child { margin-bottom: 0; }

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.9rem;
}
.required { color: #ef4444; }

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.form-control::placeholder {
    color: var(--text-muted);
    opacity: 0.7;
}

textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

select.form-control {
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    padding-right: 2.5rem;
}

/* Input with Prefix */
.input-with-prefix {
    display: flex;
    align-items: stretch;
}
.input-prefix {
    padding: 0.75rem 0.75rem;
    background: var(--bg-tertiary, #1e1e2e);
    border: 1px solid var(--border-color);
    border-right: none;
    border-radius: 8px 0 0 8px;
    color: var(--text-muted);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
}
.input-with-prefix .form-control {
    border-radius: 0 8px 8px 0;
}

.form-hint {
    margin: 0.5rem 0 0 0;
    font-size: 0.8rem;
    color: var(--text-muted);
}
.form-hint code {
    background: var(--bg-secondary);
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    font-family: 'Monaco', 'Menlo', monospace;
    font-size: 0.75rem;
    color: var(--accent-color);
}

.section-intro {
    margin: 0 0 1rem 0;
    color: var(--text-muted);
    font-size: 0.9rem;
}

/* Location Grid */
.location-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}

.location-card {
    position: relative;
    cursor: pointer;
}
.location-card input { 
    position: absolute;
    opacity: 0;
    pointer-events: none;
}
.location-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.25rem 1rem;
    background: var(--bg-secondary);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    transition: all 0.2s;
    text-align: center;
}
.location-card:hover .location-card-inner {
    border-color: var(--accent-color);
    background: rgba(99, 102, 241, 0.05);
}
.location-card.selected .location-card-inner {
    border-color: var(--accent-color);
    background: rgba(99, 102, 241, 0.1);
}
.location-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}
.location-label {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 0.95rem;
}
.location-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
}
.check-indicator {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    width: 24px;
    height: 24px;
    background: var(--accent-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.8rem;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.2s;
}
.location-card.selected .check-indicator {
    opacity: 1;
    transform: scale(1);
}

/* Settings Grid */
.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}
@media (max-width: 600px) {
    .settings-grid { grid-template-columns: 1fr; }
}

/* Switch Toggle */
.switch-toggle {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    padding: 8px 0;
    user-select: none;
}
.switch-toggle input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
    pointer-events: none;
}
.switch-slider {
    position: relative;
    display: inline-block;
    width: 52px;
    height: 28px;
    background: #4b5563;
    border-radius: 14px;
    transition: background 0.25s ease;
    flex-shrink: 0;
}
.switch-slider::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 22px;
    height: 22px;
    background: white;
    border-radius: 50%;
    transition: transform 0.25s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.switch-toggle input:checked + .switch-slider {
    background: #22c55e;
}
.switch-toggle input:checked + .switch-slider::after {
    transform: translateX(24px);
}
.switch-text {
    font-weight: 500;
    font-size: 0.95rem;
}
.switch-text::after {
    content: 'Inactive';
    color: var(--text-muted);
}
.switch-toggle input:checked ~ .switch-text::after {
    content: 'Active';
    color: #22c55e;
}

/* Form Actions */
.form-actions-bar {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem 0;
    margin-top: 0.5rem;
    border-top: 1px solid var(--border-color);
    max-width: 800px;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.btn-primary {
    background: var(--accent-color);
    color: white;
}
.btn-primary:hover {
    background: var(--accent-hover, #4f46e5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}
.btn-secondary {
    background: var(--bg-secondary);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}
.btn-secondary:hover {
    background: var(--bg-tertiary, #313244);
}
.btn-lg {
    padding: 0.875rem 2rem;
    font-size: 1rem;
}
</style>

<script>
// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');
const slugPreview = document.getElementById('slugPreview');

nameInput?.addEventListener('input', function() {
    if (!slugInput.dataset.manual) {
        const slug = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        slugInput.value = slug;
        slugPreview.textContent = slug || 'slug';
    }
});

slugInput?.addEventListener('input', function() { 
    this.dataset.manual = 'true';
    slugPreview.textContent = this.value || 'slug';
});

// Location card selection
document.querySelectorAll('.location-card input').forEach(input => {
    input.addEventListener('change', function() {
        document.querySelectorAll('.location-card').forEach(card => card.classList.remove('selected'));
        this.closest('.location-card').classList.add('selected');
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
