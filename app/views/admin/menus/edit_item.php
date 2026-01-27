<?php
/**
 * Edit Menu Item - Full Upgrade
 */
$title = 'Edit Menu Item';
ob_start();

// Common emojis for icon picker
$emojis = ['🏠', '📄', '📁', '📰', '📞', '✉️', '👤', '👥', '🛒', '💼', '🔧', '⚙️', '📊', '📈', '🎯', '🔍', '❓', '📚', '🎨', '🌐', '📱', '💡', '🔒', '🎁', '⭐', '❤️', '📍', '🗓️', '📋', '🔗'];

// Determine current link type
$linkType = 'none';
if ($item['page_id']) {
    $linkType = 'page';
} elseif ($item['url']) {
    $linkType = 'url';
}
?>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2 class="card-title">Edit Item: <?= esc($item['title']) ?></h2>
        <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="btn btn-secondary btn-sm">← Back to Items</a>
    </div>
    <div class="card-body">
        <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="<?= esc($item['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="icon">Icon</label>
                <div class="icon-input-wrapper">
                    <input type="text" id="icon" name="icon" value="<?= esc($item['icon'] ?? '') ?>" placeholder="Click to pick..." readonly style="cursor: pointer;">
                    <button type="button" class="btn btn-sm" id="clearIcon" style="<?= empty($item['icon']) ? 'display:none;' : '' ?>">×</button>
                </div>
                <div class="icon-picker" id="iconPicker" style="display: none;">
                    <?php foreach ($emojis as $emoji): ?>
                        <button type="button" class="emoji-btn" data-emoji="<?= $emoji ?>"><?= $emoji ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="parent_id">Parent Item</label>
                <select id="parent_id" name="parent_id">
                    <option value="">— No Parent (Top Level) —</option>
                    <?php foreach ($menuItems as $mi): ?>
                        <option value="<?= (int)$mi['id'] ?>" <?= ($item['parent_id'] == $mi['id']) ? 'selected' : '' ?>>
                            <?= esc($mi['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-hint">Select a parent to make this a dropdown item</small>
            </div>

            <div class="form-group">
                <label>Link Type</label>
                <div class="link-type-tabs">
                    <button type="button" class="tab-btn <?= $linkType === 'page' ? 'active' : '' ?>" data-tab="page">Page</button>
                    <button type="button" class="tab-btn <?= $linkType === 'url' ? 'active' : '' ?>" data-tab="url">URL</button>
                    <button type="button" class="tab-btn <?= $linkType === 'none' ? 'active' : '' ?>" data-tab="none">None</button>
                </div>
            </div>

            <div class="form-group link-tab" id="tab-page" style="<?= $linkType !== 'page' ? 'display:none;' : '' ?>">
                <label for="page_id">Select Page</label>
                <select id="page_id" name="page_id">
                    <option value="">— Select Page —</option>
                    <?php foreach ($pages as $page): ?>
                        <option value="<?= (int)$page['id'] ?>" <?= ($item['page_id'] == $page['id']) ? 'selected' : '' ?>>
                            <?= esc($page['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group link-tab" id="tab-url" style="<?= $linkType !== 'url' ? 'display:none;' : '' ?>">
                <label for="url">Custom URL</label>
                <input type="text" id="url" name="url" value="<?= esc($item['url'] ?? '') ?>" placeholder="https://... or /page">
            </div>

            <div class="form-group link-tab" id="tab-none" style="<?= $linkType !== 'none' ? 'display:none;' : '' ?>">
                <p style="color: var(--text-muted); font-size: 0.85rem;">
                    This item won't link anywhere. Useful for dropdown parent items.
                </p>
            </div>

            <div class="form-group">
                <label for="visibility">Visibility</label>
                <select id="visibility" name="visibility">
                    <option value="all" <?= ($item['visibility'] ?? 'all') === 'all' ? 'selected' : '' ?>>All visitors</option>
                    <option value="logged_in" <?= ($item['visibility'] ?? '') === 'logged_in' ? 'selected' : '' ?>>Logged in only</option>
                    <option value="logged_out" <?= ($item['visibility'] ?? '') === 'logged_out' ? 'selected' : '' ?>>Logged out only</option>
                    <option value="admin" <?= ($item['visibility'] ?? '') === 'admin' ? 'selected' : '' ?>>Admins only</option>
                </select>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="open_in_new_tab" value="1" <?= ($item['open_in_new_tab'] ?? $item['target'] === '_blank') ? 'checked' : '' ?>>
                    Open in new tab
                </label>
            </div>

            <div class="form-group">
                <label for="css_class">CSS Class</label>
                <input type="text" id="css_class" name="css_class" value="<?= esc($item['css_class'] ?? '') ?>" placeholder="Optional styling class">
            </div>

            <div class="form-group">
                <label for="description">Description / Tooltip</label>
                <input type="text" id="description" name="description" value="<?= esc($item['description'] ?? '') ?>" placeholder="Shows on hover">
            </div>

            <!-- Keep target for backward compatibility -->
            <input type="hidden" name="target" value="<?= ($item['open_in_new_tab'] ?? $item['target'] === '_blank') ? '_blank' : '_self' ?>">

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="btn btn-secondary">Cancel</a>
                
                <div style="margin-left: auto; display: flex; gap: 0.5rem;">
                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/clone" style="margin: 0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-secondary" title="Clone this item">📋 Clone</button>
                    </form>
                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/delete" onsubmit="return confirm('Delete this item?');" style="margin: 0;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-danger">🗑️ Delete</button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.icon-input-wrapper {
    display: flex;
    gap: 0.5rem;
}
.icon-input-wrapper input { flex: 1; }

.icon-picker {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    padding: 0.5rem;
    background: var(--bg-secondary);
    border-radius: 6px;
    margin-top: 0.5rem;
}
.emoji-btn {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 4px;
    font-size: 1.1rem;
}
.emoji-btn:hover { background: var(--bg-primary); }

.link-type-tabs {
    display: flex;
    gap: 0;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    overflow: hidden;
}
.tab-btn {
    flex: 1;
    padding: 0.5rem;
    border: none;
    background: var(--bg-secondary);
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.15s;
}
.tab-btn:not(:last-child) { border-right: 1px solid var(--border-color); }
.tab-btn.active { background: var(--accent-color); color: white; }
.tab-btn:hover:not(.active) { background: var(--bg-primary); }

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.form-hint {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.8rem;
    color: var(--text-muted);
}
</style>

<script>
// Icon picker
const iconInput = document.getElementById('icon');
const iconPicker = document.getElementById('iconPicker');
const clearIconBtn = document.getElementById('clearIcon');

iconInput?.addEventListener('click', () => {
    iconPicker.style.display = iconPicker.style.display === 'none' ? 'flex' : 'none';
});

document.querySelectorAll('.emoji-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        iconInput.value = btn.dataset.emoji;
        iconPicker.style.display = 'none';
        clearIconBtn.style.display = 'inline-block';
    });
});

clearIconBtn?.addEventListener('click', () => {
    iconInput.value = '';
    clearIconBtn.style.display = 'none';
});

// Link type tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.link-tab').forEach(t => t.style.display = 'none');
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
        
        // Clear other inputs
        if (this.dataset.tab !== 'page') document.getElementById('page_id').value = '';
        if (this.dataset.tab !== 'url') document.getElementById('url').value = '';
    });
});

// Update hidden target field when checkbox changes
document.querySelector('input[name="open_in_new_tab"]')?.addEventListener('change', function() {
    document.querySelector('input[name="target"]').value = this.checked ? '_blank' : '_self';
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
