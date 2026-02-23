<?php
$aboutBadge = theme_get('about.badge', '');
$aboutTitle = theme_get('about.title', 'About Chiaroscuro Studio');
$aboutSubtitle = theme_get('about.subtitle', 'We are a passionate team dedicated to delivering excellence in everything we do.');
$aboutText = theme_get('about.text', 'Founded with a vision to make a difference, we have grown from a small team into a trusted name in our industry. Our commitment to quality, innovation, and customer satisfaction drives everything we do. We believe in building lasting relationships and delivering results that exceed expectations.');
$aboutBtnText = theme_get('about.btn_text', 'Learn More');
$aboutBtnLink = theme_get('about.btn_link', '/about');
?>
<section class="cp-about cp-about--minimal-centered" id="about">
  <div class="container">
    <div class="cp-about-content cp-about-content--centered" data-animate="fade-up">
      <?php if ($aboutBadge): ?><span class="cp-about-badge" data-ts="about.badge"><?= esc($aboutBadge) ?></span><?php endif; ?>
      <h2 class="cp-about-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
      <p class="cp-about-subtitle" data-ts="about.subtitle"><?= esc($aboutSubtitle) ?></p>
      <div class="cp-about-divider"></div>
      <p class="cp-about-text" data-ts="about.text"><?= esc($aboutText) ?></p>
      <div class="cp-about-actions">
        <a href="<?= esc($aboutBtnLink) ?>" class="cp-btn cp-btn-primary" data-ts="about.btn_text" data-ts-href="about.btn_link"><?= esc($aboutBtnText) ?></a>
      </div>
    </div>
  </div>
</section>
