<?php
/**
 * Single Article Template
 * Variables: $article (array with all article data)
 */
$pageTitle = $article['meta_title'] ?: $article['title'];
$pageDescription = $article['meta_description'] ?: $article['excerpt'];
require_once __DIR__ . '/header.php';
?>

<main class="article-page">
    <div class="container">
        <article class="article-single">
            <?php if (!empty($article['featured_image'])): ?>
            <div class="featured-image">
                <img src="<?= htmlspecialchars($article['featured_image']) ?>" 
                     alt="<?= htmlspecialchars($article['featured_image_alt'] ?? $article['title']) ?>"
                     <?php if (!empty($article['featured_image_title'])): ?>title="<?= htmlspecialchars($article['featured_image_title']) ?>"<?php endif; ?>>
            </div>
            <?php endif; ?>
            
            <header class="article-header">
                <h1><?= htmlspecialchars($article['title']) ?></h1>
                <div class="article-meta">
                    <?php if (!empty($article['author_name'])): ?>
                    <span class="author">By <?= htmlspecialchars($article['author_name']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($article['published_at'])): ?>
                    <span class="date"><?= date('F j, Y', strtotime($article['published_at'])) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($article['category_name'])): ?>
                    <span class="category"><?= htmlspecialchars($article['category_name']) ?></span>
                    <?php endif; ?>
                </div>
            </header>
            
            <div class="article-content">
                <?= $article['content'] ?>
            </div>
            
            <?php if (!empty($article['meta_keywords'])): ?>
            <footer class="article-tags">
                <?php foreach (explode(',', $article['meta_keywords']) as $tag): ?>
                <span class="tag"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
            </footer>
            <?php endif; ?>
        </article>
        
        <nav class="article-nav">
            <a href="/blog" class="back-link">← Back to Blog</a>
        </nav>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php';
