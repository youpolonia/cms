<?php
$ctaBadge = theme_get('cta.badge', '');
$ctaTitle = theme_get('cta.title', 'Ready to Work with MediLink AI?');
$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="mat-cta mat-cta--split-card" id="cta">
  <div class="container">
    <div class="mat-cta-card" data-animate="fade-up">
      <div class="mat-cta-card-inner">
        <div class="mat-cta-content">
          <?php if ($ctaBadge): ?><span class="mat-cta-badge" data-ts="cta.badge"><?= esc($ctaBadge) ?></span><?php endif; ?>
          <h2 class="mat-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
          <p class="mat-cta-subtitle" data-ts="cta.subtitle"><?= esc($ctaSubtitle) ?></p>
        </div>
        <div class="mat-cta-action-col">
          <a href="<?= esc($ctaBtnLink) ?>" class="mat-btn mat-btn-primary mat-btn-lg" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc($ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
