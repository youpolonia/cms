<?php
/**
 * JTB Theme - Archive Template
 * Used for category, tag, and date archives
 *
 * @package JTB Theme
 *
 * Variables:
 * @var string $archiveType - Type of archive (category, tag, date, author)
 * @var string $archiveTitle - Archive title
 * @var string $archiveDescription - Archive description
 * @var array $posts - Array of posts
 * @var int $currentPage - Current page number
 * @var int $totalPages - Total pages
 */

defined('CMS_ROOT') or die('Direct access not allowed');

$archiveType = $archiveType ?? 'archive';
$archiveTitle = $archiveTitle ?? 'Archive';
$archiveDescription = $archiveDescription ?? '';
$posts = $posts ?? [];
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
?>
<div class="jtb-archive jtb-archive-<?= htmlspecialchars($archiveType) ?>">
    <div class="container">
        <header class="archive-header">
            <span class="archive-label"><?= ucfirst(htmlspecialchars($archiveType)) ?></span>
            <h1 class="archive-title"><?= htmlspecialchars($archiveTitle) ?></h1>
            <?php if (!empty($archiveDescription)): ?>
            <p class="archive-description"><?= htmlspecialchars($archiveDescription) ?></p>
            <?php endif; ?>
        </header>

        <?php if (!empty($posts)): ?>
        <div class="archive-grid">
            <?php foreach ($posts as $post): ?>
            <article class="archive-card">
                <?php if (!empty($post['featured_image'])): ?>
                <a href="/post/<?= htmlspecialchars($post['slug']) ?>" class="card-image">
                    <img src="<?= htmlspecialchars($post['featured_image']) ?>" alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy">
                </a>
                <?php endif; ?>

                <div class="card-content">
                    <h2 class="card-title">
                        <a href="/post/<?= htmlspecialchars($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h2>

                    <div class="card-meta">
                        <time datetime="<?= htmlspecialchars($post['created_at'] ?? '') ?>"><?= date('M j, Y', strtotime($post['created_at'] ?? 'now')) ?></time>
                    </div>

                    <?php if (!empty($post['excerpt'])): ?>
                    <p class="card-excerpt"><?= htmlspecialchars(mb_substr(strip_tags($post['excerpt']), 0, 120)) ?>...</p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="archive-pagination" aria-label="Archive pagination">
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
        <div class="archive-empty">
            <p>No posts found in this archive.</p>
            <a href="/blog" class="back-link">← Back to Blog</a>
        </div>
        <?php endif; ?>
    </div>
</div>
