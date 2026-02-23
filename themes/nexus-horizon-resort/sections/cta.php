<?php
$ctaBadge = theme_get('cta.badge', '');
$ctaTitle = theme_get('cta.title', 'Ready to Work with Nexus Horizon?');
$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="nhr-cta nhr-cta--banner-centered" id="cta">
  <div class="container">
    <div class="nhr-cta-content" data-animate="fade-up">
      <?php if ($ctaBadge): ?><span class="nhr-cta-badge" data-ts="cta.badge"><?= esc($ctaBadge) ?></span><?php endif; ?>
      <h2 class="nhr-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
      <p class="nhr-cta-subtitle" data-ts="cta.subtitle"><?= esc($ctaSubtitle) ?></p>
      <div class="nhr-cta-actions">
        <a href="<?= esc($ctaBtnLink) ?>" class="nhr-btn nhr-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc($ctaBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
