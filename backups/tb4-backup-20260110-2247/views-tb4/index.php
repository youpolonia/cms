<?php
/**
 * TB4 Builder - Page List View
 * Simple page management dashboard
 * Uses Catppuccin theme and admin styling
 */
$title = 'TB4 Builder';
ob_start();

// Ensure we have data
$pages = $pages ?? [];

// Calculate stats
$totalPages = count($pages);
$publishedPages = count(array_filter($pages, fn($p) => ($p['status'] ?? '') === 'published'));
$draftPages = $totalPages - $publishedPages;
$recentPages = count(array_filter($pages, fn($p) => strtotime($p['updated_at'] ?? '1970-01-01') > strtotime('-7 days')));
?>

<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">TB4 Builder</h1>
        <p class="page-description">Visual page builder - Create and edit pages with drag-and-drop</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/tb4/create" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            New Page
        </a>
    </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="margin-bottom: var(--space-6);">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?= $totalPages ?></div>
        <div class="stat-label">Total Pages</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?= $publishedPages ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon warning">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?= $draftPages ?></div>
        <div class="stat-label">Drafts</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon info">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
        </div>
        <div class="stat-value"><?= $recentPages ?></div>
        <div class="stat-label">Updated This Week</div>
    </div>
</div>

<?php if (empty($pages)): ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="3" x2="9" y2="21"/>
            </svg>
        </div>
        <div class="empty-state-title">No TB4 pages yet</div>
        <div class="empty-state-description">Create your first page with the visual builder.</div>
        <a href="/admin/tb4/create" class="btn btn-primary">Create Your First Page</a>
    </div>
</div>
<?php else: ?>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body" style="padding: var(--space-3) var(--space-4);">
        <div class="filters">
            <div class="filter-group">
                <input type="text" class="form-input" placeholder="Search pages..." id="search-input" style="max-width: 300px;">
            </div>
            <div class="filter-group">
                <select class="form-select" id="status-filter" style="max-width: 150px;">
                    <option value="">All Status</option>
                    <option value="published">Published</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Pages Table -->
<div class="card">
    <div class="table-container">
        <table class="table" id="tb4-table">
            <thead>
                <tr>
                    <th class="sortable" data-sort="title">Title</th>
                    <th>Slug</th>
                    <th class="sortable" data-sort="status">Status</th>
                    <th class="sortable" data-sort="updated_at">Last Modified</th>
                    <th style="width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr data-status="<?= esc($page['status'] ?? 'draft') ?>" data-title="<?= esc(strtolower($page['title'] ?? '')) ?>">
                    <td>
                        <a href="/admin/tb4/edit/<?= (int)$page['id'] ?>" class="table-title">
                            <?= esc($page['title'] ?? 'Untitled') ?>
                        </a>
                    </td>
                    <td>
                        <code class="table-code">/<?= esc($page['slug'] ?? '') ?></code>
                    </td>
                    <td>
                        <span class="badge badge-<?= ($page['status'] ?? 'draft') === 'published' ? 'success' : 'warning' ?> badge-dot">
                            <?= ucfirst($page['status'] ?? 'draft') ?>
                        </span>
                    </td>
                    <td>
                        <span class="text-muted"><?= date('M j, Y g:ia', strtotime($page['updated_at'] ?? 'now')) ?></span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="/admin/tb4/edit/<?= (int)$page['id'] ?>" class="btn btn-ghost btn-icon btn-sm" title="Edit in Builder">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </a>
                            <a href="/preview/tb4/<?= (int)$page['id'] ?>" target="_blank" class="btn btn-ghost btn-icon btn-sm" title="Preview">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                            <form method="POST" action="/admin/tb4/toggle/<?= (int)$page['id'] ?>" style="display: inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm" title="<?= ($page['status'] ?? 'draft') === 'published' ? 'Unpublish' : 'Publish' ?>">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <?php if (($page['status'] ?? 'draft') === 'published'): ?>
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="9" x2="15" y2="15"/><line x1="15" y1="9" x2="9" y2="15"/>
                                        <?php else: ?>
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                        <?php endif; ?>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="/admin/tb4/delete/<?= (int)$page['id'] ?>" style="display: inline;"
                                  onsubmit="return confirm('Delete this page? This action cannot be undone.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-icon btn-sm btn-danger-hover" title="Delete">
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
</div>
<?php endif; ?>

<style>
.page-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: var(--space-6);
}

.page-title {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--color-text-primary);
}

.page-description {
    font-size: var(--text-sm);
    color: var(--color-text-muted);
    margin-top: var(--space-1);
}

.filters {
    display: flex;
    gap: var(--space-3);
    flex-wrap: wrap;
}

.table-title {
    font-weight: var(--font-medium);
    color: var(--color-text-primary);
    text-decoration: none;
}

.table-title:hover {
    color: var(--color-accent);
}

.table-code {
    font-family: var(--font-mono);
    font-size: var(--text-xs);
    background: var(--color-bg-tertiary);
    padding: 2px 6px;
    border-radius: var(--radius-sm);
    color: var(--color-text-secondary);
}

.table-actions {
    display: flex;
    gap: var(--space-1);
    opacity: 0.6;
    transition: opacity var(--transition-fast);
}

tr:hover .table-actions {
    opacity: 1;
}

.btn-danger-hover:hover {
    color: var(--color-danger, #f87171);
}

.empty-state-icon svg {
    color: var(--color-text-muted);
    opacity: 0.5;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: var(--space-4);
    }

    .page-header-actions {
        width: 100%;
    }

    .page-header-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Search filter
document.getElementById('search-input')?.addEventListener('input', filterTable);
document.getElementById('status-filter')?.addEventListener('change', filterTable);

function filterTable() {
    const search = document.getElementById('search-input').value.toLowerCase();
    const status = document.getElementById('status-filter').value;
    const rows = document.querySelectorAll('#tb4-table tbody tr');

    rows.forEach(row => {
        const title = row.dataset.title || '';
        const rowStatus = row.dataset.status || '';
        const matchSearch = !search || title.includes(search);
        const matchStatus = !status || rowStatus === status;
        row.style.display = matchSearch && matchStatus ? '' : 'none';
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
