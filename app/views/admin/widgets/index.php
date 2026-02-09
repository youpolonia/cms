<?php
/**
 * Widgets List - Catppuccin Dark UI
 */
$title = 'Widgets';
ob_start();

$areaIcons = [
    'sidebar' => '📁', 'footer_1' => '📋', 'footer_2' => '📋',
    'footer_3' => '📋', 'header' => '🏠', 'after_content' => '📄'
];
$typeIcons = [
    'html' => '🌐', 'text' => '📝', 'menu' => '📋', 'recent_posts' => '📰',
    'categories' => '🏷️', 'search' => '🔍', 'social' => '🔗', 'custom' => '⚙️'
];
?>

<style>
.page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; }
.page-header h1 { margin: 0 0 0.25rem; font-size: 1.75rem; }
.page-subtitle { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
.page-actions { display: flex; gap: 0.5rem; }

.stats-bar { display: flex; gap: 1.5rem; margin-bottom: 1.5rem; }
.stat-card {
    flex: 1; background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 1rem;
}
.stat-icon { font-size: 1.5rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.stat-label { font-size: 0.8rem; color: var(--text-muted); }

.card { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--bg-secondary);
}
.card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.card-title { margin: 0; font-size: 1rem; font-weight: 600; }
.card-body { padding: 1.25rem; }
.count-badge { background: var(--accent); color: #1e1e2e; padding: 0.15rem 0.5rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }

.filters-bar { padding: 0.75rem 1.25rem; background: var(--bg-secondary); border-bottom: 1px solid var(--border); }
.filters-form { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
.filter-input, .filter-select {
    padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: var(--radius);
    background: var(--bg-primary); color: var(--text-primary); font-size: 0.875rem;
}
.filter-input { width: 200px; }
.filter-input:focus, .filter-select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-muted); }
.filter-select { min-width: 140px; cursor: pointer; }

.widgets-table { width: 100%; border-collapse: collapse; }
.widgets-table th {
    padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-muted); border-bottom: 1px solid var(--border); background: var(--bg-secondary);
}
.widgets-table td { padding: 0.875rem 1rem; border-bottom: 1px solid var(--border); vertical-align: middle; }
.widgets-table tbody tr { transition: background 0.15s; cursor: grab; }
.widgets-table tbody tr:hover { background: var(--bg-tertiary); }
.widgets-table tbody tr:last-child td { border-bottom: none; }
.widgets-table tbody tr.dragging { opacity: 0.5; }

.widget-name { font-weight: 600; color: var(--text-primary); }
.widget-name a { color: var(--text-primary); text-decoration: none; }
.widget-name a:hover { color: var(--accent); }
.widget-slug { font-family: 'Monaco','Menlo',monospace; font-size: 0.8rem; color: var(--text-muted); }
.widget-desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem; }
.widget-icon { font-size: 1.3rem; }

.badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;
}
.badge-type { background: var(--accent-muted); color: var(--accent); }
.badge-area { background: var(--bg-tertiary); color: var(--text-secondary); }
.badge-visibility { background: rgba(203,166,247,0.15); color: #cba6f7; }
.badge-version { background: var(--success-bg); color: var(--success); }

.status-toggle {
    background: none; border: 1px solid transparent; padding: 0.25rem 0.6rem;
    border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: 500; transition: all 0.15s;
}
.status-toggle.active { color: var(--success); }
.status-toggle.active:hover { background: var(--success-bg); border-color: var(--success); }
.status-toggle.inactive { color: var(--text-muted); }
.status-toggle.inactive:hover { background: var(--bg-tertiary); border-color: var(--text-muted); }

.bulk-actions {
    padding: 0.75rem 1.25rem; background: var(--warning-bg); border-bottom: 1px solid rgba(249,226,175,0.3);
    display: flex; align-items: center; gap: 1rem;
}
.bulk-count { font-weight: 500; color: var(--warning); }

.actions-dropdown { position: relative; display: inline-block; }
.dropdown-toggle {
    padding: 0.4rem 0.6rem; font-size: 1rem; background: var(--bg-tertiary);
    color: var(--text-primary); border: 1px solid var(--border); border-radius: var(--radius);
    cursor: pointer; transition: all 0.15s; line-height: 1;
}
.dropdown-toggle:hover { border-color: var(--accent); }
.dropdown-menu {
    display: none; position: absolute; right: 0; top: calc(100% + 4px);
    background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius); box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    min-width: 160px; z-index: 100; padding: 0.25rem 0;
}
.dropdown-menu.show { display: block; }
.dropdown-item {
    display: block; width: 100%; padding: 0.5rem 1rem; text-align: left;
    background: none; border: none; cursor: pointer;
    color: var(--text-primary); text-decoration: none; font-size: 0.875rem; transition: background 0.1s;
}
.dropdown-item:hover { background: var(--bg-tertiary); color: var(--text-primary); }
.dropdown-item.text-danger { color: var(--danger); }
.dropdown-item.text-danger:hover { background: var(--danger-bg); }
.dropdown-divider { margin: 0.25rem 0; border: 0; border-top: 1px solid var(--border); }

