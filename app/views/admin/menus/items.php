<?php
/**
 * Menu Items Management - Professional UI
 */
$title = 'Menu Items: ' . esc($menu['name']);
ob_start();

// Build tree structure
$topLevel = [];
$children = [];
foreach ($items as $item) {
    if (empty($item['parent_id'])) {
        $topLevel[] = $item;
    } else {
        $children[$item['parent_id']][] = $item;
    }
}

// Get articles for linking
$pdo = db();
$articlesStmt = $pdo->query("SELECT id, title, slug FROM articles WHERE status = 'published' ORDER BY title ASC");
$articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

// Extended icon palette
$icons = ['🏠','📄','📁','📂','🔗','📰','📝','📋','📑','📚','📖','✏️','📓','🗒️','📃','🗂️',
'📞','☎️','📱','✉️','📧','📩','💬','💭','🗨️','📢','📣','🔔','👤','👥','👨','👩','👨‍💼','👩‍💼','🧑‍💻','🤝','👋',
'💼','🏢','🏪','📊','📈','📉','💰','💵','🏦','💳','🧾','🛒','🛍️','🏷️','💎','🎁','📦','✨','⭐','💫',
'💻','🖥️','⌨️','🔌','📡','📶','💡','🔧','⚙️','🔩','🎨','📷','🎬','🎥','🎵','🎤','🎧','📻','🖼️',
'📍','🗺️','🌍','🌎','✈️','🚗','🚀','🧭','⏰','📅','🗓️','⏳','📆','🌅','🌙',
'🔒','🔓','🔑','🗝️','🛡️','🔐','🚫','⛔','✅','❌','⚠️','❤️','💖','🧡','💛','💚','💙','💜','❓','❗','💯','🔥'];
?>

