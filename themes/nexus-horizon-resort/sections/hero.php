<?php
$heroBadge = theme_get('hero.badge', '');
$heroHeadline = theme_get('hero.headline', 'Nexus Horizon');
$heroSubtitle = theme_get('hero.subtitle', 'Futuristic urban resort with smart luxury and immersive experiences');
$heroBtnText = theme_get('hero.btn_text', 'Get Started');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'Learn More');
$heroBtn2Link = theme_get('hero.btn2_link', '#about');
$heroImage = theme_get('hero.image', '');
?>
<section class="nhr-hero nhr-hero--split nhr-hero--reverse" id="hero">
  <div class="container">
    <div class="nhr-hero-grid">
      <div class="nhr-hero-visual" data-animate="fade-right">
        <img src="<?= esc($heroImage) ?>" alt="<?= esc($heroHeadline) ?>" class="nhr-hero-image" data-ts-bg="hero.image" loading="eager">
      </div>
      <div class="nhr-hero-content" data-animate="fade-left">
        <?php if ($heroBadge): ?><span class="nhr-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
        <h1 class="nhr-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
        <p class="nhr-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
        <div class="nhr-hero-actions">
          <a href="<?= esc($heroBtnLink) ?>" class="nhr-btn nhr-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
          <?php if ($heroBtn2Text): ?><a href="<?= esc($heroBtn2Link) ?>" class="nhr-btn nhr-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
