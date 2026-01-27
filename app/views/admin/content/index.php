<?php
/**
 * Content Blocks List - Full Upgrade
 */
$title = 'Content Blocks';
ob_start();

$typeIcons = [
    'html' => '</>',
    'text' => 'Aa',
    'json' => '{}',
    'markdown' => 'MD',
    'shortcode' => '[]'
];

$categoryIcons = [
    'header' => '🏠',
    'footer' => '📋',
    'sidebar' => '📁',
    'global' => '🌍',
    'uncategorized' => '📦'
];
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Content Blocks</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="/admin/content/export" class="btn btn-secondary btn-sm">📤 Export</a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='flex'">
                📥 Import
            </button>
            <a href="/admin/content/create" class="btn btn-primary btn-sm">+ New Block</a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
        <form method="get" action="/admin/content" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Search blocks..." class="filter-input">
            </div>
            <div class="filter-group">
                <select name="type" class="filter-select">
                    <option value="">All Types</option>
                    <?php foreach ($types as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($filterType ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($filterCategory ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if (!empty($filterType) || !empty($filterCategory) || !empty($search)): ?>
                <a href="/admin/content" class="btn btn-ghost btn-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($blocks)): ?>
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--text-primary);">No content blocks yet</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                Create reusable content blocks for your site templates.
            </p>
            <a href="/admin/content/create" class="btn btn-primary">Create First Block</a>
        </div>
    <?php else: ?>
        <!-- Bulk Actions Bar -->
        <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
            <form method="post" action="/admin/content/bulk-delete" id="bulkDeleteForm" onsubmit="return confirmBulkDelete();">
                <?= csrf_field() ?>
                <div class="bulk-actions-inner">
                    <span class="selected-count"><span id="selectedCount">0</span> selected</span>
                    <button type="submit" class="btn btn-danger btn-sm">🗑️ Delete Selected</button>
                </div>
                <div id="bulkIdsContainer"></div>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th style="text-align: center;">Status</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blocks as $block): ?>
                    <?php
                    $isActive = ($block['is_active'] ?? 1) == 1;
                    $typeIcon = $typeIcons[$block['type']] ?? '</>';
                    $catIcon = $categoryIcons[$block['category'] ?? 'uncategorized'] ?? '📦';
                    ?>
                    <tr data-id="<?= (int)$block['id'] ?>">
                        <td>
                            <input type="checkbox" class="row-checkbox" value="<?= (int)$block['id'] ?>" onchange="updateBulkSelection()">
                        </td>
                        <td>
                            <strong><?= esc($block['name']) ?></strong>
                            <?php if (!empty($block['description'])): ?>
                                <br><small style="color: var(--text-muted);"><?= esc(mb_substr($block['description'], 0, 50)) ?><?= mb_strlen($block['description']) > 50 ? '...' : '' ?></small>
                            <?php endif; ?>
                            <?php if (isset($block['version']) && $block['version'] > 1): ?>
                                <span class="version-badge">v<?= (int)$block['version'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td><code style="font-size: 0.85rem;"><?= esc($block['slug']) ?></code></td>
                        <td>
                            <span class="badge badge-type"><?= $typeIcon ?> <?= esc($types[$block['type']] ?? $block['type']) ?></span>
                        </td>
                        <td>
                            <span class="badge badge-category"><?= $catIcon ?> <?= ucfirst(esc($block['category'] ?? 'uncategorized')) ?></span>
                        </td>
                        <td style="text-align: center;">
                            <form method="post" action="/admin/content/<?= (int)$block['id'] ?>/toggle" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="status-toggle <?= $isActive ? 'active' : 'inactive' ?>" title="<?= $isActive ? 'Click to deactivate' : 'Click to activate' ?>">
                                    <?= $isActive ? '● Active' : '○ Inactive' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="actions-dropdown">
                                <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" onclick="toggleDropdown(this)">
                                    Actions ▾
                                </button>
                                <div class="dropdown-menu">
                                    <a href="/admin/content/<?= (int)$block['id'] ?>/edit" class="dropdown-item">
                                        ✏️ Edit Block
                                    </a>
                                    <button type="button" class="dropdown-item" onclick="previewBlock(<?= (int)$block['id'] ?>)">
                                        👁️ Preview
                                    </button>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/content/<?= (int)$block['id'] ?>/duplicate" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item">
                                            📋 Duplicate
                                        </button>
                                    </form>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/content/<?= (int)$block['id'] ?>/delete" onsubmit="return confirm('Delete this content block?');" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3>Import Content Blocks</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</button>
        </div>
        <form method="post" action="/admin/content/import" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="import_file">Select JSON File</label>
                    <input type="file" id="import_file" name="import_file" accept=".json" required>
                    <small style="color: var(--text-muted);">Upload a previously exported content blocks JSON file</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('importModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary">Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="previewTitle">Block Preview</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('previewModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div id="previewMeta" style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);"></div>
            <div id="previewContent" style="min-height: 100px; max-height: 400px; overflow: auto;">
                <p style="color: var(--text-muted); text-align: center;">Loading...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Filters Bar */
.filters-bar {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}
.filters-form {
    display: flex;
    gap: 0.75rem;
    align-items: center;
    flex-wrap: wrap;
}
.filter-group {
    flex: 0 0 auto;
}
.filter-input {
    padding: 0.5rem 0.75rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 0.9rem;
    min-width: 200px;
}
.filter-select {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 0.9rem;
    cursor: pointer;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239ca3af' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.5rem center;
}

/* Bulk Actions Bar */
.bulk-actions-bar {
    padding: 0.75rem 1.5rem;
    background: rgba(99, 102, 241, 0.1);
    border-bottom: 1px solid var(--accent-color);
}
.bulk-actions-inner {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.selected-count {
    font-weight: 500;
    color: var(--accent-color);
}

/* Badges */
.badge-type {
    background: #e0f2fe;
    color: #0369a1;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-family: monospace;
}
.badge-category {
    background: var(--bg-secondary);
    color: var(--text-primary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
}
.version-badge {
    display: inline-block;
    margin-left: 0.5rem;
    padding: 0.1rem 0.4rem;
    background: var(--bg-tertiary, #313244);
    color: var(--text-muted);
    font-size: 0.7rem;
    border-radius: 3px;
    font-family: monospace;
}

/* Status Toggle */
.status-toggle {
    background: none;
    border: 1px solid transparent;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.2s;
}
.status-toggle.active { color: #16a34a; }
.status-toggle.active:hover { background: #dcfce7; border-color: #16a34a; }
.status-toggle.inactive { color: #9ca3af; }
.status-toggle.inactive:hover { background: #f3f4f6; border-color: #9ca3af; }

/* Dropdown Actions */
.actions-dropdown { position: relative; display: inline-block; }
.dropdown-toggle { min-width: 90px; }
.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    min-width: 160px;
    z-index: 100;
    padding: 0.25rem 0;
}
.dropdown-menu.show { display: block; }
.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.5rem 1rem;
    text-align: left;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.9rem;
}
.dropdown-item:hover { background: var(--bg-secondary); }
.dropdown-item.text-danger { color: #dc2626; }
.dropdown-divider { margin: 0.25rem 0; border: 0; border-top: 1px solid var(--border-color); }

/* Buttons */
.btn-ghost {
    background: transparent;
    color: var(--text-muted);
    border: none;
}
.btn-ghost:hover {
    color: var(--text-primary);
    background: var(--bg-secondary);
}

/* Modals */
.modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}
.modal-content {
    background: var(--bg-primary);
    border-radius: 8px;
    width: 90%;
    max-height: 80vh;
    overflow: auto;
}
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
}
.modal-header h3 { margin: 0; }
.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    line-height: 1;
}
.modal-body { padding: 1.5rem; }
.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

/* Preview Styles */
#previewContent .html-preview { word-break: break-word; }
#previewContent .json-preview,
#previewContent .shortcode-preview {
    background: var(--bg-secondary);
    padding: 1rem;
    border-radius: 6px;
    overflow-x: auto;
}
#previewContent .text-preview,
#previewContent .markdown-preview {
    line-height: 1.6;
}
</style>

<script>
function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== btn.nextElementSibling) menu.classList.remove('show');
    });
    btn.nextElementSibling.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
    }
});

