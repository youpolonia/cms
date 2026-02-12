<?php
/**
 * Admin Dashboard v2.0
 * Modern SaaS-style dashboard with stats, quick actions, and recent activity
 */

// Get stats
$pdo = db();

// Pages stats
$pagesTotal = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn() ?: 0;
$pagesPublished = $pdo->query("SELECT COUNT(*) FROM pages WHERE status = 'published'")->fetchColumn() ?: 0;
$pagesDraft = $pdo->query("SELECT COUNT(*) FROM pages WHERE status = 'draft'")->fetchColumn() ?: 0;

// Articles stats
$articlesTotal = 0;
$articlesPublished = 0;
try {
    $articlesTotal = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn() ?: 0;
    $articlesPublished = $pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn() ?: 0;
} catch (\PDOException $e) {}

// Media stats
$mediaCount = 0;
$mediaDir = dirname(CMS_APP) . '/uploads/media/';
if (is_dir($mediaDir)) {
    $mediaCount = count(array_filter(scandir($mediaDir), fn($f) => !in_array($f, ['.', '..', 'thumbs']) && is_file($mediaDir . $f)));
}

// Users stats
$usersTotal = 0;
try {
    $usersTotal = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() ?: 0;
} catch (\PDOException $e) {}

// Recent pages
$recentPages = [];
try {
    $stmt = $pdo->query("SELECT id, title, status, updated_at FROM pages ORDER BY updated_at DESC LIMIT 5");
    $recentPages = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {}

// Recent articles
$recentArticles = [];
try {
    $stmt = $pdo->query("SELECT id, title, status, updated_at FROM articles ORDER BY updated_at DESC LIMIT 5");
    $recentArticles = $stmt->fetchAll(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {}

$title = 'Dashboard';
ob_start();
?>

<div class="dashboard">
    <!-- Stats Grid -->
    <div class="stats-grid mb-6">
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon primary">📄</div>
            </div>
            <div class="stat-value"><?= $pagesTotal ?></div>
            <div class="stat-label">Total Pages <span class="tip"><span class="tip-text">All pages including drafts and published.</span></span></div>
            <div class="stat-change positive">
                <span><?= $pagesPublished ?> published</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon success">📝</div>
            </div>
            <div class="stat-value"><?= $articlesTotal ?></div>
            <div class="stat-label">Total Articles <span class="tip"><span class="tip-text">Blog posts and news articles across all categories.</span></span></div>
            <div class="stat-change positive">
                <span><?= $articlesPublished ?> published</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon warning">🖼️</div>
            </div>
            <div class="stat-value"><?= $mediaCount ?></div>
            <div class="stat-label">Media Files <span class="tip"><span class="tip-text">Images, documents and other uploaded files.</span></span></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-header">
                <div class="stat-icon danger">👥</div>
            </div>
            <div class="stat-value"><?= $usersTotal ?></div>
            <div class="stat-label">Users</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card mb-6">
        <div class="card-header">
            <h2 class="card-title">Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="quick-actions">
                <a href="/admin/pages/create" class="quick-action-card">
                    <span class="quick-action-icon">📄</span>
                    <span class="quick-action-label">New Page</span>
                </a>
                <a href="/admin/articles/create" class="quick-action-card">
                    <span class="quick-action-icon">📝</span>
                    <span class="quick-action-label">New Article</span>
                </a>
                <a href="/admin/ai-copywriter.php" class="quick-action-card">
                    <span class="quick-action-icon">✍️</span>
                    <span class="quick-action-label">AI Copywriter</span>
                </a>
                <a href="/admin/ai-seo-assistant.php" class="quick-action-card">
                    <span class="quick-action-icon">🎯</span>
                    <span class="quick-action-label">SEO Assistant</span>
                </a>
                <a href="/admin/media" class="quick-action-card">
                    <span class="quick-action-icon">🖼️</span>
                    <span class="quick-action-label">Media Library</span>
                </a>
                <a href="/admin/settings" class="quick-action-card">
                    <span class="quick-action-icon">⚙️</span>
                    <span class="quick-action-label">Settings</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="dashboard-grid">
        <!-- Recent Pages -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Pages</h2>
                <a href="/admin/pages" class="btn btn-ghost btn-sm">View All →</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recentPages)): ?>
                <div class="empty-state" style="padding: 32px;">
                    <div class="empty-state-icon">📄</div>
                    <div class="empty-state-title">No pages yet</div>
                    <div class="empty-state-description">Create your first page to get started.</div>
                    <a href="/admin/pages/create" class="btn btn-primary">Create Page</a>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentPages as $page): ?>
                    <a href="/admin/pages/<?= (int)$page['id'] ?>/edit" class="activity-item">
                        <div class="activity-content">
                            <div class="activity-title"><?= esc($page['title']) ?></div>
                            <div class="activity-meta">
                                <?= date('M j, Y', strtotime($page['updated_at'])) ?>
                            </div>
                        </div>
                        <span class="badge badge-<?= $page['status'] === 'published' ? 'success' : 'warning' ?> badge-dot">
                            <?= ucfirst($page['status']) ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Articles -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Articles</h2>
                <a href="/admin/articles" class="btn btn-ghost btn-sm">View All →</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (empty($recentArticles)): ?>
                <div class="empty-state" style="padding: 32px;">
                    <div class="empty-state-icon">📝</div>
                    <div class="empty-state-title">No articles yet</div>
                    <div class="empty-state-description">Start writing your first article.</div>
                    <a href="/admin/articles/create" class="btn btn-primary">Create Article</a>
                </div>
                <?php else: ?>
                <div class="activity-list">
                    <?php foreach ($recentArticles as $article): ?>
                    <a href="/admin/articles/<?= (int)$article['id'] ?>/edit" class="activity-item">
                        <div class="activity-content">
                            <div class="activity-title"><?= esc($article['title']) ?></div>
                            <div class="activity-meta">
                                <?= date('M j, Y', strtotime($article['updated_at'])) ?>
                            </div>
                        </div>
                        <span class="badge badge-<?= $article['status'] === 'published' ? 'success' : 'warning' ?> badge-dot">
                            <?= ucfirst($article['status']) ?>
                        </span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
}

.quick-action-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px;
    background: var(--bg-secondary);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    text-decoration: none;
    transition: all 0.15s;
}

.quick-action-card:hover {
    background: var(--bg-tertiary);
    border-color: var(--accent);
    transform: translateY(-2px);
}

.quick-action-icon {
    font-size: 1.5rem;
}

.quick-action-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}

.activity-list {
    display: flex;
    flex-direction: column;
}

.activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
    text-decoration: none;
    transition: background 0.15s;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: var(--bg-secondary);
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-title {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.activity-meta {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
}

@media (max-width: 900px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