<style>
.menu-items-layout { display: grid; grid-template-columns: 1fr 380px; gap: 1.5rem; align-items: start; }
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color, #313244); }
.back-link { color: var(--text-muted, #a6adc8); text-decoration: none; font-size: 0.875rem; margin-bottom: 0.25rem; display: inline-block; }
.back-link:hover { color: var(--primary, #89b4fa); }
.page-header h1 { margin: 0.25rem 0 0.5rem; font-size: 1.75rem; }
.page-subtitle { color: var(--text-muted, #a6adc8); margin: 0; font-size: 0.9rem; }
.card { background: var(--card-bg, #1e1e2e); border: 1px solid var(--border-color, #313244); border-radius: 12px; overflow: hidden; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color, #313244); background: var(--card-header-bg, #181825); }
.card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.card-title { margin: 0; font-size: 1rem; font-weight: 600; }
.count-badge { background: var(--primary, #89b4fa); color: var(--bg, #1e1e2e); padding: 0.15rem 0.5rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }
.card-body { padding: 1.25rem; }
.empty-state { text-align: center; padding: 3rem 1.5rem; color: var(--text-muted, #a6adc8); }
.empty-icon { font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5; }
.bulk-actions { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 1.25rem; border-bottom: 1px solid var(--border-color, #313244); background: var(--bg, #11111b); }
.checkbox-wrapper { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; }
.items-tree { max-height: 600px; overflow-y: auto; }
.item-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1rem; border-bottom: 1px solid var(--border-color, #313244); transition: background 0.15s; }
.item-row:hover { background: var(--hover-bg, #313244); }
.item-row.dragging { opacity: 0.5; background: var(--primary, #89b4fa); }
.item-content { display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 0; }
.drag-handle { cursor: grab; color: var(--text-muted, #a6adc8); padding: 0.25rem; user-select: none; }
.drag-handle:active { cursor: grabbing; }
.level-indicator { color: var(--text-muted, #a6adc8); font-size: 0.875rem; }
.item-icon { font-size: 1.1rem; }
.item-title { font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.badge { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 4px; text-transform: uppercase; font-weight: 600; }
.badge-page { background: #89b4fa22; color: #89b4fa; }
.badge-url { background: #a6e3a122; color: #a6e3a1; }
.badge-none { background: #45475a; color: #a6adc8; }
.item-actions { display: flex; gap: 0.25rem; opacity: 0; transition: opacity 0.15s; }
.item-row:hover .item-actions { opacity: 1; }
.action-btn { background: transparent; border: none; padding: 0.35rem; cursor: pointer; border-radius: 4px; font-size: 0.9rem; transition: background 0.15s; color: inherit; text-decoration: none; }
.action-btn:hover { background: var(--hover-bg, #45475a); }
.action-delete:hover { background: #f38ba822; }
.status-btn.active { color: #a6e3a1; }
.inline-form { display: inline; }
.drag-hint { padding: 0.75rem 1.25rem; font-size: 0.8rem; color: var(--text-muted, #a6adc8); background: var(--bg, #11111b); text-align: center; }
.form-panel { position: sticky; top: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.875rem; font-weight: 500; color: var(--text-primary, #cdd6f4); }
.required { color: #f38ba8; }
.form-control { width: 100%; padding: 0.6rem 0.75rem; background: var(--input-bg, #313244); border: 1px solid var(--border-color, #45475a); border-radius: 6px; color: var(--text-primary, #cdd6f4); font-size: 0.9rem; }
.form-control:focus { outline: none; border-color: var(--primary, #89b4fa); box-shadow: 0 0 0 3px rgba(137, 180, 250, 0.15); }
.form-control::placeholder { color: var(--text-muted, #6c7086); }
.form-hint { font-size: 0.8rem; color: var(--text-muted, #a6adc8); margin-top: 0.35rem; }
.icon-input-row { display: flex; gap: 0.5rem; }
.icon-input-row .form-control { flex: 1; cursor: pointer; }
.icon-picker { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem; margin-top: 0.5rem; max-height: 180px; overflow-y: auto; padding: 0.5rem; background: var(--input-bg, #313244); border-radius: 6px; }
.emoji-btn { background: transparent; border: none; padding: 0.4rem; font-size: 1.25rem; cursor: pointer; border-radius: 4px; transition: background 0.15s, transform 0.1s; }
.emoji-btn:hover { background: var(--hover-bg, #45475a); transform: scale(1.15); }
.link-tabs { display: flex; gap: 0.25rem; background: var(--input-bg, #313244); padding: 0.25rem; border-radius: 6px; }
.tab-btn { flex: 1; padding: 0.5rem 0.75rem; background: transparent; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; color: var(--text-primary, #cdd6f4); transition: background 0.15s; }
.tab-btn:hover { background: var(--hover-bg, #45475a); }
.tab-btn.active { background: var(--primary, #89b4fa); color: var(--bg, #1e1e2e); }
.link-tab-content { margin-top: 0.75rem; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.6rem 1rem; border: none; border-radius: 6px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.15s; }
.btn-primary { background: var(--primary, #89b4fa); color: var(--bg, #1e1e2e); }
.btn-primary:hover { background: #7ba3e8; }
.btn-secondary { background: var(--secondary-bg, #45475a); color: var(--text-primary, #cdd6f4); }
.btn-secondary:hover { background: #585b70; }
.btn-danger { background: #f38ba8; color: #1e1e2e; }
.btn-sm { padding: 0.4rem 0.75rem; font-size: 0.8rem; }
.btn-block { width: 100%; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.alert { padding: 0.875rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
.alert-success { background: #a6e3a122; border: 1px solid #a6e3a144; color: #a6e3a1; }
.alert-error { background: #f38ba822; border: 1px solid #f38ba844; color: #f38ba8; }
.modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; }
.modal.active { display: flex; }
.modal-content { background: var(--card-bg, #1e1e2e); border-radius: 12px; width: 90%; max-width: 600px; max-height: 80vh; overflow: hidden; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color, #313244); }
.modal-header h3 { margin: 0; }
.modal-close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted, #a6adc8); }
.modal-body { padding: 1.25rem; overflow-y: auto; max-height: calc(80vh - 60px); }
@media (max-width: 900px) { .menu-items-layout { grid-template-columns: 1fr; } .form-panel { position: static; } }
</style>

<div class="page-header">
    <div class="page-header-content">
        <a href="/admin/menus" class="back-link">← Back to Menus</a>
        <h1><?= esc($menu['name']) ?></h1>
        <p class="page-subtitle">Manage navigation items • Drag to reorder</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-secondary" onclick="previewMenu(<?= (int)$menu['id'] ?>)">👁️ Preview</button>
    </div>
</div>

<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success"><?= esc($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?></div>
<?php endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-error"><?= esc($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
<?php endif; ?>

<div class="menu-items-layout">
    <div class="card items-panel">
        <div class="card-header">
            <div class="card-header-left">
                <h2 class="card-title">📋 Menu Items</h2>
                <span class="count-badge"><?= count($items) ?></span>
            </div>
        </div>

        <?php if (empty($items)): ?>
        <div class="empty-state">
            <span class="empty-icon">📝</span>
            <p>No items yet. Add your first item using the form.</p>
        </div>
        <?php else: ?>
        <div class="bulk-actions">
            <label class="checkbox-wrapper">
                <input type="checkbox" id="selectAll">
                <span>Select All</span>
            </label>
            <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled onclick="bulkDelete()">🗑️ Delete Selected</button>
        </div>

        <div class="items-tree" id="sortable-items">
            <?php 
            function renderItemRow($item, $menu, $children, $level = 0) {
                $indent = $level * 28;
                $hasChildren = isset($children[$item['id']]);
                $isActive = ($item['is_active'] ?? 1) == 1;
                $icon = $item['icon'] ?? '';
            ?>
            <div class="item-row" data-id="<?= (int)$item['id'] ?>" data-parent="<?= (int)($item['parent_id'] ?? 0) ?>" draggable="true">
                <div class="item-content" style="padding-left: <?= $indent + 12 ?>px;">
                    <span class="drag-handle" title="Drag to reorder">⋮⋮</span>
                    <input type="checkbox" class="item-checkbox" value="<?= (int)$item['id'] ?>">
                    <?php if ($level > 0): ?><span class="level-indicator">↳</span><?php endif; ?>
                    <?php if ($icon): ?><span class="item-icon"><?= esc($icon) ?></span><?php endif; ?>
                    <span class="item-title"><?= esc($item['title']) ?></span>
                    <?php if ($item['page_id']): ?>
                        <span class="badge badge-page">Page</span>
                    <?php elseif ($item['url']): ?>
                        <span class="badge badge-url" title="<?= esc($item['url']) ?>">URL</span>
                    <?php else: ?>
                        <span class="badge badge-none">—</span>
                    <?php endif; ?>
                </div>
                <div class="item-actions">
                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/toggle" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="action-btn status-btn <?= $isActive ? 'active' : '' ?>" title="<?= $isActive ? 'Visible' : 'Hidden' ?>"><?= $isActive ? '👁️' : '👁️‍🗨️' ?></button>
                    </form>
                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/edit" class="action-btn" title="Edit">✏️</a>
                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/clone" class="inline-form">
                        <?= csrf_field() ?>
                        <button type="submit" class="action-btn" title="Clone">📋</button>
                    </form>
                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/<?= (int)$item['id'] ?>/delete" class="inline-form" onsubmit="return confirm('Delete this item?');">
                        <?= csrf_field() ?>
                        <button type="submit" class="action-btn action-delete" title="Delete">🗑️</button>
                    </form>
                </div>
            </div>
            <?php
                if ($hasChildren) {
                    foreach ($children[$item['id']] as $child) {
                        renderItemRow($child, $menu, $children, $level + 1);
                    }
                }
            }
            foreach ($topLevel as $item) {
                renderItemRow($item, $menu, $children, 0);
            }
            ?>
        </div>
        <div class="drag-hint">Drag items to reorder • Indented items are children</div>
        <?php endif; ?>
    </div>

    <div class="card form-panel">
        <div class="card-header"><h3 class="card-title">➕ Add Item</h3></div>
        <div class="card-body">
            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items" id="addItemForm">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="Link text">
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <div class="icon-input-row">
                        <input type="text" id="icon" name="icon" class="form-control" placeholder="Click to pick..." readonly>
                        <button type="button" class="btn btn-sm btn-secondary" id="clearIcon" style="display:none;">✕</button>
                    </div>
                    <div class="icon-picker" id="iconPicker">
                        <?php foreach ($icons as $emoji): ?>
                        <button type="button" class="emoji-btn" data-emoji="<?= $emoji ?>"><?= $emoji ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (!empty($items)): ?>
                <div class="form-group">
                    <label for="parent_id">Parent Item</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">— Top Level —</option>
                        <?php foreach ($items as $mi): if (empty($mi['parent_id'])): ?>
                        <option value="<?= (int)$mi['id'] ?>"><?= esc($mi['title']) ?></option>
                        <?php endif; endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Link Type</label>
                    <div class="link-tabs">
                        <button type="button" class="tab-btn active" data-tab="page">📄 Page</button>
                        <button type="button" class="tab-btn" data-tab="article">📰 Article</button>
                        <button type="button" class="tab-btn" data-tab="url">🔗 URL</button>
                        <button type="button" class="tab-btn" data-tab="none">—</button>
                    </div>
                </div>
                <div id="tab-page" class="link-tab-content">
                    <div class="form-group">
                        <label for="page_id">Select Page</label>
                        <select id="page_id" name="page_id" class="form-control">
                            <option value="">— Select Page —</option>
                            <?php foreach ($pages as $page): ?>
                            <option value="<?= (int)$page['id'] ?>"><?= esc($page['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div id="tab-article" class="link-tab-content" style="display:none;">
                    <div class="form-group">
                        <label for="article_url">Select Article</label>
                        <select id="article_url" class="form-control" onchange="document.getElementById('url').value = this.value ? '/article/' + this.value : '';">
                            <option value="">— Select Article —</option>
                            <?php foreach ($articles as $article): ?>
                            <option value="<?= esc($article['slug']) ?>"><?= esc($article['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Links to: /article/[slug]</p>
                    </div>
                </div>
                <div id="tab-url" class="link-tab-content" style="display:none;">
                    <div class="form-group">
                        <label for="url">Custom URL</label>
                        <input type="text" id="url" name="url" class="form-control" placeholder="https://... or /page">
                    </div>
                </div>
                <div id="tab-none" class="link-tab-content" style="display:none;">
                    <p class="form-hint">No link - useful for dropdown parent items.</p>
                </div>
                <div class="form-group">
                    <label for="visibility">Visibility</label>
                    <select id="visibility" name="visibility" class="form-control">
                        <option value="all">👁️ All visitors</option>
                        <option value="logged_in">🔓 Logged in only</option>
                        <option value="logged_out">🔒 Logged out only</option>
                        <option value="admin">👑 Admins only</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="open_in_new_tab" value="1">
                        <span>Open in new tab</span>
                    </label>
                </div>
                <div class="form-group">
                    <label for="css_class">CSS Class</label>
                    <input type="text" id="css_class" name="css_class" class="form-control" placeholder="Optional">
                </div>
                <div class="form-group">
                    <label for="description">Tooltip</label>
                    <input type="text" id="description" name="description" class="form-control" placeholder="Shows on hover">
                </div>
                <button type="submit" class="btn btn-primary btn-block">➕ Add Item</button>
            </form>
        </div>
    </div>
</div>

<div id="previewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Menu Preview</h3>
            <button type="button" class="modal-close" onclick="closeModal('previewModal')">&times;</button>
        </div>
        <div class="modal-body" id="previewContent"></div>
    </div>
</div>

<form id="bulkDeleteForm" method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/items/bulk-delete" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="ids" id="bulkDeleteIds">
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon');
    const clearIcon = document.getElementById('clearIcon');
    
    document.getElementById('iconPicker').querySelectorAll('.emoji-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            iconInput.value = this.dataset.emoji;
            clearIcon.style.display = 'block';
        });
    });
    
    clearIcon.addEventListener('click', function() {
        iconInput.value = '';
        this.style.display = 'none';
    });
    
    if (iconInput.value) clearIcon.style.display = 'block';
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            document.querySelectorAll('.link-tab-content').forEach(c => c.style.display = 'none');
            document.getElementById('tab-' + this.dataset.tab).style.display = 'block';
            if (this.dataset.tab !== 'page') document.getElementById('page_id').value = '';
            if (this.dataset.tab !== 'url' && this.dataset.tab !== 'article') document.getElementById('url').value = '';
            if (this.dataset.tab === 'article') document.getElementById('article_url').value = '';
        });
    });
    
    const selectAll = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            document.querySelectorAll('.item-checkbox').forEach(cb => cb.checked = this.checked);
            updateBulkBtn();
        });
    }
    document.querySelectorAll('.item-checkbox').forEach(cb => cb.addEventListener('change', updateBulkBtn));
    function updateBulkBtn() {
        const checked = document.querySelectorAll('.item-checkbox:checked').length;
        if (bulkDeleteBtn) bulkDeleteBtn.disabled = checked === 0;
    }
    
    const sortable = document.getElementById('sortable-items');
    if (sortable) {
        let draggedItem = null;
        sortable.querySelectorAll('.item-row').forEach(item => {
            item.addEventListener('dragstart', function(e) {
                draggedItem = this;
                this.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
            });
            item.addEventListener('dragend', function() {
                this.classList.remove('dragging');
                draggedItem = null;
                saveOrder();
            });
            item.addEventListener('dragover', function(e) {
                e.preventDefault();
                if (draggedItem && draggedItem !== this) {
                    const rect = this.getBoundingClientRect();
                    const midY = rect.top + rect.height / 2;
                    if (e.clientY < midY) sortable.insertBefore(draggedItem, this);
                    else sortable.insertBefore(draggedItem, this.nextSibling);
                }
            });
        });
    }
});

function saveOrder() {
    const items = document.querySelectorAll('.item-row');
    const order = [];
    items.forEach((item, index) => {
        order.push({ id: item.dataset.id, position: index, parent_id: item.dataset.parent || 0 });
    });
    fetch('/admin/menus/<?= (int)$menu['id'] ?>/items/reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= csrf_token() ?>' },
        body: JSON.stringify({ items: order })
    });
}

function bulkDelete() {
    const ids = [];
    document.querySelectorAll('.item-checkbox:checked').forEach(cb => ids.push(cb.value));
    if (ids.length && confirm('Delete ' + ids.length + ' selected items?')) {
        document.getElementById('bulkDeleteIds').value = ids.join(',');
        document.getElementById('bulkDeleteForm').submit();
    }
}

function previewMenu(menuId) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    content.innerHTML = '<p>Loading preview...</p>';
    modal.classList.add('active');
    fetch('/admin/menus/' + menuId + '/preview').then(r => r.text()).then(html => content.innerHTML = html).catch(() => content.innerHTML = '<p>Failed to load preview</p>');
}

function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';