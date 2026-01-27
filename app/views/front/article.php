<?php
$pageTitle = $article['title'] ?? 'Article';
require_once __DIR__ . '/layouts/header.php';
?>
<article class="single-article">
    <div class="container-narrow">
        <header class="article-header">
            <?php if (!empty($article['category_name'])): ?>
            <span class="tag"><?= esc($article['category_name']) ?></span>
            <?php endif; ?>
            <h1><?= esc($article['title']) ?></h1>
            <?php if (!empty($article['excerpt'])): ?>
            <p class="excerpt"><?= esc($article['excerpt']) ?></p>
            <?php endif; ?>
            <div class="article-meta">
                <span>📅 <?= date('F j, Y', strtotime($article['published_at'] ?? $article['created_at'])) ?></span>
                <span>👁 <?= number_format($article['views'] ?? 0) ?> views</span>
            </div>
        </header>
        <?php if (!empty($article['featured_image'])): ?>
        <div class="featured-image">
            <img src="<?= esc($article['featured_image']) ?>" alt="<?= esc($article['title']) ?>">
        </div>
        <?php endif; ?>
        <div class="article-content">
            <?= $article['content'] ?? '' ?>
        </div>
        <footer class="article-footer">
            <a href="/articles" class="btn btn-secondary">← Back to Articles</a>
        </footer>
    </div>
</article>

<style>
.container-narrow { max-width: 800px; margin: 0 auto; padding: 0 24px; }
.single-article { padding: 140px 0 80px; }
.article-header { text-align: center; margin-bottom: 40px; }
.article-header .tag { margin-bottom: 20px; }
.article-header h1 { margin-bottom: 16px; font-size: clamp(2rem, 4vw, 3rem); }
.article-header .excerpt { font-size: 1.2rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto 20px; }
.article-meta { display: flex; justify-content: center; gap: 24px; color: var(--text-muted); font-size: 0.9rem; }
.featured-image { margin-bottom: 40px; border-radius: var(--radius-lg); overflow: hidden; }
.featured-image img { width: 100%; height: auto; display: block; }
.article-content { line-height: 1.8; font-size: 1.05rem; }
.article-content h2 { margin: 40px 0 20px; font-size: 1.75rem; }
.article-content h3 { margin: 32px 0 16px; font-size: 1.35rem; }
.article-content p { margin-bottom: 20px; }
.article-content a { color: var(--accent-primary); text-decoration: underline; }
.article-content img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 24px 0; }
.article-content blockquote { border-left: 4px solid var(--accent-primary); padding-left: 20px; margin: 24px 0; font-style: italic; color: var(--text-secondary); }
.article-content code { font-family: 'JetBrains Mono', monospace; background: var(--bg-tertiary); padding: 2px 6px; border-radius: 4px; font-size: 0.9em; }
.article-content pre { background: var(--bg-tertiary); padding: 20px; border-radius: var(--radius-md); overflow-x: auto; margin: 24px 0; }
.article-content pre code { background: none; padding: 0; }
.article-footer { margin-top: 60px; padding-top: 40px; border-top: 1px solid var(--border); }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
