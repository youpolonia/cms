<?php
/**
 * Widgets List - Full Upgrade
 */
$title = 'Widgets';
ob_start();

$areaIcons = [
    'sidebar' => '📁',
    'footer_1' => '📋',
    'footer_2' => '📋',
    'footer_3' => '📋',
    'header' => '🏠',
    'after_content' => '📄'
];

$typeIcons = [
    'html' => '🌐',
    'text' => '📝',
    'menu' => '📋',
    'recent_posts' => '📰',
    'categories' => '🏷️',
    'search' => '🔍',
    'social' => '🔗',
    'custom' => '⚙️'
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
        <h2 class="card-title">Widgets</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="/admin/widgets/export<?= $currentArea ? '?area=' . esc($currentArea) : '' ?>" class="btn btn-secondary btn-sm">📤 Export</a>
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='flex'">📥 Import</button>
            <a href="/admin/widgets/create" class="btn btn-primary btn-sm">+ New Widget</a>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="filters-bar">
        <form method="get" action="/admin/widgets" class="filters-form">
            <div class="filter-group">
                <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Search widgets..." class="filter-input">
            </div>
            <div class="filter-group">
                <select name="area" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Areas</option>
                    <?php foreach ($areas as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($currentArea ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach ($types as $key => $label): ?>
                        <option value="<?= esc($key) ?>" <?= ($currentType ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if ($search || $currentArea || $currentType): ?>
                <a href="/admin/widgets" class="btn btn-sm" style="background: var(--bg-secondary);">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($widgets)): ?>
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📦</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--text-primary);">No widgets found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                <?php if ($search || $currentArea || $currentType): ?>
                    Try adjusting your filters or <a href="/admin/widgets">view all widgets</a>.
                <?php else: ?>
                    Create your first widget to add dynamic content to your site.
                <?php endif; ?>
            </p>
            <a href="/admin/widgets/create" class="btn btn-primary">Create First Widget</a>
        </div>
    <?php else: ?>
        <!-- Bulk Actions Form -->
        <form id="bulkForm" method="post" action="/admin/widgets/bulk-delete">
            <?= csrf_field() ?>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="bulk-count"><span id="selectedCount">0</span> selected</span>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected widgets?');">Delete Selected</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">Clear Selection</button>
            </div>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                        </th>
                        <th>Widget</th>
                        <th>Type</th>
                        <th>Area</th>
                        <?php if ($hasNewColumns ?? false): ?>
                            <th>Visibility</th>
                            <th style="text-align: center;">Version</th>
                        <?php endif; ?>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="widgetsList">
                    <?php foreach ($widgets as $widget): ?>
                        <?php
                        $isActive = ($widget['is_active'] ?? 1) == 1;
                        $areaIcon = $areaIcons[$widget['area']] ?? '📍';
                        $typeIcon = $typeIcons[$widget['type']] ?? '📦';
                        $icon = $widget['icon'] ?? '';
                        ?>
                        <tr data-id="<?= (int)$widget['id'] ?>" data-area="<?= esc($widget['area']) ?>">
                            <td>
                                <input type="checkbox" name="ids[]" value="<?= (int)$widget['id'] ?>" class="row-checkbox" onchange="updateBulkActions()">
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <?php if ($icon): ?>
                                        <span style="font-size: 1.5rem;"><?= esc($icon) ?></span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= esc($widget['name']) ?></strong>
                                        <br><code style="font-size: 0.8rem; color: var(--text-muted);"><?= esc($widget['slug']) ?></code>
                                        <?php if (!empty($widget['description'])): ?>
                                            <br><small style="color: var(--text-muted);"><?= esc(mb_substr($widget['description'], 0, 60)) ?><?= mb_strlen($widget['description'] ?? '') > 60 ? '...' : '' ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-type"><?= $typeIcon ?> <?= esc($types[$widget['type']] ?? $widget['type']) ?></span>
                            </td>
                            <td>
                                <span class="badge badge-area"><?= $areaIcon ?> <?= esc($areas[$widget['area']] ?? $widget['area']) ?></span>
                            </td>
                            <?php if ($hasNewColumns ?? false): ?>
                                <td>
                                    <?php
                                    $visibility = $widget['visibility'] ?? 'all';
                                    $visibilityLabels = [
                                        'all' => '👥 Everyone',
                                        'logged_in' => '🔐 Logged In',
                                        'logged_out' => '👤 Guests',
                                        'admin' => '🛡️ Admin'
                                    ];
                                    ?>
                                    <span class="badge badge-visibility"><?= $visibilityLabels[$visibility] ?? $visibility ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge badge-version">v<?= (int)($widget['version'] ?? 1) ?></span>
                                </td>
                            <?php endif; ?>
                            <td style="text-align: center;">
                                <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/toggle" style="display: inline;">
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
                                        <a href="/admin/widgets/<?= (int)$widget['id'] ?>/edit" class="dropdown-item">
                                            ✏️ Edit
                                        </a>
                                        <button type="button" class="dropdown-item" onclick="previewWidget(<?= (int)$widget['id'] ?>)">
                                            👁️ Preview
                                        </button>
                                        <hr class="dropdown-divider">
                                        <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/duplicate" style="margin: 0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item">
                                                📋 Duplicate
                                            </button>
                                        </form>
                                        <hr class="dropdown-divider">
                                        <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/delete" onsubmit="return confirm('Delete this widget?');" style="margin: 0;">
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
        </form>
    <?php endif; ?>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3>Import Widgets</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</button>
        </div>
        <form method="post" action="/admin/widgets/import" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="import_file">Select JSON File</label>
                    <input type="file" id="import_file" name="import_file" accept=".json" required style="width: 100%;">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">
                        Upload a previously exported widgets JSON file. Duplicate slugs will be renamed automatically.
                    </small>
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
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="previewTitle">Widget Preview</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('previewModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div id="previewMeta" style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                <span id="previewType" class="badge badge-type"></span>
                <span id="previewArea" class="badge badge-area" style="margin-left: 0.5rem;"></span>
            </div>
            <div id="previewContent" style="min-height: 100px;">
                <p style="color: var(--text-muted); text-align: center;">Loading...</p>
            </div>
        </div>
    </div>
</div>

<style>
.filters-bar {
    padding: 1rem 1.5rem;
    background: var(--bg-secondary);
    border-bottom: 1px solid var(--border-color);
}
.filters-form {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
    align-items: center;
}
.filter-group { flex-shrink: 0; }
.filter-input, .filter-select {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.9rem;
}
.filter-input { width: 200px; }
.filter-select { min-width: 140px; }

.bulk-actions {
    padding: 0.75rem 1.5rem;
    background: #fef3c7;
    border-bottom: 1px solid #f59e0b;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.bulk-count {
    font-weight: 500;
    color: #92400e;
}

.badge { padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; white-space: nowrap; }
.badge-type { background: #e0f2fe; color: #0369a1; }
.badge-area { background: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-color); }
.badge-visibility { background: #f3e8ff; color: #7c3aed; }
.badge-version { background: #ecfdf5; color: #059669; }

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
    min-width: 150px;
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

#widgetsList tr { cursor: grab; }
#widgetsList tr.dragging { opacity: 0.5; }
</style>

<script>
// Dropdown toggle
function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
        if (menu !== btn.nextElementSibling) menu.classList.remove('show');
    });
    btn.nextElementSibling.classList.toggle('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => menu.classList.remove('show'));
    }
});

// Bulk selection
function toggleSelectAll(checkbox) {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';

    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    document.getElementById('selectAll').checked = checked === allCheckboxes.length && checked > 0;
    document.getElementById('selectAll').indeterminate = checked > 0 && checked < allCheckboxes.length;
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Widget preview
function previewWidget(widgetId) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const title = document.getElementById('previewTitle');
    const typeEl = document.getElementById('previewType');
    const areaEl = document.getElementById('previewArea');

    content.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Loading...</p>';
    modal.style.display = 'flex';

    fetch('/admin/widgets/' + widgetId + '/preview')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                title.textContent = 'Preview: ' + data.widget.name;
                typeEl.textContent = data.widget.type;
                areaEl.textContent = data.widget.area;
                content.innerHTML = data.html || '<p style="color: var(--text-muted); text-align: center;">No content to preview.</p>';
            } else {
                content.innerHTML = '<p style="color: #dc2626;">Error loading preview</p>';
            }
        })
        .catch(err => {
            content.innerHTML = '<p style="color: #dc2626;">Error loading preview</p>';
        });
}

// Close modals on backdrop click
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});

// Drag and drop reordering
let draggedRow = null;
const tbody = document.getElementById('widgetsList');

if (tbody) {
    tbody.querySelectorAll('tr').forEach(row => {
        row.draggable = true;

        row.addEventListener('dragstart', function(e) {
            draggedRow = this;
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            draggedRow = null;
        });

        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (draggedRow && draggedRow !== this && draggedRow.dataset.area === this.dataset.area) {
                const rect = this.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (e.clientY < midY) {
                    this.parentNode.insertBefore(draggedRow, this);
                } else {
                    this.parentNode.insertBefore(draggedRow, this.nextSibling);
                }
            }
        });

        row.addEventListener('drop', function(e) {
            e.preventDefault();
            saveOrder();
        });
    });
}

function saveOrder() {
    const rows = document.querySelectorAll('#widgetsList tr');
    const order = [];
    rows.forEach((row, index) => {
        order.push(parseInt(row.dataset.id));
    });

    fetch('/admin/widgets/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('input[name="csrf_token"]')?.value || ''
        },
        body: JSON.stringify({ order: order })
    }).then(r => r.json()).then(data => {
        if (!data.success) {
            console.error('Failed to save order');
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
