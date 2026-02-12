<?php
/**
 * Starter Blog — Hero Section
 * Editable via Theme Studio. data-ts for live preview.
 */
$heroHeadline = theme_get('hero.headline', get_site_name());
$heroSubtitle = theme_get('hero.subtitle', get_setting('hero_subtitle') ?: 'Stories, thoughts, and ideas worth sharing.');
$heroBtnText  = theme_get('hero.btn_text', 'Browse Articles');
$heroBtnLink  = theme_get('hero.btn_link', '/articles');
$heroBgImage  = theme_get('hero.bg_image');
?>
<!-- Blog Hero -->
<section class="blog-hero"<?php if ($heroBgImage): ?> style="background:url(<?= esc($heroBgImage) ?>) center/cover no-repeat"<?php endif; ?> data-ts-bg="hero.bg_image">
    <h1 data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
    <p class="hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
    <div class="hero-actions">
        <a href="<?= esc($heroBtnLink) ?>" class="btn btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?> <i class="fas fa-arrow-right"></i></a>
        <a href="#latest" class="btn btn-outline">Latest Posts</a>
    </div>
</section>
