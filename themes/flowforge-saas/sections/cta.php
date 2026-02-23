<?php
$ctaBadge = theme_get('cta.badge', '');
$ctaTitle = theme_get('cta.title', 'Ready to Work with FlowForge?');
$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="fs-cta fs-cta--banner-gradient" id="cta">
  <div class="fs-cta-gradient-bg"></div>
  <div class="container">
    <div class="fs-cta-content" data-animate="fade-up">
      <?php if ($ctaBadge): ?><span class="fs-cta-badge" data-ts="cta.badge"><?= esc($ctaBadge) ?></span><?php endif; ?>
      <h2 class="fs-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
      <p class="fs-cta-subtitle" data-ts="cta.subtitle"><?= esc($ctaSubtitle) ?></p>
      <div class="fs-cta-actions">
        <a href="<?= esc($ctaBtnLink) ?>" class="fs-btn fs-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc($ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
