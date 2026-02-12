<?php
/**
 * Articles List - Modern Dark Theme
 */
$title = 'Articles';
ob_start();

// Count stats
$totalArticles = count($articles);
$publishedCount = count(array_filter($articles, fn($a) => $a['status'] === 'published'));
$draftCount = count(array_filter($articles, fn($a) => $a['status'] === 'draft'));
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>


<div class="inline-help" id="help-articles">
    <span class="inline-help-icon">💡</span>
    <div><strong>Articles</strong> are blog posts and news updates. They appear in chronological feeds and can be categorized. Use <a href="/admin/ai-content-creator">AI Content Creator</a> to generate drafts quickly. <a href="/admin/docs?section=articles">Read more →</a></div>
    <button class="inline-help-close" onclick="this.closest('.inline-help').style.display='none';localStorage.setItem('help-articles-hidden','1')" title="Dismiss">×</button>
</div>
<script>if(localStorage.getItem('help-articles-hidden'))document.getElementById('help-articles').style.display='none'</script>
<div class="page-header">
    <div class="page-header-content">
        <h1 class="page-title">📝 Articles</h1>
        <p class="page-description">Manage your blog posts and articles</p>
    </div>
    <div class="page-header-actions">
        <a href="/admin/articles/create" class="btn btn-primary">+ New Article</a>
    </div>
</div>

<!-- Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon">📝</div>
        <div class="stat-info">
            <div class="stat-value"><?= $totalArticles ?></div>
            <div class="stat-label">Total Articles</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <div class="stat-value"><?= $publishedCount ?></div>
            <div class="stat-label">Published</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <div class="stat-value"><?= $draftCount ?></div>
            <div class="stat-label">Drafts</div>
        </div>
    </div>
</div>

<?php if (empty($articles)): ?>
<div class="card">
    <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h3>No articles yet</h3>
        <p>Start writing your first article to engage your audience.</p>
        <a href="/admin/articles/create" class="btn btn-primary">Create First Article</a>
    </div>
</div>
<?php else: ?>

<!-- Filters -->
<div class="card filters-card">
    <div class="filters">
        <div class="filter-group">
            <input type="text" class="form-control" placeholder="🔍 Search articles..." id="searchInput">
        </div>
        <div class="filter-group">
            <select class="form-control" id="statusFilter">
                <option value="">All Status</option>
                <option value="published">✅ Published</option>
                <option value="draft">📋 Draft</option>
                <option value="archived">📦 Archived</option>
            </select>
        </div>
        <?php if (!empty($categories)): ?>
        <div class="filter-group">
            <select class="form-control" id="categoryFilter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>">📁 <?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Articles Table -->
<div class="card">
    <table class="articles-table" id="articlesTable">
        <thead>
            <tr>
                <th>Article</th>
                <th>Category <span class="tip"><span class="tip-text">Used for organizing and filtering articles.</span></span></th>
                <th>Status <span class="tip"><span class="tip-text">Draft articles are only visible to admins.</span></span></th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($articles as $article): ?>
            <tr data-status="<?= esc($article['status']) ?>" 
                data-title="<?= esc(strtolower($article['title'])) ?>"
                data-category="<?= (int)($article['category_id'] ?? 0) ?>">
                <td>
                    <div class="article-cell">
                        <?php if (!empty($article['featured_image'])): ?>
                            <img src="<?= esc($article['featured_image']) ?>" alt="" class="article-thumb">
                        <?php else: ?>
                            <div class="article-thumb-placeholder">📝</div>
                        <?php endif; ?>
                        <div class="article-info">
                            <a href="/admin/articles/<?= (int)$article['id'] ?>/edit" class="article-title">
                                <?= esc($article['title']) ?>
                            </a>
                            <div class="article-slug">/<?= esc($article['slug']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if (!empty($article['category_name'])): ?>
                        <span class="category-badge">📁 <?= esc($article['category_name']) ?></span>
                    <?php else: ?>
                        <span class="no-category">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?= $article['status'] ?>">
                        <?php echo match($article['status']) {
                            'published' => '● Published',
                            'draft' => '● Draft',
                            'archived' => '● Archived',
                            default => '● ' . ucfirst($article['status'])
                        }; ?>
                    </span>
                </td>
                <td>
                    <div class="date-cell">
                        <span class="date-primary"><?= date('M j, Y', strtotime($article['updated_at'])) ?></span>
                        <span class="date-secondary"><?= date('g:i A', strtotime($article['updated_at'])) ?></span>
                    </div>
                </td>
                <td>
                    <div class="actions-cell">
                        <a href="/admin/articles/<?= (int)$article['id'] ?>/edit" class="action-btn" title="Edit">
                            ✏️
                        </a>
                        <a href="/preview/article/<?= (int)$article['id'] ?>" target="_blank" class="action-btn" title="Preview">
                            👁️
                        </a>
                        <form method="POST" action="/admin/articles/<?= (int)$article['id'] ?>/delete" 
                              onsubmit="return confirm('Delete article: <?= esc(addslashes($article['title'])) ?>?');"
                              class="inline-form">
                            <?= csrf_field() ?>
                            <button type="submit" class="action-btn delete" title="Delete">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

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
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}
.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0;
}
.page-description {
    color: var(--text-muted);
    margin: 0.25rem 0 0 0;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    font-size: 1.5rem;
}
.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}
.stat-label {
    font-size: 0.8125rem;
    color: var(--text-muted);
}

