<?php
$statsLabel = theme_get('stats.label', 'PLATFORM METRICS');
$statsTitle = theme_get('stats.title', 'Trusted by Freelancers Worldwide');
$statsDesc = theme_get('stats.description', 'Our AI-powered banking platform processes millions of transactions monthly, helping independent professionals manage their finances with precision and confidence.');

$stat1Value = theme_get('stats.stat1_value', '$2.4B+');
$stat1Label = theme_get('stats.stat1_label', 'Total Processed');
$stat1Icon = theme_get('stats.stat1_icon', 'fa-chart-line');

$stat2Value = theme_get('stats.stat2_value', '50K+');
$stat2Label = theme_get('stats.stat2_label', 'Active Freelancers');
$stat2Icon = theme_get('stats.stat2_icon', 'fa-users');

$stat3Value = theme_get('stats.stat3_value', '98.7%');
$stat3Label = theme_get('stats.stat3_label', 'Accuracy Rate');
$stat3Icon = theme_get('stats.stat3_icon', 'fa-bullseye');

$stat4Value = theme_get('stats.stat4_value', '24/7');
$stat4Label = theme_get('stats.stat4_label', 'AI Support');
$stat4Icon = theme_get('stats.stat4_icon', 'fa-robot');

$stat5Value = theme_get('stats.stat5_value', '120+');
$stat5Label = theme_get('stats.stat5_label', 'Currencies');
$stat5Icon = theme_get('stats.stat5_icon', 'fa-globe');

$stat6Value = theme_get('stats.stat6_value', '<2s');
$stat6Label = theme_get('stats.stat6_label', 'Invoice Generation');
$stat6Icon = theme_get('stats.stat6_icon', 'fa-bolt');
?>
<section class="vbf-stats" id="stats">
    <div class="vbf-stats-bg-accent"></div>
    
    <div class="container">
        <div class="vbf-section-header" data-animate>
            <span class="vbf-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="vbf-section-divider"></div>
            <h2 class="vbf-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="vbf-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        
        <div class="vbf-stats-grid">
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat1Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat1_value"><?= esc($stat1Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat1_label"><?= esc($stat1Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
            
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat2Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat2_value"><?= esc($stat2Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat2_label"><?= esc($stat2Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
            
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat3Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat3_value"><?= esc($stat3Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat3_label"><?= esc($stat3Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
            
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat4Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat4_value"><?= esc($stat4Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat4_label"><?= esc($stat4Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
            
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat5Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat5_value"><?= esc($stat5Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat5_label"><?= esc($stat5Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
            
            <div class="vbf-stat-card" data-animate>
                <div class="vbf-stat-card-icon">
                    <i class="fas <?= esc($stat6Icon) ?>"></i>
                </div>
                <div class="vbf-stat-card-content">
                    <div class="vbf-stat-card-value" data-ts="stats.stat6_value"><?= esc($stat6Value) ?></div>
                    <div class="vbf-stat-card-label" data-ts="stats.stat6_label"><?= esc($stat6Label) ?></div>
                </div>
                <div class="vbf-stat-card-glow"></div>
            </div>
        </div>
    </div>
</section>
