<?php
/**
 * Starter Blog — Categories Section (Pages)
 * Editable via Theme Studio. data-ts for live preview.
 */
$pagesLabel = theme_get('pages.label', 'Explore');
$pagesTitle = theme_get('pages.title', 'Our Pages');
$pagesDesc  = theme_get('pages.description', 'Discover more content across our site.');
?>
<!-- Pages Section -->
<?php if (!empty($pages)): ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="categories-grid">
            <?php $pageIcons = ['fas fa-file-alt', 'fas fa-info-circle', 'fas fa-bookmark', 'fas fa-star', 'fas fa-heart', 'fas fa-folder']; ?>
            <?php foreach ($pages as $i => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="category-card">
                <div class="cat-icon">
                    <i class="<?= $pageIcons[$i % count($pageIcons)] ?>"></i>
                </div>
                <h3><?= esc($p['title']) ?></h3>
                <span class="cat-count"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 50, '...')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
