<?php
/**
 * JTB Theme - Single Article/Page Template
 *
 * @package JTB Theme
 *
 * Variables:
 * @var array $post - Post data
 * @var string $content - Post content (already rendered)
 */

defined('CMS_ROOT') or die('Direct access not allowed');

$title = $post['title'] ?? 'Untitled';
$content = $post['content'] ?? '';
$featuredImage = $post['featured_image'] ?? '';
$author = $post['author_name'] ?? 'Unknown';
$date = $post['created_at'] ?? date('Y-m-d');
$formattedDate = date('F j, Y', strtotime($date));
$categories = $post['categories'] ?? [];
$type = $post['type'] ?? 'post';
?>
<article class="jtb-article jtb-single-<?= htmlspecialchars($type) ?>">
    <header class="article-header">
        <?php if (!empty($featuredImage)): ?>
        <div class="article-featured-image">
            <img src="<?= htmlspecialchars($featuredImage) ?>" alt="<?= htmlspecialchars($title) ?>">
        </div>
        <?php endif; ?>

        <div class="container">
            <?php if (!empty($categories)): ?>
            <div class="article-categories">
                <?php foreach ($categories as $cat): ?>
                <a href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="category-tag"><?= htmlspecialchars($cat['name'] ?? '') ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <h1 class="article-title"><?= htmlspecialchars($title) ?></h1>

            <?php if ($type === 'post'): ?>
            <div class="article-meta">
                <span class="meta-author">By <?= htmlspecialchars($author) ?></span>
                <span class="meta-separator">•</span>
                <time class="meta-date" datetime="<?= htmlspecialchars($date) ?>"><?= $formattedDate ?></time>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="article-content">
        <div class="container">
            <?= $content ?>
        </div>
    </div>

    <?php if ($type === 'post'): ?>
    <footer class="article-footer">
        <div class="container">
            <div class="article-share">
                <span>Share:</span>
                <a href="https://twitter.com/intent/tweet?url=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?>&text=<?= urlencode($title) ?>" target="_blank" rel="noopener" aria-label="Share on Twitter">Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook">Facebook</a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode('https://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '')) ?>&title=<?= urlencode($title) ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn">LinkedIn</a>
            </div>
        </div>
    </footer>
    <?php endif; ?>
</article>
