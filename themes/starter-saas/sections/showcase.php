<?php
/**
 * Starter SaaS — Showcase Section (Pages)
 * Editable via Theme Studio. data-ts for live preview.
 */
$pagesLabel = theme_get('pages.label', 'Explore');
$pagesTitle = theme_get('pages.title', 'Our Pages');
$pagesDesc  = theme_get('pages.description', 'Discover what we have to offer.');
?>
<!-- Pages Section -->
<?php if (!empty($pages)): ?>
<section class="showcase-section" id="pages">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="features-grid" style="grid-template-columns:repeat(2, 1fr)">
            <?php foreach (array_slice($pages, 0, 2) as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:200px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="feature-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <?php endif; ?>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($p['title']) ?></h3>
                <p class="feature-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 150, '...')) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
