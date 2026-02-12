<?php
/**
 * Starter Business — Services Section (Pages)
 * Editable via Theme Studio. data-ts for live preview.
 */
$servicesLabel = theme_get('services.label', 'What We Offer');
$servicesTitle = theme_get('services.title', 'Our Services');
$servicesDesc  = theme_get('services.description', 'Explore what we have to offer across all our pages.');
?>
<!-- Pages as "Services" -->
<?php if (!empty($pages)): ?>
<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header">
            <span class="section-badge" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <?php $icons = ['fas fa-cogs', 'fas fa-chart-line', 'fas fa-shield-alt', 'fas fa-lightbulb', 'fas fa-users', 'fas fa-globe']; ?>
            <?php foreach ($pages as $i => $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="service-card fade-in-up" style="text-decoration:none">
                <?php if (!empty($p['featured_image'])): ?>
                <div style="margin:-40px -32px 24px;border-radius:16px 16px 0 0;overflow:hidden;height:180px">
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                </div>
                <?php else: ?>
                <div class="service-icon">
                    <i class="<?= $icons[$i % count($icons)] ?>"></i>
                </div>
                <?php endif; ?>
                <h3 class="service-title"><?= esc($p['title']) ?></h3>
                <p class="service-desc"><?= esc(mb_strimwidth(strip_tags($p["content"]), 0, 160, '...')) ?></p>
                <span class="service-link">Learn more <i class="fas fa-arrow-right"></i></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
