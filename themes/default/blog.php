<?php
/**
 * Blog Listing Template
 * Variables: $articles (array of articles)
 */
$pageTitle = 'Blog';
$pageDescription = 'Latest articles and news';
require_once __DIR__ . '/header.php';
?>

<main class="blog-page">
    <div class="container">
        <h1>Blog</h1>
        
        <?php if (empty($articles)): ?>
        <p class="no-articles">No articles published yet.</p>
        <?php else: ?>
        <div class="articles-grid">
            <?php foreach ($articles as $article): ?>
            <article class="article-card">
                <?php if (!empty($article['featured_image'])): ?>
                <a href="/blog/<?= htmlspecialchars($article['slug']) ?>" class="article-image">
                    <img src="<?= htmlspecialchars($article['featured_image']) ?>" 
                         alt="<?= htmlspecialchars($article['featured_image_alt'] ?? $article['title']) ?>">
                </a>
                <?php endif; ?>
                <div class="article-card-content">
                    <h2><a href="/blog/<?= htmlspecialchars($article['slug']) ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
                    <div class="article-meta">
                        <?php if (!empty($article['published_at'])): ?>
                        <span class="date"><?= date('F j, Y', strtotime($article['published_at'])) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($article['category_name'])): ?>
                        <span class="category"><?= htmlspecialchars($article['category_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($article['excerpt'])): ?>
                    <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                    <?php endif; ?>
                    <a href="/blog/<?= htmlspecialchars($article['slug']) ?>" class="read-more">Read More →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/footer.php';
