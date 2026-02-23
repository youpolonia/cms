<?php
$statsLabel = theme_get('stats.label', 'PROVEN RESULTS');
$statsTitle = theme_get('stats.title', 'Security Metrics That Matter');
$statsDesc = theme_get('stats.description', 'Real-world performance data from our enterprise deployments across financial services, healthcare, and government sectors.');
?>
<section class="ss-stats-section">
    <div class="container">
        <div class="ss-section-header" data-animate>
            <span class="ss-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="ss-section-divider"></div>
            <h2 class="ss-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="ss-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="ss-stats-grid">
            <div class="ss-stat-block" data-animate>
                <div class="ss-stat-visual">
                    <div class="ss-stat-circle">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <div class="ss-stat-content">
                    <span class="ss-stat-number">99.97%</span>
                    <span class="ss-stat-text">Attack Prevention Rate</span>
                    <p class="ss-stat-detail">Zero successful breaches across all protected accounts since deployment</p>
                </div>
            </div>
            <div class="ss-stat-block" data-animate>
                <div class="ss-stat-visual">
                    <div class="ss-stat-circle">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="ss-stat-content">
                    <span class="ss-stat-number">68%</span>
                    <span class="ss-stat-text">Faster Login Times</span>
                    <p class="ss-stat-detail">Biometric authentication eliminates password friction and reduces support tickets</p>
                </div>
            </div>
            <div class="ss-stat-block" data-animate>
                <div class="ss-stat-visual">
                    <div class="ss-stat-circle">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="ss-stat-content">
                    <span class="ss-stat-number">$2.4M</span>
                    <span class="ss-stat-text">Avg. Annual Savings</span>
                    <p class="ss-stat-detail">Reduced IT overhead, eliminated password resets, prevented breach costs</p>
                </div>
            </div>
            <div class="ss-stat-block" data-animate>
                <div class="ss-stat-visual">
                    <div class="ss-stat-circle">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <div class="ss-stat-content">
                    <span class="ss-stat-number">100%</span>
                    <span class="ss-stat-text">Compliance Coverage</span>
                    <p class="ss-stat-detail">SOC 2, GDPR, HIPAA, PCI-DSS audit trails generated automatically</p>
                </div>
            </div>
        </div>
        <div class="ss-stats-footer" data-animate>
            <div class="ss-compliance-badges">
                <div class="ss-compliance-item">
                    <i class="fas fa-certificate"></i>
                    <span>SOC 2 Type II</span>
                </div>
                <div class="ss-compliance-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>ISO 27001</span>
                </div>
                <div class="ss-compliance-item">
                    <i class="fas fa-hospital"></i>
                    <span>HIPAA</span>
                </div>
                <div class="ss-compliance-item">
                    <i class="fas fa-credit-card"></i>
                    <span>PCI-DSS</span>
                </div>
                <div class="ss-compliance-item">
                    <i class="fas fa-globe-europe"></i>
                    <span>GDPR</span>
                </div>
            </div>
        </div>
    </div>
</section>
