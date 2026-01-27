<?php
/**
 * JTB Theme - Blog Listing Template
 *
 * @package JTB Theme
 *
 * Variables:
 * @var array $posts - Array of posts
 * @var int $currentPage - Current page number
 * @var int $totalPages - Total pages
 * @var string $category - Current category (if filtering)
 */

defined('CMS_ROOT') or die('Direct access not allowed');

$posts = $posts ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$category = $category ?? null;
?>
<div class="jtb-blog-listing">
    <div class="container">
        <header class="blog-header">
            <?php if ($category): ?>
            <h1 class="blog-title">Category: <?= htmlspecialchars($category) ?></h1>
            <?php else: ?>
            <h1 class="blog-title">Blog</h1>
            <?php endif; ?>
        </header>

        <?php if (!empty($posts)): ?>
        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
            <article class="blog-card">
                <?php if (!empty($post['featured_image'])): ?>
                <a href="/post/<?= htmlspecialchars($post['slug']) ?>" class="card-image">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                </a>
                <?php endif; ?>

                <div class="card-content">
                    <?php if (!empty($post['categories'])): ?>
                    <div class="card-categories">
                        <?php foreach (array_slice($post['categories'], 0, 2) as $cat): ?>
                        <a href="/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="category-tag"><?= htmlspecialchars($cat['name'] ?? '') ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <h2 class="card-title">
                        <a href="/post/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h2>

                    <?php if (!empty($post['excerpt'])): ?>
                    <p class="card-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($post['excerpt']), 0, 150)) ?>...</p>
                    <?php endif; ?>

                    <div class="card-meta">
                        <time datetime="<?= htmlspecialchars($post['created_at'] ?? '') ?>"><?= date('M j, Y', strtotime($post['created_at'] ?? 'now')) ?></time>
                        <span class="separator">•</span>
                        <span class="author"><?= htmlspecialchars($post['author_name'] ?? 'Unknown') ?></span>
                    </div>

                    <a href="/post/<?= htmlspecialchars($post['slug']) ?>" class="card-link">Read More →</a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="blog-pagination" aria-label="Blog pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="pagination-link prev">← Previous</a>
            <?php endif; ?>

            <span class="pagination-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>

            <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="pagination-link next">Next →</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <?php else: ?>
        <div class="blog-empty">
            <p>No posts found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