/* Card */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}
.empty-state h3 {
    margin: 0 0 0.5rem 0;
}
.empty-state p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

/* Filters */
.filters-card {
    padding: 1rem 1.5rem;
}
.filters {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}
.filter-group {
    flex: 1;
    min-width: 150px;
    max-width: 250px;
}
.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 0.875rem;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent-color);
}

/* Table */
.articles-table {
    width: 100%;
    border-collapse: collapse;
}
.articles-table th {
    padding: 1rem 1.5rem;
    text-align: left;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-color);
}
.articles-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}
.articles-table tr:last-child td {
    border-bottom: none;
}
.articles-table tr:hover {
    background: var(--bg-primary);
}

/* Article Cell */
.article-cell {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.article-thumb {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    object-fit: cover;
}
.article-thumb-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    background: var(--bg-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.article-title {
    font-weight: 600;
    color: var(--text-primary);
    text-decoration: none;
    display: block;
    margin-bottom: 0.25rem;
}
.article-title:hover {
    color: var(--accent-color);
}
.article-slug {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-family: monospace;
}

/* Category Badge */
.category-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: rgba(59, 130, 246, 0.1);
    color: var(--accent-color);
    border-radius: 20px;
    font-size: 0.8125rem;
}
.no-category {
    color: var(--text-muted);
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8125rem;
    font-weight: 500;
}
.status-badge.published {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
}
.status-badge.draft {
    background: rgba(245, 158, 11, 0.15);
    color: #f59e0b;
}
.status-badge.archived {
    background: rgba(107, 114, 128, 0.15);
    color: #9ca3af;
}

/* Date Cell */
.date-cell {
    display: flex;
    flex-direction: column;
}
.date-primary {
    font-weight: 500;
    color: var(--text-primary);
}
.date-secondary {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Actions */
.actions-cell {
    display: flex;
    gap: 0.5rem;
    opacity: 0.6;
    transition: opacity 0.2s;
}
.articles-table tr:hover .actions-cell {
    opacity: 1;
}
.action-btn {
    padding: 0.375rem 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 6px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s;
    text-decoration: none;
}
.action-btn:hover {
    background: var(--bg-primary);
    border-color: var(--border-color);
}
.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.3);
}

.inline-form {
    display: inline;
}

/* Button */
.btn-primary {
    background: var(--accent-color);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary:hover {
    background: #2563eb;
}

/* Responsive */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
    }
    .stats-row {
        grid-template-columns: 1fr;
    }
    .article-thumb, .article-thumb-placeholder {
        display: none;
    }
    .articles-table th, .articles-table td {
        padding: 0.75rem 1rem;
    }
}
</style>

<script>
// Filters
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const categoryFilter = document.getElementById('categoryFilter');

[searchInput, statusFilter, categoryFilter].forEach(el => {
    el?.addEventListener('input', filterTable);
    el?.addEventListener('change', filterTable);
});

function filterTable() {
    const search = searchInput?.value.toLowerCase() || '';
    const status = statusFilter?.value || '';
    const category = categoryFilter?.value || '';
    const rows = document.querySelectorAll('#articlesTable tbody tr');
    
    rows.forEach(row => {
        const title = row.dataset.title || '';
        const rowStatus = row.dataset.status || '';
        const rowCategory = row.dataset.category || '';
        
        const matchSearch = !search || title.includes(search);
        const matchStatus = !status || rowStatus === status;
        const matchCategory = !category || rowCategory === category;
        
        row.style.display = (matchSearch && matchStatus && matchCategory) ? '' : 'none';
    });
}
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
