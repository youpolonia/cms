<?php
/**
 * Starter SaaS — Showcase Section (Pages as product areas)
 * Shows pages as product feature areas with images
 * Editable via Theme Studio. data-ts for live preview.
 */
$pagesLabel = theme_get('pages.label', 'Product');
$pagesTitle = theme_get('pages.title', 'Explore AppFlow');
$pagesDesc  = theme_get('pages.description', 'Dive deeper into what makes AppFlow the choice of 10,000+ teams worldwide.');

$pageIcons = [
    'features' => 'fas fa-rocket',
    'pricing' => 'fas fa-tags',
    'blog' => 'fas fa-rss',
    'resources' => 'fas fa-book',
    'faq' => 'fas fa-question-circle',
];
?>
<!-- Showcase (Pages) -->
<?php if (!empty($pages)): ?>
<section class="showcase-section" id="pages">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="features-grid" style="grid-template-columns:repeat(auto-fit, minmax(280px, 1fr))">
            <?php foreach ($pages as $p): 
                $icon = $pageIcons[$p['slug']] ?? 'fas fa-file-alt';
            ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="feature-card glass-card" style="text-decoration:none">
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-32px -28px 20px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="feature-icon"><i class="<?= $icon ?>"></i></div>
                <?php endif; ?>
                <h3 class="feature-title" style="color:#f8fafc"><?= esc($p['title']) ?></h3>
                <p class="feature-desc"><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...')) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
