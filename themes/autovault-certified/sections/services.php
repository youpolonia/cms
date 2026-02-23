<?php
$fBadge = theme_get('features.badge', '');
$fTitle = theme_get('features.title', 'Why Choose AutoVault Certified');
$fSubtitle = theme_get('features.subtitle', 'Everything you need to succeed, all in one place.');
$features = [];
for ($i = 1; $i <= 6; $i++) {
    $_fIcon = theme_get("features.item{$i}_icon", 'fas fa-star');
    $_fTitle = theme_get("features.item{$i}_title", 'Feature ' . ['One','Two','Three','Four','Five','Six'][$i-1]);
    $_fText = theme_get("features.item{$i}_text", 'Description of this amazing feature and how it helps you.');
    if ($_fTitle) $features[] = ['icon'=>$_fIcon, 'title'=>$_fTitle, 'text'=>$_fText];
}
unset($_fIcon, $_fTitle, $_fText);
?>
<section class="ac-features ac-features--grid-3col" id="features">
  <div class="container">
    <div class="ac-features-header" data-animate="fade-up">
      <?php if ($fBadge): ?><span class="ac-features-badge" data-ts="features.badge"><?= esc($fBadge) ?></span><?php endif; ?>
      <h2 class="ac-features-title" data-ts="features.title"><?= esc($fTitle) ?></h2>
      <p class="ac-features-subtitle" data-ts="features.subtitle"><?= esc($fSubtitle) ?></p>
    </div>
    <div class="ac-features-grid" data-animate="fade-up" data-animate-stagger>
      <?php foreach ($features as $idx => $f): ?>
      <div class="ac-feature-item">
        <div class="ac-feature-icon"><i class="<?= esc($f['icon']) ?>" data-ts="features.item<?= $idx+1 ?>_icon"></i></div>
        <h3 class="ac-feature-item-title" data-ts="features.item<?= $idx+1 ?>_title"><?= esc($f['title']) ?></h3>
        <p class="ac-feature-item-text" data-ts="features.item<?= $idx+1 ?>_text"><?= esc($f['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