.empty-state { text-align: center; padding: 3rem 1.5rem; }
.empty-icon { font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5; }
.empty-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary); }
.empty-text { color: var(--text-muted); margin-bottom: 1.5rem; }

.btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;
    border-radius: var(--radius); border: none; cursor: pointer; text-decoration: none; transition: all 0.15s;
}
.btn-primary { background: var(--accent); color: #1e1e2e; }
.btn-primary:hover { background: var(--accent-hover); color: #1e1e2e; }
.btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
.btn-secondary:hover { border-color: var(--accent); }
.btn-danger { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(243,139,168,0.3); }
.btn-danger:hover { background: var(--danger); color: #1e1e2e; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }

.alert { padding: 0.875rem 1rem; border-radius: var(--radius); margin-bottom: 1rem; font-size: 0.875rem; }
.alert-success { background: var(--success-bg); color: var(--success); border: 1px solid rgba(166,227,161,0.3); }
.alert-error { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(243,139,168,0.3); }

.modal {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal-content {
    background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius-lg); width: 90%; max-height: 80vh; overflow: auto;
}
.modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
}
.modal-header h3 { margin: 0; }
.modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-muted); line-height: 1; }
.modal-close:hover { color: var(--text-primary); }
.modal-body { padding: 1.25rem; }
.modal-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.5rem; }

@media (max-width: 768px) {
    .stats-bar { flex-direction: column; gap: 0.75rem; }
    .page-header { flex-direction: column; gap: 1rem; }
    .widgets-table th:nth-child(4), .widgets-table td:nth-child(4) { display: none; }
}
</style>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="page-header">
    <div>
        <h1>🧩 Widgets</h1>
        <p class="page-subtitle">Manage sidebar, header and footer widgets</p>
    </div>
    <div class="page-actions">
        <a href="/admin/widgets/export<?= ($currentArea ?? '') ? '?area=' . esc($currentArea) : '' ?>" class="btn btn-secondary btn-sm">📤 Export</a>
        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='flex'">📥 Import</button>
        <a href="/admin/widgets/create" class="btn btn-primary btn-sm">+ New Widget</a>
    </div>
</div>

