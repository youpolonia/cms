<?php
/**
 * Theme Builder 3.0 - Page List with Bulk Actions
 */
$title = 'Theme Builder';
ob_start();

$totalPages = count($pages ?? []);
$publishedPages = count(array_filter($pages ?? [], fn($p) => ($p['status'] ?? '') === 'published'));
$draftPages = $totalPages - $publishedPages;
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: -4px;">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
            </svg>
            Theme Builder 3.0
        </h1>
        <p class="page-description">Visual page builder with drag-and-drop modules</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/theme-builder/templates" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/>
            </svg>
            Templates
        </a>
        <a href="/admin/theme-builder/create" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Page
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">📄</div>
        <div class="stat-value"><?= $totalPages ?></div>
        <div class="stat-label">Builder Pages</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success">✓</div>
        <div class="stat-value"><?= $publishedPages ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning">✎</div>
        <div class="stat-value"><?= $draftPages ?></div>
        <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon info">🧩</div>
        <div class="stat-value"><?= count($availablePages ?? []) ?></div>
        <div class="stat-label">Available to Edit</div>
    </div>
</div>

<!-- Bulk Actions Toolbar (hidden by default) -->
<div id="bulkToolbar" class="bulk-toolbar" style="display: none;">
    <div class="bulk-toolbar-inner">
        <span class="bulk-count"><span id="selectedCount">0</span> selected</span>
        <div class="bulk-actions">
            <button type="button" class="btn btn-sm btn-success" onclick="bulkAction('publish')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                Publish
            </button>
            <button type="button" class="btn btn-sm btn-warning" onclick="bulkAction('draft')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Set Draft
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="bulkAction('delete')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                Delete
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-ghost" onclick="clearSelection()">✕ Clear</button>
    </div>
</div>

