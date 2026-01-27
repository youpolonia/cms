<?php
$pageTitle = 'Articles';
require_once __DIR__ . '/layouts/header.php';
?>
<section class="page-hero">
    <div class="container">
        <span class="tag">Blog</span>
        <h1>Articles</h1>
        <p>Insights, tutorials, and updates.</p>
    </div>
</section>

<section class="articles-list">
    <div class="container">
        <?php if (empty($articles)): ?>
        <p class="no-articles">No articles yet.</p>
        <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $a): ?>
            <article class="article-card card">
                <?php if (!empty($a['featured_image'])): ?>
                <div class="article-image" style="background-image: url('<?= esc($a['featured_image']) ?>')"></div>
                <?php else: ?>
                <div class="article-image placeholder"><span>📝</span></div>
                <?php endif; ?>
                <div class="article-body">
                    <?php if (!empty($a['category_name'])): ?>
                    <span class="article-cat"><?= esc($a['category_name']) ?></span>
                    <?php endif; ?>
                    <h3><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                    <?php if (!empty($a['excerpt'])): ?>
                    <p><?= esc($a['excerpt']) ?></p>
                    <?php endif; ?>
                    <div class="article-meta">
                        <span><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                        <a href="/article/<?= esc($a['slug']) ?>" class="read-more">Read more →</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="/articles?page=<?= $currentPage - 1 ?>" class="btn btn-secondary">← Prev</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
            <a href="/articles?page=<?= $currentPage + 1 ?>" class="btn btn-secondary">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.page-hero { padding: 160px 0 60px; text-align: center; }
.page-hero .tag { margin-bottom: 16px; }
.page-hero h1 { margin-bottom: 12px; }
.articles-list { padding: 40px 0 100px; }
.no-articles { text-align: center; padding: 60px; color: var(--text-muted); }
.articles-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
@media (max-width: 900px) { .articles-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .articles-grid { grid-template-columns: 1fr; } }
.article-card { overflow: hidden; }
.article-image { height: 180px; background-size: cover; background-position: center; background-color: var(--bg-tertiary); }
.article-image.placeholder { display: flex; align-items: center; justify-content: center; font-size: 3rem; background: var(--gradient-primary); }
.article-body { padding: 20px; }
.article-cat { display: inline-block; padding: 4px 10px; background: rgba(139,92,246,0.15); color: var(--accent-primary); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; border-radius: 50px; margin-bottom: 10px; }
.article-body h3 { font-size: 1.1rem; margin-bottom: 10px; }
.article-body h3 a { color: var(--text-primary); }
.article-body h3 a:hover { color: var(--accent-primary); }
.article-body > p { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.article-meta { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); }
.read-more { color: var(--accent-primary); }
.pagination { display: flex; justify-content: center; align-items: center; gap: 20px; margin-top: 48px; }
.page-info { color: var(--text-muted); }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