<?php
$totalWidgets = count($widgets);
$activeWidgets = count(array_filter($widgets, fn($w) => ($w['is_active'] ?? 1) == 1));
$areaCount = count(array_unique(array_column($widgets, 'area')));
?>
<div class="stats-bar">
    <div class="stat-card">
        <span class="stat-icon">🧩</span>
        <div><div class="stat-value"><?= $totalWidgets ?></div><div class="stat-label">Total Widgets</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">✅</span>
        <div><div class="stat-value"><?= $activeWidgets ?></div><div class="stat-label">Active</div></div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">📍</span>
        <div><div class="stat-value"><?= $areaCount ?></div><div class="stat-label">Areas Used</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <h2 class="card-title">All Widgets</h2>
            <span class="count-badge"><?= $totalWidgets ?></span>
        </div>
    </div>

    <div class="filters-bar">
        <form method="get" action="/admin/widgets" class="filters-form">
            <input type="text" name="search" value="<?= esc($search ?? '') ?>" placeholder="Search widgets..." class="filter-input">
            <select name="area" class="filter-select" onchange="this.form.submit()">
                <option value="">All Areas</option>
                <?php foreach ($areas as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($currentArea ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="type" class="filter-select" onchange="this.form.submit()">
                <option value="">All Types</option>
                <?php foreach ($types as $key => $label): ?>
                    <option value="<?= esc($key) ?>" <?= ($currentType ?? '') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
            <?php if (($search ?? '') || ($currentArea ?? '') || ($currentType ?? '')): ?>
                <a href="/admin/widgets" class="btn btn-sm btn-secondary">✕ Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($widgets)): ?>
        <div class="empty-state">
            <span class="empty-icon">🧩</span>
            <div class="empty-title">No widgets found</div>
            <p class="empty-text">
                <?php if (($search ?? '') || ($currentArea ?? '') || ($currentType ?? '')): ?>
                    Try adjusting your filters or <a href="/admin/widgets">view all widgets</a>.
                <?php else: ?>
                    Create your first widget to add dynamic content to your site.
                <?php endif; ?>
            </p>
            <a href="/admin/widgets/create" class="btn btn-primary">✨ Create First Widget</a>
        </div>
    <?php else: ?>
        <form id="bulkForm" method="post" action="/admin/widgets/bulk-delete">
            <?= csrf_field() ?>
            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="bulk-count"><span id="selectedCount">0</span> selected</span>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected widgets?');">🗑️ Delete Selected</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="clearSelection()">Clear</button>
            </div>

            <table class="widgets-table">
                <thead>
                    <tr>
                        <th style="width: 36px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)"></th>
                        <th>Widget</th>
                        <th>Type</th>
                        <th>Area</th>
                        <?php if ($hasNewColumns ?? false): ?>
                            <th>Visibility</th>
                        <?php endif; ?>
                        <th style="text-align: center;">Status</th>
                        <th style="width: 50px;"></th>
                    </tr>
                </thead>
                <tbody id="widgetsList">
                    <?php foreach ($widgets as $widget): ?>
                        <?php
                        $isActive = ($widget['is_active'] ?? 1) == 1;
                        $icon = $widget['icon'] ?? '';
                        ?>
                        <tr data-id="<?= (int)$widget['id'] ?>" data-area="<?= esc($widget['area']) ?>" draggable="true">
                            <td><input type="checkbox" name="ids[]" value="<?= (int)$widget['id'] ?>" class="row-checkbox" onchange="updateBulkActions()"></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <?php if ($icon): ?><span class="widget-icon"><?= esc($icon) ?></span><?php endif; ?>
                                    <div>
                                        <div class="widget-name"><a href="/admin/widgets/<?= (int)$widget['id'] ?>/edit"><?= esc($widget['name']) ?></a></div>
                                        <div class="widget-slug"><?= esc($widget['slug']) ?></div>
                                        <?php if (!empty($widget['description'])): ?>
                                            <div class="widget-desc"><?= esc(mb_substr($widget['description'], 0, 60)) ?><?= mb_strlen($widget['description'] ?? '') > 60 ? '…' : '' ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-type"><?= $typeIcons[$widget['type']] ?? '📦' ?> <?= esc($types[$widget['type']] ?? $widget['type']) ?></span></td>
                            <td><span class="badge badge-area"><?= $areaIcons[$widget['area']] ?? '📍' ?> <?= esc($areas[$widget['area']] ?? $widget['area']) ?></span></td>
                            <?php if ($hasNewColumns ?? false): ?>
                                <td>
                                    <?php
                                    $vis = $widget['visibility'] ?? 'all';
                                    $visLabels = ['all'=>'👥 Everyone','logged_in'=>'🔐 Logged In','logged_out'=>'👤 Guests','admin'=>'🛡️ Admin'];
                                    ?>
                                    <span class="badge badge-visibility"><?= $visLabels[$vis] ?? $vis ?></span>
                                </td>
                            <?php endif; ?>
                            <td style="text-align: center;">
                                <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/toggle" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="status-toggle <?= $isActive ? 'active' : 'inactive' ?>"
                                            title="<?= $isActive ? 'Click to deactivate' : 'Click to activate' ?>">
                                        <?= $isActive ? '● Active' : '○ Inactive' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <div class="actions-dropdown">
                                    <button type="button" class="dropdown-toggle" onclick="toggleDropdown(this)">⋯</button>
                                    <div class="dropdown-menu">
                                        <a href="/admin/widgets/<?= (int)$widget['id'] ?>/edit" class="dropdown-item">✏️ Edit</a>
                                        <button type="button" class="dropdown-item" onclick="previewWidget(<?= (int)$widget['id'] ?>)">👁️ Preview</button>
                                        <hr class="dropdown-divider">
                                        <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/duplicate" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item">📋 Duplicate</button>
                                        </form>
                                        <hr class="dropdown-divider">
                                        <form method="post" action="/admin/widgets/<?= (int)$widget['id'] ?>/delete" onsubmit="return confirm('Delete this widget?');" style="margin:0;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">🗑️ Delete</button>
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
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>📥 Import Widgets</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</button>
        </div>
        <form method="post" action="/admin/widgets/import" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; margin-bottom:0.5rem; font-weight:500;">Select JSON File</label>
                    <input type="file" name="import_file" accept=".json" required
                           style="width:100%; padding:0.5rem; background:var(--bg-secondary); border:1px solid var(--border); border-radius:var(--radius); color:var(--text-primary);">
                    <small style="color:var(--text-muted); display:block; margin-top:0.5rem;">Duplicate slugs will be renamed automatically.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">📥 Import</button>
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
            <div id="previewMeta" style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid var(--border); display:flex; gap:0.5rem;">
                <span id="previewType" class="badge badge-type"></span>
                <span id="previewArea" class="badge badge-area"></span>
            </div>
            <div id="previewContent" style="min-height: 100px;">
                <p style="color: var(--text-muted); text-align: center;">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu.show').forEach(function(m) { if (m !== btn.nextElementSibling) m.classList.remove('show'); });
    btn.nextElementSibling.classList.toggle('show');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-dropdown')) document.querySelectorAll('.dropdown-menu.show').forEach(function(m) { m.classList.remove('show'); });
});

