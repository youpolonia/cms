<?php
/**
 * Menus List - Catppuccin Dark UI
 */
$title = 'Navigation Menus';
ob_start();

$locationIcons = [
    'header' => '🏠',
    'footer' => '📋',
    'sidebar' => '📁'
];
?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}
.page-header h1 { margin: 0 0 0.25rem; font-size: 1.75rem; }
.page-subtitle { margin: 0; color: var(--text-muted); font-size: 0.9rem; }
.page-actions { display: flex; gap: 0.5rem; }

/* Cards */
.card { background: var(--bg-primary); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }
.card-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-bottom: 1px solid var(--border);
    background: var(--bg-secondary);
}
.card-header-left { display: flex; align-items: center; gap: 0.75rem; }
.card-title { margin: 0; font-size: 1rem; font-weight: 600; }
.card-body { padding: 1.25rem; }

/* Table */
.menus-table { width: 100%; border-collapse: collapse; }
.menus-table th {
    padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem;
    font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;
    color: var(--text-muted); border-bottom: 1px solid var(--border);
    background: var(--bg-secondary);
}
.menus-table td {
    padding: 0.875rem 1rem; border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.menus-table tbody tr { transition: background 0.15s; }
.menus-table tbody tr:hover { background: var(--bg-tertiary); }
.menus-table tbody tr:last-child td { border-bottom: none; }

.menu-name { font-weight: 600; color: var(--text-primary); }
.menu-name a { color: var(--text-primary); text-decoration: none; }
.menu-name a:hover { color: var(--accent); }
.menu-desc { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem; }
.menu-slug {
    font-family: 'Monaco', 'Menlo', monospace; font-size: 0.8rem;
    background: var(--bg-tertiary); padding: 0.2rem 0.5rem;
    border-radius: 4px; color: var(--text-secondary);
}

/* Badges */
.badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600;
}
.badge-location { background: var(--accent-muted); color: var(--accent); }
.badge-count { background: var(--bg-tertiary); color: var(--text-secondary); min-width: 28px; justify-content: center; }
.count-badge { background: var(--accent); color: #1e1e2e; padding: 0.15rem 0.5rem; border-radius: 10px; font-size: 0.75rem; font-weight: 600; }

/* Status toggle */
.status-toggle {
    background: none; border: 1px solid transparent; padding: 0.25rem 0.6rem;
    border-radius: 6px; cursor: pointer; font-size: 0.8rem;
    font-weight: 500; transition: all 0.15s;
}
.status-toggle.active { color: var(--success); }
.status-toggle.active:hover { background: var(--success-bg); border-color: var(--success); }
.status-toggle.inactive { color: var(--text-muted); }
.status-toggle.inactive:hover { background: var(--bg-tertiary); border-color: var(--text-muted); }

/* Actions dropdown */
.actions-dropdown { position: relative; display: inline-block; }
.dropdown-toggle {
    padding: 0.4rem 0.75rem; font-size: 0.8rem; background: var(--bg-tertiary);
    color: var(--text-primary); border: 1px solid var(--border); border-radius: var(--radius);
    cursor: pointer; transition: all 0.15s;
}
.dropdown-toggle:hover { border-color: var(--accent); }
.dropdown-menu {
    display: none; position: absolute; right: 0; top: calc(100% + 4px);
    background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius); box-shadow: 0 8px 24px rgba(0,0,0,0.3);
    min-width: 170px; z-index: 100; padding: 0.25rem 0;
}
.dropdown-menu.show { display: block; }
.dropdown-item {
    display: block; width: 100%; padding: 0.5rem 1rem; text-align: left;
    background: none; border: none; cursor: pointer;
    color: var(--text-primary); text-decoration: none; font-size: 0.875rem;
    transition: background 0.1s;
}
.dropdown-item:hover { background: var(--bg-tertiary); color: var(--text-primary); }
.dropdown-item.text-danger { color: var(--danger); }
.dropdown-item.text-danger:hover { background: var(--danger-bg); }
.dropdown-divider { margin: 0.25rem 0; border: 0; border-top: 1px solid var(--border); }

/* Empty state */
.empty-state { text-align: center; padding: 3rem 1.5rem; }
.empty-icon { font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5; }
.empty-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary); }
.empty-text { color: var(--text-muted); margin-bottom: 1.5rem; }

