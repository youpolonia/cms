<?php
/**
 * Categories Management - Modern Dark Theme
 */
$title = 'Categories';
ob_start();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-error"><?= esc($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">📁 Categories</h2>
        <a href="/admin/categories/create" class="btn btn-primary">+ New Category</a>
    </div>

    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="empty-icon">📁</div>
            <h3>No categories yet</h3>
            <p>Categories help organize your articles and content.</p>
            <a href="/admin/categories/create" class="btn btn-primary">Create First Category</a>
        </div>
    <?php else: ?>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
                <div class="category-card">
                    <div class="category-icon">
                        <?= $cat['parent_id'] ? '📂' : '📁' ?>
                    </div>
                    <div class="category-info">
                        <h3 class="category-name"><?= esc($cat['name']) ?></h3>
                        <code class="category-slug">/<?= esc($cat['slug']) ?></code>
                        <?php if (!empty($cat['description'])): ?>
                            <p class="category-desc"><?= esc($cat['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="category-meta">
                        <?php if ($cat['parent_name']): ?>
                            <span class="meta-item parent">
                                ↳ <?= esc($cat['parent_name']) ?>
                            </span>
                        <?php endif; ?>
                        <span class="meta-item articles">
                            📄 <?= (int)$cat['article_count'] ?> article<?= $cat['article_count'] != 1 ? 's' : '' ?>
                        </span>
                        <span class="meta-item order">
                            ⬆️ Order: <?= (int)$cat['sort_order'] ?>
                        </span>
                    </div>
                    <div class="category-actions">
                        <a href="/admin/categories/<?= (int)$cat['id'] ?>/edit" class="action-btn edit" title="Edit">
                            ✏️ Edit
                        </a>
                        <?php if ($cat['article_count'] == 0 && $cat['slug'] !== 'uncategorized'): ?>
                            <form method="post" action="/admin/categories/<?= (int)$cat['id'] ?>/delete" 
                                  onsubmit="return confirm('Delete category \'<?= esc($cat['name']) ?>\'?');" 
                                  class="inline-form">
                                <?= csrf_field() ?>
                                <button type="submit" class="action-btn delete" title="Delete">
                                    🗑️ Delete
                                </button>
                            </form>
                        <?php elseif ($cat['slug'] === 'uncategorized'): ?>
                            <span class="action-btn disabled" title="Default category cannot be deleted">
                                🔒 Protected
                            </span>
                        <?php else: ?>
                            <span class="action-btn disabled" title="Has articles - cannot delete">
                                📄 Has Articles
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Category Stats -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value"><?= count($categories) ?></div>
        <div class="stat-label">Total Categories</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= array_sum(array_column($categories, 'article_count')) ?></div>
        <div class="stat-label">Total Articles</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= count(array_filter($categories, fn($c) => $c['parent_id'])) ?></div>
        <div class="stat-label">Subcategories</div>
    </div>
</div>

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

/* Card */
.card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 1.5rem;
}
.card-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
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
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}
.empty-state p {
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1rem;
    padding: 1.5rem;
}

.category-card {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    transition: all 0.2s;
}
.category-card:hover {
    border-color: var(--accent-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.category-icon {
    font-size: 2rem;
}

.category-info {
    flex: 1;
}
.category-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: var(--text-primary);
}
.category-slug {
    font-size: 0.8125rem;
    color: var(--accent-color);
    background: rgba(59, 130, 246, 0.1);
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
}
.category-desc {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin: 0.5rem 0 0 0;
    line-height: 1.5;
}

.category-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}
.meta-item {
    font-size: 0.8125rem;
    color: var(--text-muted);
}
.meta-item.parent {
    color: var(--accent-color);
}
.meta-item.articles {
    background: rgba(34, 197, 94, 0.1);
    color: #22c55e;
    padding: 0.125rem 0.5rem;
    border-radius: 4px;
}

.category-actions {
    display: flex;
    gap: 0.5rem;
    padding-top: 0.75rem;
    border-top: 1px solid var(--border-color);
}

.inline-form {
    display: inline;
}

.action-btn {
    padding: 0.5rem 1rem;
    font-size: 0.8125rem;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-secondary);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.action-btn:hover {
    background: var(--bg-primary);
}
.action-btn.edit:hover {
    background: rgba(59, 130, 246, 0.15);
    border-color: #3b82f6;
    color: #3b82f6;
}
.action-btn.delete:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: #ef4444;
    color: #ef4444;
}
.action-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    font-size: 0.75rem;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
}
.stat-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 1.25rem;
    text-align: center;
}
.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--accent-color);
}
.stat-label {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
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
    transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
    .categories-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
