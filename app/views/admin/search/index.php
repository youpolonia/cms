<?php
$title = 'Search';

function getEditUrl(array $item): string {
    $type = $item['entity_type'] ?? '';
    $id = $item['id'] ?? 0;
    return match($type) {
        'page' => "/admin/pages/{$id}/edit",
        'article' => "/admin/articles/{$id}/edit",
        'category' => "/admin/categories/{$id}/edit",
        'user' => "/admin/users/{$id}/edit",
        'content_block' => "/admin/content/{$id}/edit",
        'menu' => "/admin/menus/{$id}/items",
        default => '/admin'
    };
}

ob_start();
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Search CMS</h2>
        <a href="/admin/search/analytics" class="btn btn-secondary btn-sm">View Analytics</a>
    </div>
    <div class="card-body">
        <form method="get" action="/admin/search" style="max-width: 600px; margin-bottom: 1.5rem;">
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" name="q" value="<?= esc($query) ?>" placeholder="Search pages, articles, users..." style="flex: 1;" autofocus>
                <select name="type" style="width: 150px;">
                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Types</option>
                    <option value="pages" <?= $type === 'pages' ? 'selected' : '' ?>>Pages</option>
                    <option value="articles" <?= $type === 'articles' ? 'selected' : '' ?>>Articles</option>
                    <option value="categories" <?= $type === 'categories' ? 'selected' : '' ?>>Categories</option>
                    <option value="users" <?= $type === 'users' ? 'selected' : '' ?>>Users</option>
                    <option value="content" <?= $type === 'content' ? 'selected' : '' ?>>Content Blocks</option>
                    <option value="menus" <?= $type === 'menus' ? 'selected' : '' ?>>Menus</option>
                </select>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <?php if (!empty($query)): ?>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">
                Found <strong><?= $totalCount ?></strong> result<?= $totalCount !== 1 ? 's' : '' ?> for "<strong><?= esc($query) ?></strong>"
            </p>

            <?php if ($totalCount === 0): ?>
                <p>No results found. Try a different search term.</p>
            <?php else: ?>
                <?php foreach ($results as $section => $items): ?>
                    <?php if (!empty($items)): ?>
                        <div style="margin-bottom: 1.5rem;">
                            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;"><?= ucfirst($section) ?> (<?= count($items) ?>)</h3>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <?php foreach ($items as $item): ?>
                                    <div style="padding: 0.75rem 1rem; background: #f8fafc; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong><?= esc($item['title'] ?? $item['name'] ?? $item['username'] ?? 'Unknown') ?></strong>
                                            <?php if (!empty($item['slug'])): ?>
                                                <code style="margin-left: 0.5rem; font-size: 0.75rem;"><?= esc($item['slug']) ?></code>
                                            <?php endif; ?>
                                            <?php if (!empty($item['status'])): ?>
                                                <span class="badge badge-<?= $item['status'] === 'published' ? 'success' : 'muted' ?>" style="margin-left: 0.5rem;"><?= esc($item['status']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <a href="<?= getEditUrl($item) ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php else: ?>
            <p style="color: var(--text-muted);">Enter a search term (minimum 2 characters).</p>
        <?php endif; ?>
    </div>
</div>

<style>
.badge-success { background: #d1fae5; color: #065f46; }
.badge-muted { background: #e2e8f0; color: #64748b; }
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
