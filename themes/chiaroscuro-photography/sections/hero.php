<?php
$heroBadge = theme_get('hero.badge', '');
$heroHeadline = theme_get('hero.headline', 'Chiaroscuro Studio');
$heroSubtitle = theme_get('hero.subtitle', 'Award-winning photography studio specializing in weddings, portraits, events, and commercial shoots.');
$heroBtnText = theme_get('hero.btn_text', 'Explore');
$heroBtnLink = theme_get('hero.btn_link', '/portfolio');
?>
<section class="cp-hero cp-hero--minimal" id="hero">
  <div class="container">
    <div class="cp-hero-content" data-animate="fade-up">
      <?php if ($heroBadge): ?><span class="cp-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
      <h1 class="cp-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
      <p class="cp-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
      <div class="cp-hero-actions">
        <a href="<?= esc($heroBtnLink) ?>" class="cp-btn cp-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
