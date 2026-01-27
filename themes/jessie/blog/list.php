<?php
/**
 * Jessie Theme - Blog listing template
 */
?>
<section class="blog-section">
    <div class="container">
        <div class="section-header">
            <span class="tag">Blog</span>
            <h1>Latest Articles</h1>
        </div>

        <?php if (empty($posts)): ?>
            <p class="no-posts">No posts found.</p>
        <?php else: ?>
            <div class="blog-grid">
                <?php foreach ($posts as $post): ?>
                <article class="blog-card card">
                    <?php if (!empty($post['featured_image'])): ?>
                    <div class="blog-image" style="background-image: url('<?= htmlspecialchars($post['featured_image']) ?>')"></div>
                    <?php else: ?>
                    <div class="blog-image placeholder"><span>📝</span></div>
                    <?php endif; ?>
                    <div class="blog-body">
                        <h2><a href="/article/<?= htmlspecialchars($post['slug']) ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a></h2>
                        <?php if (!empty($post['excerpt'])): ?>
                        <p><?= htmlspecialchars($post['excerpt']) ?></p>
                        <?php endif; ?>
                        <div class="blog-meta">
                            <time datetime="<?= $post['published_at'] ?? $post['created_at'] ?>">
                                <?= date('M j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
                            </time>
                            <a href="/article/<?= htmlspecialchars($post['slug']) ?>" class="read-more">Read more →</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="/articles?page=<?= $i ?>" class="<?= ($i == ($currentPage ?? 1)) ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
.blog-section { padding: 60px 0; }
.section-header { text-align: center; margin-bottom: 48px; }
.section-header .tag { margin-bottom: 16px; }
.section-header h1 { margin-bottom: 0; }
.no-posts { text-align: center; padding: 60px 0; }
.blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
@media (max-width: 900px) { .blog-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .blog-grid { grid-template-columns: 1fr; } }
.blog-card { overflow: hidden; }
.blog-image { height: 200px; background-size: cover; background-position: center; background-color: var(--bg-tertiary); }
.blog-image.placeholder { display: flex; align-items: center; justify-content: center; font-size: 3rem; background: var(--gradient-primary); }
.blog-body { padding: 24px; }
.blog-body h2 { font-size: 1.15rem; margin-bottom: 12px; line-height: 1.4; }
.blog-body h2 a { color: var(--text-primary); transition: color 0.2s; }
.blog-body h2 a:hover { color: var(--accent-primary); }
.blog-body p { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 16px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.blog-meta { display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; }
.blog-meta time { color: var(--text-muted); }
.read-more { color: var(--accent-primary); font-weight: 500; }
.pagination { display: flex; justify-content: center; gap: 8px; margin-top: 48px; }
.pagination a { padding: 10px 16px; background: var(--bg-tertiary); border: 1px solid var(--border); border-radius: var(--radius-md); color: var(--text-secondary); transition: all 0.2s; }
.pagination a:hover, .pagination a.active { background: var(--accent-primary); border-color: var(--accent-primary); color: white; }
</style>
