<?php
/**
 * Starter Portfolio — Work Section (Pages as Projects)
 * Editable via Theme Studio. data-ts for live preview.
 */
$pagesLabel = theme_get('pages.label', 'Projects');
$pagesTitle = theme_get('pages.title', 'Featured Work');
$pagesDesc  = theme_get('pages.description', 'Explore our pages and projects.');
?>
<!-- Pages as "Projects" -->
<?php if (!empty($pages)): ?>
<div class="section-divider"><hr></div>
<section class="section" id="projects">
    <div class="section-header">
        <div class="section-label" data-ts="pages.label"><?= esc($pagesLabel) ?></div>
        <h2 class="section-title" data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
        <p class="section-subtitle" data-ts="pages.description"><?= esc($pagesDesc) ?></p>
    </div>
    <div class="work-grid">
        <?php foreach ($pages as $p): ?>
        <a href="/page/<?= esc($p['slug']) ?>" class="work-card" style="text-decoration:none">
            <?php if (!empty($p['featured_image'])): ?>
            <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" class="work-card-image">
            <?php else: ?>
            <div style="width:100%;aspect-ratio:16/10;background:var(--color-surface);display:flex;align-items:center;justify-content:center">
                <i class="fas fa-file-alt" style="font-size:2rem;color:var(--color-border)"></i>
            </div>
            <?php endif; ?>
            <div class="work-card-content">
                <div class="work-card-title"><?= esc($p['title']) ?></div>
                <div class="work-card-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 100, '...')) ?></div>
            </div>
            <div class="work-card-arrow"><i class="fas fa-arrow-right"></i></div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
