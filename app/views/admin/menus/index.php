<?php
/**
 * Menus List - Full Upgrade
 */
$title = 'Navigation Menus';
ob_start();

$locationIcons = [
    'header' => '🏠',
    'footer' => '📋',
    'sidebar' => '📁'
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
        <h2 class="card-title">Navigation Menus</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('importModal').style.display='flex'">
                📥 Import
            </button>
            <a href="/admin/menus/create" class="btn btn-primary btn-sm">+ New Menu</a>
        </div>
    </div>

    <?php if (empty($menus)): ?>
        <div class="card-body" style="text-align: center; padding: 3rem;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
            <h3 style="margin-bottom: 0.5rem; color: var(--text-primary);">No menus yet</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">
                Create your first navigation menu to organize your site structure.
            </p>
            <a href="/admin/menus/create" class="btn btn-primary">Create First Menu</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Location</th>
                    <th style="text-align: center;">Items</th>
                    <th style="text-align: center;">Status</th>
                    <th style="width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $menu): ?>
                    <?php 
                    $isActive = ($menu['is_active'] ?? 1) == 1;
                    $locationIcon = $locationIcons[$menu['location'] ?? ''] ?? '📍';
                    ?>
                    <tr>
                        <td>
                            <strong><?= esc($menu['name']) ?></strong>
                            <?php if (!empty($menu['description'])): ?>
                                <br><small style="color: var(--text-muted);"><?= esc(mb_substr($menu['description'], 0, 50)) ?><?= mb_strlen($menu['description']) > 50 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td><code style="font-size: 0.85rem;"><?= esc($menu['slug']) ?></code></td>
                        <td>
                            <?php if ($menu['location']): ?>
                                <span class="badge badge-location"><?= $locationIcon ?> <?= ucfirst(esc($menu['location'])) ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-info"><?= (int)$menu['item_count'] ?></span>
                        </td>
                        <td style="text-align: center;">
                            <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/toggle" style="display: inline;">
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
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/items" class="dropdown-item">
                                        📝 Manage Items
                                    </a>
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/edit" class="dropdown-item">
                                        ✏️ Edit Menu
                                    </a>
                                    <button type="button" class="dropdown-item" onclick="previewMenu(<?= (int)$menu['id'] ?>)">
                                        👁️ Preview
                                    </button>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/duplicate" style="margin: 0;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item">
                                            📋 Duplicate
                                        </button>
                                    </form>
                                    <a href="/admin/menus/<?= (int)$menu['id'] ?>/export" class="dropdown-item">
                                        📤 Export JSON
                                    </a>
                                    <hr class="dropdown-divider">
                                    <form method="post" action="/admin/menus/<?= (int)$menu['id'] ?>/delete" onsubmit="return confirm('Delete this menu and all its items?');" style="margin: 0;">
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
            <h3>Import Menu</h3>
            <button type="button" class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</button>
        </div>
        <form method="post" action="/admin/menus/import" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="import_file">Select JSON File</label>
                    <input type="file" id="import_file" name="import_file" accept=".json" required>
                    <small style="color: var(--text-muted);">Upload a previously exported menu JSON file</small>
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

<style>
.badge-info { background: #e0f2fe; color: #0369a1; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }
.badge-location { background: var(--bg-secondary); color: var(--text-primary); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; }

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

#previewContent ul { list-style: none; padding-left: 0; margin: 0; }
#previewContent ul ul { padding-left: 1.5rem; }
#previewContent li { padding: 0.5rem 0; }
#previewContent a { color: var(--text-primary); text-decoration: none; }
#previewContent a:hover { color: var(--accent-color); }
</style>

<script>
function toggleDropdown(btn) {
    // Close all other dropdowns
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

function previewMenu(menuId) {
    const modal = document.getElementById('previewModal');
    const content = document.getElementById('previewContent');
    const title = document.getElementById('previewTitle');
    
    content.innerHTML = '<p style="color: var(--text-muted); text-align: center;">Loading...</p>';
    modal.style.display = 'flex';
    
    fetch('/admin/menus/' + menuId + '/preview')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                title.textContent = 'Preview: ' + data.menu.name;
                content.innerHTML = data.html || '<p style="color: var(--text-muted); text-align: center;">No active items in this menu.</p>';
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
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
