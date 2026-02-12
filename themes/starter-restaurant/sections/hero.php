<?php
/**
 * Starter Restaurant — Hero Section
 * Premium cinematic hero with ornamental divider
 * Variables inherited from parent scope
 */
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image"<?php if ($heroBgImage): ?> style="background: url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?>></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" data-animate>
        <div class="hero-badge" data-ts="hero.badge"><i class="fas fa-utensils"></i> <?= esc($heroBadge) ?></div>
        <div class="hero-ornament"><i class="fas fa-diamond"></i></div>
        <h1 class="hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
            <a href="#menu" class="btn btn-ghost"><i class="fas fa-book-open"></i> View Menu</a>
        </div>
    </div>
    <div class="hero-scroll">
        <span>Scroll</span>
        <div class="hero-scroll-line"></div>
    </div>
</section>
