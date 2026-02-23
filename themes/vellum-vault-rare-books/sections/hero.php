<?php
$heroBadge = theme_get('hero.badge', '');
$heroHeadline = theme_get('hero.headline', 'Vellum & Vault');
$heroSubtitle = theme_get('hero.subtitle', 'A luxurious digital gallery for antiquarian books, first editions, and rare manuscripts.');
$heroBtnText = theme_get('hero.btn_text', 'Get Started');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
$heroBtn2Link = theme_get('hero.btn2_link', '#features');
$heroImage = theme_get('hero.image', '');
?>
<section class="vvr-hero vvr-hero--split vvr-hero--cards" id="hero">
  <div class="container">
    <div class="vvr-hero-grid">
      <div class="vvr-hero-content" data-animate="fade-right">
        <?php if ($heroBadge): ?><span class="vvr-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
        <h1 class="vvr-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="vvr-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="vvr-hero-actions">
          <a href="<?= esc($heroBtnLink) ?>" class="vvr-btn vvr-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
          <?php if ($heroBtn2Text): ?><a href="<?= esc($heroBtn2Link) ?>" class="vvr-btn vvr-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
      <div class="vvr-hero-visual" data-animate="fade-left">
        <img src="<?= esc($heroImage) ?>" alt="<?= esc($heroHeadline) ?>" class="vvr-hero-image" data-ts-bg="hero.image" loading="eager">
        <div class="vvr-hero-float-card vvr-hero-float-1">
          <i class="fas fa-shield-alt"></i>
          <span>Trusted by 1000+</span>
        </div>
        <div class="vvr-hero-float-card vvr-hero-float-2">
          <i class="fas fa-star"></i>
          <span>5-Star Rated</span>
        </div>
      </div>
    </div>
  </div>
</section>
