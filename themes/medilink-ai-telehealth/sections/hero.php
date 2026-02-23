<?php
$heroBadge = theme_get('hero.badge', '');
$heroHeadline = theme_get('hero.headline', 'MediLink AI');
$heroSubtitle = theme_get('hero.subtitle', 'AI-powered telehealth platform connecting patients with specialists instantly');
$heroBtnText = theme_get('hero.btn_text', 'Get Started');
$heroBtnLink = theme_get('hero.btn_link', '/contact');
$heroBtn2Text = theme_get('hero.btn2_text', 'See Our Work');
$heroBtn2Link = theme_get('hero.btn2_link', '/portfolio');
?>
<section class="mat-hero mat-hero--gradient" id="hero">
  <div class="mat-hero-gradient-bg"></div>
  <div class="container">
    <div class="mat-hero-content" data-animate="fade-up">
      <?php if ($heroBadge): ?><span class="mat-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span><?php endif; ?>
      <h1 class="mat-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
      <p class="mat-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
      <div class="mat-hero-actions">
        <a href="<?= esc($heroBtnLink) ?>" class="mat-btn mat-btn-primary" data-ts="hero.btn_text" data-ts-href="hero.btn_link"><?= esc($heroBtnText) ?></a>
        <?php if ($heroBtn2Text): ?><a href="<?= esc($heroBtn2Link) ?>" class="mat-btn mat-btn-outline" data-ts="hero.btn2_text" data-ts-href="hero.btn2_link"><?= esc($heroBtn2Text) ?></a><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="mat-hero-wave">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
      <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V120H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
    </svg>
  </div>
</section>
