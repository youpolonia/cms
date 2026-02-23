<?php
$statsLabel = theme_get('stats.label', 'By The Numbers');
$statsTitle = theme_get('stats.title', 'Proven Cybersecurity Excellence');
$statsDesc = theme_get('stats.description', 'Our track record speaks for itself. Trusted by enterprises worldwide to protect their most critical assets.');

$stat1Value = theme_get('stats.stat1_value', '500M+');
$stat1Label = theme_get('stats.stat1_label', 'Threats Blocked Daily');
$stat2Value = theme_get('stats.stat2_value', '99.97%');
$stat2Label = theme_get('stats.stat2_label', 'Detection Accuracy');
$stat3Value = theme_get('stats.stat3_value', '<15min');
$stat3Label = theme_get('stats.stat3_label', 'Avg Response Time');
$stat4Value = theme_get('stats.stat4_value', '2,500+');
$stat4Label = theme_get('stats.stat4_label', 'Enterprise Clients');
?>
<section class="csh-stats-section" id="stats">
    <div class="container">
        <div class="csh-section-header" data-animate>
            <span class="csh-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="csh-section-divider"></div>
            <h2 class="csh-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="csh-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="csh-stats-grid">
            <div class="csh-stat-card" data-animate>
                <div class="csh-stat-icon">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <div class="csh-stat-value" data-ts="stats.stat1_value"><?= esc($stat1Value) ?></div>
                <div class="csh-stat-label" data-ts="stats.stat1_label"><?= esc($stat1Label) ?></div>
                <div class="csh-stat-bar"></div>
            </div>
            <div class="csh-stat-card" data-animate>
                <div class="csh-stat-icon">
                    <i class="fas fa-crosshairs"></i>
                </div>
                <div class="csh-stat-value" data-ts="stats.stat2_value"><?= esc($stat2Value) ?></div>
                <div class="csh-stat-label" data-ts="stats.stat2_label"><?= esc($stat2Label) ?></div>
                <div class="csh-stat-bar"></div>
            </div>
            <div class="csh-stat-card" data-animate>
                <div class="csh-stat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="csh-stat-value" data-ts="stats.stat3_value"><?= esc($stat3Value) ?></div>
                <div class="csh-stat-label" data-ts="stats.stat3_label"><?= esc($stat3Label) ?></div>
                <div class="csh-stat-bar"></div>
            </div>
            <div class="csh-stat-card" data-animate>
                <div class="csh-stat-icon">
                    <i class="fas fa-building"></i>
                </div>
                <div class="csh-stat-value" data-ts="stats.stat4_value"><?= esc($stat4Value) ?></div>
                <div class="csh-stat-label" data-ts="stats.stat4_label"><?= esc($stat4Label) ?></div>
                <div class="csh-stat-bar"></div>
            </div>
        </div>
    </div>
</section>