/* Buttons */
.btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;
    border-radius: var(--radius); border: none; cursor: pointer;
    text-decoration: none; transition: all 0.15s;
}
.btn-primary { background: var(--accent); color: #1e1e2e; }
.btn-primary:hover { background: var(--accent-hover); color: #1e1e2e; }
.btn-secondary { background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border); }
.btn-secondary:hover { background: var(--bg-primary); border-color: var(--accent); }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8rem; }

/* Alerts */
.alert {
    padding: 0.875rem 1rem; border-radius: var(--radius);
    margin-bottom: 1rem; font-size: 0.875rem;
}
.alert-success { background: var(--success-bg); color: var(--success); border: 1px solid rgba(166,227,161,0.3); }
.alert-error { background: var(--danger-bg); color: var(--danger); border: 1px solid rgba(243,139,168,0.3); }

/* Modal */
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
.modal-close {
    background: none; border: none; font-size: 1.5rem;
    cursor: pointer; color: var(--text-muted); line-height: 1;
}
.modal-close:hover { color: var(--text-primary); }
.modal-body { padding: 1.25rem; }
.modal-footer {
    padding: 1rem 1.25rem; border-top: 1px solid var(--border);
    display: flex; justify-content: flex-end; gap: 0.5rem;
}

/* Preview */
#previewContent ul { list-style: none; padding-left: 0; margin: 0; }
#previewContent ul ul { padding-left: 1.5rem; }
#previewContent li { padding: 0.5rem 0; border-bottom: 1px solid var(--border); }
#previewContent li:last-child { border-bottom: none; }
#previewContent a { color: var(--text-primary); text-decoration: none; }
#previewContent a:hover { color: var(--accent); }

