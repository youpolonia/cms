<?php
/**
 * Jessie Theme - Single article template
 */
?>
<article class="single-article">
    <div class="container">
        <div class="article-header">
            <?php if (!empty($post['category_name'])): ?>
            <a href="/articles?category=<?= htmlspecialchars($post['category_slug'] ?? '') ?>" class="tag"><?= htmlspecialchars($post['category_name']) ?></a>
            <?php endif; ?>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <?php if (!empty($post['excerpt'])): ?>
            <p class="excerpt"><?= htmlspecialchars($post['excerpt']) ?></p>
            <?php endif; ?>
            <div class="article-meta">
                <time datetime="<?= $post['published_at'] ?? $post['created_at'] ?>">
                    <?= date('F j, Y', strtotime($post['published_at'] ?? $post['created_at'])) ?>
                </time>
                <?php if (!empty($post['views'])): ?>
                <span class="views">👁 <?= number_format($post['views']) ?> views</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($post['featured_image'])): ?>
        <div class="featured-image">
            <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="article-content content-body">
            <?= $post['content'] ?>
        </div>

        <div class="article-footer">
            <a href="/articles" class="btn btn-secondary">← Back to Articles</a>
        </div>
    </div>
</article>

<style>
.single-article { padding: 60px 0; }
.article-header { max-width: 800px; margin: 0 auto 40px; text-align: center; }
.article-header .tag { margin-bottom: 20px; }
.article-header h1 { margin-bottom: 16px; }
.article-header .excerpt { font-size: 1.2rem; color: var(--text-secondary); margin-bottom: 24px; }
.article-meta { display: flex; justify-content: center; gap: 24px; color: var(--text-muted); font-size: 0.95rem; }
.featured-image { max-width: 1000px; margin: 0 auto 48px; }
.featured-image img { width: 100%; height: auto; border-radius: var(--radius-lg); }
.article-content { max-width: 800px; margin: 0 auto; }
.content-body { line-height: 1.8; }
.content-body h2 { margin: 40px 0 20px; font-size: 1.75rem; }
.content-body h3 { margin: 32px 0 16px; font-size: 1.35rem; }
.content-body p { margin-bottom: 20px; }
.content-body ul, .content-body ol { margin: 20px 0; padding-left: 28px; }
.content-body li { margin-bottom: 10px; color: var(--text-secondary); }
.content-body a { color: var(--accent-primary); text-decoration: underline; }
.content-body blockquote { border-left: 4px solid var(--accent-primary); padding-left: 24px; margin: 32px 0; color: var(--text-muted); font-style: italic; }
.content-body img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 32px 0; }
.content-body code { font-family: 'JetBrains Mono', monospace; background: var(--bg-tertiary); padding: 2px 8px; border-radius: 4px; font-size: 0.9em; }
.content-body pre { background: var(--bg-tertiary); padding: 24px; border-radius: var(--radius-md); overflow-x: auto; margin: 32px 0; }
.content-body pre code { background: none; padding: 0; }
.article-footer { max-width: 800px; margin: 48px auto 0; padding-top: 32px; border-top: 1px solid var(--border); }
</style>
