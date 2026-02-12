<?php
/**
 * Starter Restaurant — Page Template
 * Elegant inner page with hero and breadcrumbs
 */
?>
<section class="page-hero"<?php if (!empty($page['featured_image'])): ?> style="background:url(<?= esc($page['featured_image']) ?>) center/cover no-repeat"<?php endif; ?>>
    <?php if (!empty($page['featured_image'])): ?>
    <div class="page-hero-overlay"></div>
    <?php endif; ?>
    <div class="container">
        <h1 class="page-hero-title"><?= esc($page['title']) ?></h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span><?= esc($page['title']) ?></span>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <div class="prose">
            <?= $page['content'] ?>
        </div>
    </div>
</section>
