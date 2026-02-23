<?php
$galBadge = theme_get('gallery.badge', '');
$galTitle = theme_get('gallery.title', 'Our Gallery');
$galSubtitle = theme_get('gallery.subtitle', 'Browse through our collection of work and projects.');
?>
<section class="cp-gallery cp-gallery--grid-3col" id="gallery">
  <div class="container">
    <div class="cp-gallery-header" data-animate="fade-up">
      <?php if ($galBadge): ?><span class="cp-gallery-badge" data-ts="gallery.badge"><?= esc($galBadge) ?></span><?php endif; ?>
      <h2 class="cp-gallery-title" data-ts="gallery.title"><?= esc($galTitle) ?></h2>
      <p class="cp-gallery-subtitle" data-ts="gallery.subtitle"><?= esc($galSubtitle) ?></p>
    </div>
    <div class="cp-gallery-grid" data-animate="fade-up">
      <?php for ($i = 1; $i <= 6; $i++): ?>
        <?php $img = theme_get("gallery.image{$i}", ''); $cap = theme_get("gallery.caption{$i}", ''); ?>
        <?php if ($img): ?>
        <div class="cp-gallery-item">
          <div class="cp-gallery-item-inner">
            <img src="<?= esc($img) ?>" alt="<?= esc($cap) ?>" class="cp-gallery-img" loading="lazy" data-ts-bg="gallery.image<?= $i ?>">
            <?php if ($cap): ?><div class="cp-gallery-caption" data-ts="gallery.caption<?= $i ?>"><?= esc($cap) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  </div>
</section>
