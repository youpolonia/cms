<?php
$heroBadge = theme_get('hero.badge', 'AI-Powered Endpoint Protection');
$heroHeadline = theme_get('hero.headline', 'Zero-Day Threats Stop Here');
$heroSubtitle = theme_get('hero.subtitle', 'Enterprise-grade endpoint security powered by AI. Detect, prevent, and neutralize advanced threats before they breach your network.');
$heroBtnText = theme_get('hero.btn_text', 'Start Free Trial');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'See Our Work');
$heroBtn2Link = theme_get('hero.btn2_link', '/portfolio');
?>
<section class="sc-hero sc-hero--gradient" id="hero">
  <div class="sc-hero-gradient-bg"></div>
  <div class="container">
    <div class="sc-hero-content" data-animate="fade-up">
      <?php if ($heroBadge): ?><span class="sc-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
      <h1 class="sc-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
      <p class="sc-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
      <div class="sc-hero-actions">
        <a href="<?= esc($heroBtnLink) ?>" class="sc-btn sc-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
        <?php if ($heroBtn2Text): ?><a href="<?= esc($heroBtn2Link) ?>" class="sc-btn sc-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="sc-hero-wave">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
    </svg>
  </div>
</section>
