<?php
/**
 * Jessie Theme - Articles List Template
 * Blog listing with sidebar
 *
 * @var array $articles List of articles
 * @var array $categories List of categories
 * @var int $currentPage Current page number
 * @var int $totalPages Total number of pages
 */
?>
<section class="blog-header">
    <div class="container">
        <h1>Blog</h1>
        <p>Latest articles and insights</p>
    </div>
</section>

<section class="blog-content">
    <div class="container">
        <div class="blog-layout">
            <main class="articles-grid">
                <?php if (empty($articles)): ?>
                <div class="empty-state">
                    <p>No articles published yet.</p>
                </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <?php if (!empty($article['featured_image'])): ?>
                        <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="article-image">
                            <img src="<?= htmlspecialchars($article['featured_image']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                        </a>
                        <?php endif; ?>
                        <div class="article-body">
                            <?php if (!empty($article['category_name'])): ?>
                            <span class="category-badge"><?= htmlspecialchars($article['category_name']) ?></span>
                            <?php endif; ?>
                            <h2><a href="/article/<?= htmlspecialchars($article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                            <p class="excerpt"><?= htmlspecialchars($article['excerpt'] ?? substr(strip_tags($article['content']), 0, 150) . '...') ?></p>
                            <div class="article-meta">
                                <span class="date"><?= date('M j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
                                <span class="views"><?= (int)($article['views'] ?? 0) ?> views</span>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </main>

            <aside class="blog-sidebar">
                <div class="sidebar-widget">
                    <h3>Categories</h3>
                    <ul class="category-list">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="/blog?category=<?= htmlspecialchars($cat['slug']) ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                                <span class="count"><?= (int)($cat['article_count'] ?? 0) ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="/blog?page=<?= $currentPage - 1 ?>" class="prev">← Previous</a>
            <?php endif; ?>
            <span class="page-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <?php if ($currentPage < $totalPages): ?>
            <a href="/blog?page=<?= $currentPage + 1 ?>" class="next">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    </div>
</section>