function previewBlock(blockId) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const meta = document.getElementById('previewMeta');
    const title = document.getElementById('previewTitle');

    content.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Loading...</p>';
    meta.innerHTML = '';
    modal.style.display = 'flex';

    fetch('/admin/content/' + blockId + '/preview')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                title.textContent = 'Preview: ' + data.block.name;
                meta.innerHTML = '<span class="badge badge-type">' + data.block.type + '</span> ' +
                                 '<span class="badge badge-category">' + data.block.category + '</span>';
                content.innerHTML = data.html || '<p style="color: var(--text-muted); text-align: center;">Empty content</p>';
            } else {
                content.innerHTML = '<p style="color: #dc2626;">Error loading preview</p>';
            }
        })
        .catch(err => {
            content.innerHTML = '<p style="color: #dc2626;">Error loading preview</p>';
        });
}

// Bulk selection
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateBulkSelection();
}

function updateBulkSelection() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');
    const container = document.getElementById('bulkIdsContainer');

    countSpan.textContent = checkboxes.length;
    bulkBar.style.display = checkboxes.length > 0 ? 'block' : 'none';

    // Update hidden inputs
    container.innerHTML = '';
    checkboxes.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    // Update select all checkbox state
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
        selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    }
}

function confirmBulkDelete() {
    const count = document.querySelectorAll('.row-checkbox:checked').length;
    return confirm('Delete ' + count + ' content block(s)? This cannot be undone.');
}

// Close modals on backdrop click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
