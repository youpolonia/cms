<?php
$statsLabel = theme_get('stats.label', 'Performance Metrics');
$statsTitle = theme_get('stats.title', 'Trusted by WordPress Professionals Worldwide');
$statsDesc = theme_get('stats.description', 'Our infrastructure powers thousands of high-traffic WordPress sites with industry-leading performance and reliability.');

$stat1Number = theme_get('stats.stat1_number', '50K+');
$stat1Label = theme_get('stats.stat1_label', 'Sites Hosted');
$stat1Icon = theme_get('stats.stat1_icon', 'fa-globe');

$stat2Number = theme_get('stats.stat2_number', '99.99%');
$stat2Label = theme_get('stats.stat2_label', 'Uptime SLA');
$stat2Icon = theme_get('stats.stat2_icon', 'fa-clock');

$stat3Number = theme_get('stats.stat3_number', '<200ms');
$stat3Label = theme_get('stats.stat3_label', 'Avg Response Time');
$stat3Icon = theme_get('stats.stat3_icon', 'fa-bolt');

$stat4Number = theme_get('stats.stat4_number', '24/7');
$stat4Label = theme_get('stats.stat4_label', 'Expert Support');
$stat4Icon = theme_get('stats.stat4_icon', 'fa-headset');
?>
<section class="vp-section vp-stats-section" id="stats">
    <div class="container">
        <div class="vp-section-header" data-animate>
            <span class="vp-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="vp-section-divider"></div>
            <h2 class="vp-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="vp-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        
        <div class="vp-stats-grid">
            <div class="vp-stat-card" data-animate>
                <div class="vp-stat-icon">
                    <i class="fas <?= esc($stat1Icon) ?>" data-ts="stats.stat1_icon"></i>
                </div>
                <div class="vp-stat-content">
                    <div class="vp-stat-number" data-ts="stats.stat1_number"><?= esc($stat1Number) ?></div>
                    <div class="vp-stat-label" data-ts="stats.stat1_label"><?= esc($stat1Label) ?></div>
                </div>
            </div>
            
            <div class="vp-stat-card" data-animate>
                <div class="vp-stat-icon">
                    <i class="fas <?= esc($stat2Icon) ?>" data-ts="stats.stat2_icon"></i>
                </div>
                <div class="vp-stat-content">
                    <div class="vp-stat-number" data-ts="stats.stat2_number"><?= esc($stat2Number) ?></div>
                    <div class="vp-stat-label" data-ts="stats.stat2_label"><?= esc($stat2Label) ?></div>
                </div>
            </div>
            
            <div class="vp-stat-card" data-animate>
                <div class="vp-stat-icon">
                    <i class="fas <?= esc($stat3Icon) ?>" data-ts="stats.stat3_icon"></i>
                </div>
                <div class="vp-stat-content">
                    <div class="vp-stat-number" data-ts="stats.stat3_number"><?= esc($stat3Number) ?></div>
                    <div class="vp-stat-label" data-ts="stats.stat3_label"><?= esc($stat3Label) ?></div>
                </div>
            </div>
            
            <div class="vp-stat-card" data-animate>
                <div class="vp-stat-icon">
                    <i class="fas <?= esc($stat4Icon) ?>" data-ts="stats.stat4_icon"></i>
                </div>
                <div class="vp-stat-content">
                    <div class="vp-stat-number" data-ts="stats.stat4_number"><?= esc($stat4Number) ?></div>
                    <div class="vp-stat-label" data-ts="stats.stat4_label"><?= esc($stat4Label) ?></div>
                </div>
            </div>
        </div>
    </div>
</section>
