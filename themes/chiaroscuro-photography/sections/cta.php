<?php
$ctaBgImage = theme_get('cta.bg_image', '');
$ctaBadge = theme_get('cta.badge', '');
$ctaTitle = theme_get('cta.title', 'Ready to Work with Chiaroscuro Studio?');
$ctaSubtitle = theme_get('cta.subtitle', 'Join thousands of satisfied customers. Take the first step today.');
$ctaBtnText = theme_get('cta.btn_text', 'Get Started');
$ctaBtnLink = theme_get('cta.btn_link', '/contact');
?>
<section class="cp-cta cp-cta--creative-glass" id="cta">
  <div class="cp-cta-glass-bg" style="background-image: url('<?= esc($ctaBgImage) ?>');" data-ts-bg="cta.bg_image"></div>
  <div class="container">
    <div class="cp-cta-glass-card" data-animate="fade-up">
      <div class="cp-cta-content">
        <?php if ($ctaBadge): ?><span class="cp-cta-badge" data-ts="cta.badge"><?= esc($ctaBadge) ?></span><?php endif; ?>
        <h2 class="cp-cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
        <p class="cp-cta-subtitle" data-ts="cta.subtitle"><?= esc($ctaSubtitle) ?></p>
        <div class="cp-cta-actions">
          <a href="<?= esc($ctaBtnLink) ?>" class="cp-btn cp-btn-primary" data-ts="cta.btn_text" data-ts-href="cta.btn_link"><?= esc($ctaBtnText) ?></a>
        </div>
      </div>
    </div>
  </div>
</section>
