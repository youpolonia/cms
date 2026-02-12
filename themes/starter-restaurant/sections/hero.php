<?php
/**
 * Starter Restaurant — Hero Section
 * Variables inherited from parent scope: $heroHeadline, $heroSubtitle, $heroBtnText, $heroBtnLink, $heroBgImage, $heroBadge
 */
?>
<!-- Hero Section -->
<section class="hero">
    <div class="hero-bg" data-ts-bg="hero.bg_image"<?php if ($heroBgImage): ?> style="background: url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?>></div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-badge"><i class="fas fa-utensils"></i> <?= esc($heroBadge) ?></div>
        <h1 class="hero-title" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="hero-actions">
            <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
            <a href="#menu" class="btn btn-outline"><i class="fas fa-book-open"></i> View Menu</a>
        </div>
    </div>
</section>