function toggleSelectAll(cb) {
    document.querySelectorAll('.row-checkbox').forEach(function(c) { c.checked = cb.checked; });
    updateBulkActions();
}
function updateBulkActions() {
    var checked = document.querySelectorAll('.row-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked;
    document.getElementById('bulkActions').style.display = checked > 0 ? 'flex' : 'none';
    var all = document.querySelectorAll('.row-checkbox');
    var sa = document.getElementById('selectAll');
    sa.checked = checked === all.length && checked > 0;
    sa.indeterminate = checked > 0 && checked < all.length;
}
function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(function(c) { c.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function previewWidget(id) {
    var modal = document.getElementById('previewModal');
    var content = document.getElementById('previewContent');
    var title = document.getElementById('previewTitle');
    content.innerHTML = '<p style="color:var(--text-muted);text-align:center;">Loading...</p>';
    modal.style.display = 'flex';
    fetch('/admin/widgets/' + id + '/preview').then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            title.textContent = 'Preview: ' + data.widget.name;
            document.getElementById('previewType').textContent = data.widget.type;
            document.getElementById('previewArea').textContent = data.widget.area;
            content.innerHTML = data.html || '<p style="color:var(--text-muted);text-align:center;">No content.</p>';
        } else { content.innerHTML = '<p style="color:var(--danger);">Error loading preview</p>'; }
    }).catch(function() { content.innerHTML = '<p style="color:var(--danger);">Error loading preview</p>'; });
}

document.querySelectorAll('.modal').forEach(function(m) {
    m.addEventListener('click', function(e) { if (e.target === this) this.style.display = 'none'; });
});

// Drag & drop reorder
var draggedRow = null;
var tbody = document.getElementById('widgetsList');
if (tbody) {
    tbody.querySelectorAll('tr').forEach(function(row) {
        row.addEventListener('dragstart', function(e) { draggedRow = this; this.classList.add('dragging'); e.dataTransfer.effectAllowed = 'move'; });
        row.addEventListener('dragend', function() { this.classList.remove('dragging'); draggedRow = null; saveOrder(); });
        row.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (draggedRow && draggedRow !== this && draggedRow.dataset.area === this.dataset.area) {
                var rect = this.getBoundingClientRect();
                if (e.clientY < rect.top + rect.height / 2) this.parentNode.insertBefore(draggedRow, this);
                else this.parentNode.insertBefore(draggedRow, this.nextSibling);
            }
        });
    });
}
function saveOrder() {
    var rows = document.querySelectorAll('#widgetsList tr');
    var order = [];
    rows.forEach(function(r) { order.push(parseInt(r.dataset.id)); });
    var csrf = document.querySelector('input[name="csrf_token"]');
    fetch('/admin/widgets/reorder', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf ? csrf.value : '' },
        body: JSON.stringify({ order: order })
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
