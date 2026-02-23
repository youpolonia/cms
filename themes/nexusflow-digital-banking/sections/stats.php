<?php
$statsLabel = theme_get('stats.label', 'TRUSTED BY FREELANCERS WORLDWIDE');
$statsTitle = theme_get('stats.title', 'Numbers That Speak Volumes');
$statsDesc = theme_get('stats.description', 'Join thousands of independent professionals who have transformed their financial workflow with NexusFlow.');
?>
<section class="nf-stats-section" id="stats">
    <div class="container">
        <div class="nf-section-header" data-animate>
            <span class="nf-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="nf-section-divider"></div>
            <h2 class="nf-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="nf-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="nf-stats-grid">
            <div class="nf-stat-card" data-animate>
                <div class="nf-stat-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="nf-stat-content">
                    <span class="nf-stat-number" data-count="85000">85,000+</span>
                    <span class="nf-stat-label">Active Freelancers</span>
                </div>
            </div>
            <div class="nf-stat-card" data-animate>
                <div class="nf-stat-icon">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <div class="nf-stat-content">
                    <span class="nf-stat-number" data-count="120">120+</span>
                    <span class="nf-stat-label">Countries Supported</span>
                </div>
            </div>
            <div class="nf-stat-card" data-animate>
                <div class="nf-stat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="nf-stat-content">
                    <span class="nf-stat-number" data-count="98">98%</span>
                    <span class="nf-stat-label">Faster Invoicing</span>
                </div>
            </div>
            <div class="nf-stat-card" data-animate>
                <div class="nf-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="nf-stat-content">
                    <span class="nf-stat-number" data-count="4.9">4.9/5</span>
                    <span class="nf-stat-label">Customer Satisfaction</span>
                </div>
            </div>
        </div>
        <div class="nf-stats-note" data-animate>
            <i class="fas fa-shield-alt"></i>
            <span>All data is encrypted and secure. PCI DSS & GDPR compliant.</span>
        </div>
    </div>
</section>
