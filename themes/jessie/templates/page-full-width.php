<?php
/**
 * Jessie Theme - Full Width Page Template
 * Edge-to-edge content without max-width constraints
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */
?>
<article class="full-width-page">
    <?php if (!empty($page['featured_image'])): ?>
    <div class="page-hero">
        <img src="<?= htmlspecialchars($page['featured_image']) ?>" alt="<?= htmlspecialchars($page['title'] ?? '') ?>">
        <div class="page-hero-overlay">
            <h1><?= htmlspecialchars($page['title'] ?? '') ?></h1>
        </div>
    </div>
    <?php else: ?>
    <header class="page-header-wide">
        <div class="container">
            <?php if (!empty($page['title'])): ?>
            <h1><?= htmlspecialchars($page['title']) ?></h1>
            <?php endif; ?>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="page-excerpt"><?= htmlspecialchars($page['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </header>
    <?php endif; ?>

    <div class="page-content-wide">
        <div class="content-body">
            <?= $page['content'] ?? $content ?? '' ?>
        </div>
    </div>
</article>
