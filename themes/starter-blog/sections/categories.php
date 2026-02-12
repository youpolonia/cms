<?php
/**
 * Starter Blog — Categories Section (Pages)
 * Shows blog-related pages as category cards
 * Editable via Theme Studio. data-ts for live preview.
 */
$pagesLabel = theme_get('pages.label', 'More');
$pagesTitle = theme_get('pages.title', 'Explore');
$pagesDesc  = theme_get('pages.description', 'Dive into our curated sections and discover something new.');

$pageIcons = [
    'destinations' => 'fas fa-map-marked-alt',
    'newsletter' => 'fas fa-envelope-open-text',
    'editorial-team' => 'fas fa-users',
    'opinion' => 'fas fa-comment-dots',
    'subscribe' => 'fas fa-bell',
];
?>
<!-- Pages as Categories -->
<?php if (!empty($pages)): ?>
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="categories-grid">
            <?php foreach ($pages as $p): 
                $icon = $pageIcons[$p['slug']] ?? 'fas fa-file-alt';
            ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="category-card">
                <div class="cat-icon"><i class="<?= $icon ?>"></i></div>
                <h3><?= esc($p['title']) ?></h3>
                <span class="cat-count"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 60, '...')) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
