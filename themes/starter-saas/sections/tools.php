<?php
/**
 * Starter SaaS — Tools Section (Pages in compact grid)
 * Editable via Theme Studio. data-ts for live preview.
 */
$toolsLabel = theme_get('tools.label', 'Tools');
$toolsTitle = theme_get('tools.title', 'Powerful Tools');
$toolsDesc  = theme_get('tools.description', 'Everything you need to succeed.');
?>
<!-- Tools / Integrations -->
<?php if (!empty($pages) && count($pages) > 2): ?>
<section class="features-section" style="padding-top:0">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="tools.label"><?= esc($toolsLabel) ?></span>
            <h2 data-ts="tools.title"><?= esc($toolsTitle) ?></h2>
            <p data-ts="tools.description"><?= esc($toolsDesc) ?></p>
        </div>
        <div class="features-grid" style="grid-template-columns:repeat(3, 1fr)">
            <?php foreach (array_slice($pages, 2) as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <div class="feature-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($p['title']) ?></h3>
                <p class="feature-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 100, '...')) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