/* Stats bar */
.stats-bar {
    display: flex; gap: 1.5rem; margin-bottom: 1.5rem;
}
.stat-card {
    flex: 1; background: var(--bg-primary); border: 1px solid var(--border);
    border-radius: var(--radius-lg); padding: 1rem 1.25rem;
    display: flex; align-items: center; gap: 1rem;
}
.stat-icon { font-size: 1.5rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.stat-label { font-size: 0.8rem; color: var(--text-muted); }

@media (max-width: 768px) {
    .stats-bar { flex-direction: column; gap: 0.75rem; }
    .page-header { flex-direction: column; gap: 1rem; }
    .menus-table th:nth-child(3), .menus-table td:nth-child(3) { display: none; }
}
</style>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>


<div class="inline-help" id="help-menus">
    <span class="inline-help-icon">💡</span>
    <div><strong>Menus</strong> control your site navigation. Create menus and assign them to locations (header, footer). Add pages, custom links, or categories as menu items. Drag items to reorder. <a href="/admin/docs?section=menus">Read more →</a></div>
    <button class="inline-help-close" onclick="this.closest('.inline-help').style.display='none';localStorage.setItem('help-menus-hidden','1')" title="Dismiss">×</button>
</div>
<script>if(localStorage.getItem('help-menus-hidden'))document.getElementById('help-menus').style.display='none'</script>
<div class="page-header">
    <div>
        <h1>📋 Navigation Menus</h1>
        <p class="page-subtitle">Manage site navigation menus and their items</p>
    </div>
    <div class="page-actions">
        <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='flex'">
            📥 Import
        </button>
        <a href="/admin/menus/create" class="btn btn-primary btn-sm">+ New Menu</a>
    </div>
</div>

<?php
$totalMenus = count($menus);
$activeMenus = count(array_filter($menus, fn($m) => ($m['is_active'] ?? 1) == 1));
$totalItems = array_sum(array_column($menus, 'item_count'));
?>
<div class="stats-bar">
    <div class="stat-card">
        <span class="stat-icon">📋</span>
        <div>
            <div class="stat-value"><?= $totalMenus ?></div>
            <div class="stat-label">Total Menus</div>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">✅</span>
        <div>
            <div class="stat-value"><?= $activeMenus ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card">
        <span class="stat-icon">🔗</span>
        <div>
            <div class="stat-value"><?= $totalItems ?></div>
            <div class="stat-label">Menu Items</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-header-left">
            <h2 class="card-title">All Menus</h2>
            <span class="count-badge"><?= $totalMenus ?></span>
        </div>
    </div>

    <?php if (empty($menus)): ?>
        <div class="empty-state">
            <span class="empty-icon">📋</span>
            <div class="empty-title">No menus yet</div>
            <p class="empty-text">Create your first navigation menu to organize your site structure.</p>
            <a href="/admin/menus/create" class="btn btn-primary">✨ Create First Menu</a>
        </div>
    <?php else: ?>
        <table class="menus-table">
            <thead>
                <tr>
                    <th>Menu <span class="tip"><span class="tip-text">Name of the navigation menu.</span></span></th>
                    <th>Slug <span class="tip"><span class="tip-text">Unique identifier used in code. Auto-generated.</span></span></th>
                    <th>Location <span class="tip"><span class="tip-text">Where this menu is displayed (header, footer, etc.).</span></span></th>
                    <th style="text-align: center;">Items</th>
                    <th style="text-align: center;">Status</th>
                    <th style="width: 110px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                    <?php $isActive = ($menu['is_active'] ?? 1) == 1; ?>
                    <tr>
                        <td>
                            <div class="menu-name">
                                <a href="/admin/menus/<?= (int)$menu['id'] ?>/items"><?= esc($menu['name']) ?></a>
                            </div>
                            <?php if (!empty($menu['description'])): ?>
                                <div class="menu-desc"><?= esc(mb_substr($menu['description'], 0, 60)) ?><?= mb_strlen($menu['description']) > 60 ? '…' : '' ?></div>
                            <?php endif; ?>
                        </td>
                        <td><span class="menu-slug"><?= esc($menu['slug']) ?></span></td>
                        <td>
                            <?php if ($menu['location']): ?>
                                <span class="badge badge-location"><?= $locationIcons[$menu['location']] ?? '📍' ?> <?= ucfirst(esc($menu['location'])) ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-count"><?= (int)$menu['item_count'] ?></span>
                        </td>
                        <td style="text-align: center;">
                            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/toggle" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="status-toggle <?= $isActive ? 'active' : 'inactive' ?>"
                                        title="<?= $isActive ? 'Click to deactivate' : 'Click to activate' ?>">
                                    <?= $isActive ? '● Active' : '○ Inactive' ?>
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="actions-dropdown">
                                <button type="button" class="dropdown-toggle" onclick="toggleDropdown(this)">
                                    ⋯
                                </button>
                                <div class="dropdown-menu">
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="dropdown-item">📝 Manage Items</a>
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/edit" class="dropdown-item">✏️ Edit Menu</a>
                                    <button type="button" class="dropdown-item" onclick="previewMenu(<?= (int)$menu['id'] ?>)">👁️ Preview</button>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/duplicate" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item">📋 Duplicate</button>
                                    </form>
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/export" class="dropdown-item">📤 Export JSON</a>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/delete"
                                          onsubmit="return confirm('Delete this menu and all its items?');" style="margin: 0;">
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
    <?php endif; ?>
</div>

<!-- Import Modal -->
<div id="importModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 420px;">
        <div class="modal-header">
            <h3>📥 Import Menu</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</button>
        </div>
        <form method="post" action="/admin/menus/import" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="import_file" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Select JSON File</label>
                    <input type="file" id="import_file" name="import_file" accept=".json" required
                           style="width: 100%; padding: 0.5rem; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: var(--radius); color: var(--text-primary);">
                    <small style="color: var(--text-muted); display: block; margin-top: 0.5rem;">Upload a previously exported menu JSON file</small>
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
            <h3 id="previewTitle">Menu Preview</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('previewModal').style.display='none'">&times;</button>
        </div>
        <div class="modal-body">
            <div id="previewContent" style="min-height: 100px;">
                <p style="color: var(--text-muted); text-align: center;">Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function toggleDropdown(btn) {
    document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
        if (menu !== btn.nextElementSibling) menu.classList.remove('show');
    });
    btn.nextElementSibling.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) { menu.classList.remove('show'); });
    }
});

function previewMenu(menuId) {
    var modal = document.getElementById('previewModal');
    var content = document.getElementById('previewContent');
    var title = document.getElementById('previewTitle');

    content.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Loading...</p>';
    modal.style.display = 'flex';

    fetch('/admin/menus/' + menuId + '/preview')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                title.textContent = 'Preview: ' + data.menu.name;
                content.innerHTML = data.html || '<p style="color: var(--text-muted); text-align: center;">No active items.</p>';
            } else {
                content.innerHTML = '<p style="color: var(--danger);">Error loading preview</p>';
            }
        })
        .catch(function() {
            content.innerHTML = '<p style="color: var(--danger);">Error loading preview</p>';
        });
}

document.querySelectorAll('.modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