<!-- Builder Pages -->
<div class="card mb-4">
    <div class="card-header">
        <h3 class="card-title">Builder Pages</h3>
    </div>
    <?php if (empty($pages)): ?>
    <div class="card-body">
        <div class="empty-state">
            <div class="empty-state-icon">🎨</div>
            <div class="empty-state-title">No builder pages yet</div>
            <div class="empty-state-description">Create your first page with the visual builder.</div>
            <a href="/admin/theme-builder/create" class="btn btn-primary">Create First Page</a>
        </div>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                    </th>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Revisions</th>
                    <th>Last Updated</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr data-id="<?= (int)$page['id'] ?>">
                    <td>
                        <input type="checkbox" class="row-checkbox" value="<?= (int)$page['id'] ?>" onchange="updateBulkToolbar()">
                    </td>
                    <td>
                        <a href="/admin/theme-builder/<?= (int)$page['id'] ?>/edit" class="table-title">
                            <?= esc($page['title']) ?>
                        </a>
                        <?php if (!empty($page['is_homepage'])): ?>
                        <span class="badge badge-primary" style="margin-left: 8px; font-size: 10px;">🏠 Homepage</span>
                        <?php endif; ?>
                        <?php if ($page['page_id']): ?>
                        <span class="badge badge-info" style="margin-left: 8px; font-size: 10px;">Linked</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code class="table-code">/<?= esc($page['slug']) ?></code>
                    </td>
                    <td>
                        <span class="badge badge-<?= $page['status'] === 'published' ? 'success' : 'warning' ?>">
                            <?= ucfirst($page['status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted"><?= (int)($page['revision_count'] ?? 0) ?></span>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('M j, Y g:i A', strtotime($page['updated_at'])) ?></span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <?php if (empty($page['is_homepage'])): ?>
                            <form method="POST" action="/admin/theme-builder/<?= (int)$page['id'] ?>/set-homepage" style="display: inline;" title="Set as Homepage">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm">🏠</button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="/admin/theme-builder/<?= (int)$page['id'] ?>/remove-homepage" style="display: inline;" title="Remove Homepage">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" style="color: var(--accent-primary);">🏠</button>
                            </form>
                            <?php endif; ?>
                            <a href="/admin/theme-builder/<?= (int)$page['id'] ?>/edit" class="btn btn-ghost btn-icon btn-sm" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="/preview/tb/<?= (int)$page['id'] ?>" target="_blank" class="btn btn-ghost btn-icon btn-sm" title="Preview">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </a>
                            <form method="POST" action="/admin/theme-builder/<?= (int)$page['id'] ?>/delete" style="display: inline;" 
                                  onsubmit="return confirm('Delete this page and all its revisions?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>


<style>
.stat-icon.info { background: var(--accent-muted); }
.empty-state { text-align: center; padding: 60px 20px; }
.empty-state-icon { font-size: 48px; margin-bottom: 16px; }
.empty-state-title { font-size: 18px; font-weight: 600; margin-bottom: 8px; }
.empty-state-description { color: var(--text-muted); margin-bottom: 24px; }
.table-title { font-weight: 500; color: var(--text-primary); }
.table-title:hover { color: var(--accent); }
.table-code { font-family: monospace; font-size: 12px; background: var(--bg-tertiary); padding: 2px 6px; border-radius: 4px; }
.table-actions { display: flex; gap: 4px; opacity: 0.6; transition: opacity 0.15s; }
tr:hover .table-actions { opacity: 1; }
.btn-icon { padding: 6px; }
.mb-4 { margin-bottom: 24px; }
.badge-info { background: var(--accent-muted); color: var(--accent); }

/* Bulk Actions Toolbar */
.bulk-toolbar {
    position: sticky;
    top: 60px;
    z-index: 100;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent-secondary, #6366f1) 100%);
    border-radius: 12px;
    margin-bottom: 20px;
    padding: 12px 20px;
    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.3);
    animation: slideDown 0.2s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
.bulk-toolbar-inner {
    display: flex;
    align-items: center;
    gap: 20px;
}
.bulk-count {
    color: white;
    font-weight: 600;
    font-size: 14px;
}
.bulk-actions {
    display: flex;
    gap: 8px;
    flex: 1;
}
.bulk-toolbar .btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.bulk-toolbar .btn-success { background: #22c55e; color: white; border: none; }
.bulk-toolbar .btn-warning { background: #f59e0b; color: white; border: none; }
.bulk-toolbar .btn-danger { background: #ef4444; color: white; border: none; }
.bulk-toolbar .btn-ghost { color: rgba(255,255,255,0.8); }
.bulk-toolbar .btn-ghost:hover { color: white; background: rgba(255,255,255,0.1); }

/* Checkbox styling */
.table th:first-child, .table td:first-child { text-align: center; }
input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--accent);
}
tr.selected { background: rgba(139, 92, 246, 0.1); }
</style>

<script>
const csrfToken = '<?= csrf_token() ?>';

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
        cb.closest('tr').classList.toggle('selected', checkbox.checked);
    });
    updateBulkToolbar();
}

function updateBulkToolbar() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const toolbar = document.getElementById('bulkToolbar');
    const countSpan = document.getElementById('selectedCount');
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    
    countSpan.textContent = checkboxes.length;
    toolbar.style.display = checkboxes.length > 0 ? 'block' : 'none';
    
    // Update select all checkbox state
    selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
    selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
    
    // Update row highlighting
    allCheckboxes.forEach(cb => {
        cb.closest('tr').classList.toggle('selected', cb.checked);
    });
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('tr').classList.remove('selected');
    });
    document.getElementById('selectAll').checked = false;
    updateBulkToolbar();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => parseInt(cb.value));
}

async function bulkAction(action) {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    let confirmMsg = '';
    switch(action) {
        case 'delete':
            confirmMsg = `Are you sure you want to DELETE ${ids.length} page(s)? This cannot be undone.`;
            break;
        case 'publish':
            confirmMsg = `Publish ${ids.length} page(s)?`;
            break;
        case 'draft':
            confirmMsg = `Set ${ids.length} page(s) to draft?`;
            break;
    }
    
    if (!confirm(confirmMsg)) return;
    
    try {
        const response = await fetch('/admin/theme-builder/bulk', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            },
            body: JSON.stringify({ action, ids })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove deleted rows or update status
            if (action === 'delete') {
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) row.remove();
                });
            } else {
                // Reload to show updated statuses
                window.location.reload();
            }
            clearSelection();
        } else {
            alert(data.error || 'Action failed');
        }
    } catch (err) {
        console.error('Bulk action error:', err);
        alert('An error occurred. Please try again.');
    }
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
