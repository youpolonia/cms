<?php
$statsTitle = theme_get('stats.title', 'MediLink AI in Numbers');
$statsSubtitle = theme_get('stats.subtitle', 'Numbers that speak for themselves');
$statsBadge = theme_get('stats.badge', '');
$stat1Num = theme_get('stats.stat1_number', '500+');
$stat1Lab = theme_get('stats.stat1_label', 'Clients');
$stat2Num = theme_get('stats.stat2_number', '1200+');
$stat2Lab = theme_get('stats.stat2_label', 'Projects');
$stat3Num = theme_get('stats.stat3_number', '98%');
$stat3Lab = theme_get('stats.stat3_label', 'Satisfaction');
$stat4Num = theme_get('stats.stat4_number', '25+');
$stat4Lab = theme_get('stats.stat4_label', 'Years');
?>
<section class="mat-stats mat-stats--counters-row" id="stats">
  <div class="container">
    <div class="mat-stats-header" data-animate="fade-up">
      <?php if ($statsBadge): ?><span class="mat-stats-badge" data-ts="stats.badge"><?= esc($statsBadge) ?></span><?php endif; ?>
      <h2 class="mat-stats-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
      <p class="mat-stats-subtitle" data-ts="stats.subtitle"><?= esc($statsSubtitle) ?></p>
    </div>
    <div class="mat-stats-grid" data-animate="fade-up">
      <div class="mat-stats-item">
        <span class="mat-stats-number" data-ts="stats.stat1_number"><?= esc($stat1Num) ?></span>
        <span class="mat-stats-label" data-ts="stats.stat1_label"><?= esc($stat1Lab) ?></span>
      </div>
      <div class="mat-stats-item">
        <span class="mat-stats-number" data-ts="stats.stat2_number"><?= esc($stat2Num) ?></span>
        <span class="mat-stats-label" data-ts="stats.stat2_label"><?= esc($stat2Lab) ?></span>
      </div>
      <div class="mat-stats-item">
        <span class="mat-stats-number" data-ts="stats.stat3_number"><?= esc($stat3Num) ?></span>
        <span class="mat-stats-label" data-ts="stats.stat3_label"><?= esc($stat3Lab) ?></span>
      </div>
      <div class="mat-stats-item">
        <span class="mat-stats-number" data-ts="stats.stat4_number"><?= esc($stat4Num) ?></span>
        <span class="mat-stats-label" data-ts="stats.stat4_label"><?= esc($stat4Lab) ?></span>
      </div>
    </div>
  </div>
</section>
