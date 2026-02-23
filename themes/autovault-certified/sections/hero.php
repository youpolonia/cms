<?php
$heroBadge = theme_get('hero.badge', '');
$heroHeadline = theme_get('hero.headline', 'AutoVault Certified');
$heroSubtitle = theme_get('hero.subtitle', 'Premium used car dealership with inspected, certified vehicles and comprehensive services.');
$heroBtnText = theme_get('hero.btn_text', 'Get Started');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
$heroBtn2Link = theme_get('hero.btn2_link', '#features');
$heroImage = theme_get('hero.image', '');
?>
<section class="ac-hero ac-hero--split" id="hero">
  <div class="container">
    <div class="ac-hero-grid">
      <div class="ac-hero-content" data-animate="fade-right">
        <?php if ($heroBadge): ?><span class="ac-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
        <h1 class="ac-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="ac-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="ac-hero-actions">
          <a href="<?= esc($heroBtnLink) ?>" class="ac-btn ac-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
          <?php if ($heroBtn2Text): ?><a href="<?= esc($heroBtn2Link) ?>" class="ac-btn ac-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
      <div class="ac-hero-visual" data-animate="fade-left">
        <img src="<?= esc($heroImage) ?>" alt="<?= esc($heroHeadline) ?>" class="ac-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
    </div>
  </div>
</section>
