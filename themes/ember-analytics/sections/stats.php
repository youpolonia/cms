<?php
$statsLabel = theme_get('stats.label', 'By The Numbers');
$statsTitle = theme_get('stats.title', 'Trusted by Growing E-Commerce Brands');
$statsDesc = theme_get('stats.description', 'Our platform processes millions of data points daily to deliver insights that matter.');
$stat1Value = theme_get('stats.stat1_value', '500M+');
$stat1Label = theme_get('stats.stat1_label', 'Data Points Processed');
$stat2Value = theme_get('stats.stat2_value', '12,000+');
$stat2Label = theme_get('stats.stat2_label', 'Active Stores');
$stat3Value = theme_get('stats.stat3_value', '47%');
$stat3Label = theme_get('stats.stat3_label', 'Average Revenue Lift');
$stat4Value = theme_get('stats.stat4_value', '99.9%');
$stat4Label = theme_get('stats.stat4_label', 'Uptime Guarantee');
?>
<section class="ea-stats" id="stats">
    <div class="ea-stats-container">
        <div class="ea-stats-header" data-animate>
            <span class="ea-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <h2 class="ea-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="ea-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="ea-stats-grid">
            <div class="ea-stat-card" data-animate>
                <div class="ea-stat-icon">
                    <i class="fas fa-database"></i>
                </div>
                <div class="ea-stat-value" data-ts="stats.stat1_value"><?= esc($stat1Value) ?></div>
                <div class="ea-stat-label" data-ts="stats.stat1_label"><?= esc($stat1Label) ?></div>
            </div>
            <div class="ea-stat-card" data-animate>
                <div class="ea-stat-icon">
                    <i class="fas fa-store"></i>
                </div>
                <div class="ea-stat-value" data-ts="stats.stat2_value"><?= esc($stat2Value) ?></div>
                <div class="ea-stat-label" data-ts="stats.stat2_label"><?= esc($stat2Label) ?></div>
            </div>
            <div class="ea-stat-card" data-animate>
                <div class="ea-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="ea-stat-value" data-ts="stats.stat3_value"><?= esc($stat3Value) ?></div>
                <div class="ea-stat-label" data-ts="stats.stat3_label"><?= esc($stat3Label) ?></div>
            </div>
            <div class="ea-stat-card" data-animate>
                <div class="ea-stat-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="ea-stat-value" data-ts="stats.stat4_value"><?= esc($stat4Value) ?></div>
                <div class="ea-stat-label" data-ts="stats.stat4_label"><?= esc($stat4Label) ?></div>
            </div>
        </div>
    </div>
</section>
