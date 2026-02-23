<?php
$statsLabel = theme_get('stats.label', 'BY THE NUMBERS');
$statsTitle = theme_get('stats.title', 'Trusted by Remote Teams Worldwide');
$statsDesc = theme_get('stats.description', 'Our platform powers collaboration for thousands of distributed engineering teams, from startups to enterprises.');
?>
<section class="sn-section sn-section-stats" id="stats">
    <div class="container">
        <div class="sn-section-header" data-animate>
            <span class="sn-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="sn-section-divider"></div>
            <h2 class="sn-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="sn-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="sn-stats-grid">
            <div class="sn-stat-card" data-animate>
                <div class="sn-stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="sn-stat-content">
                    <span class="sn-stat-number" data-counter="true">25,000</span>
                    <span class="sn-stat-label">Active Teams</span>
                </div>
            </div>
            <div class="sn-stat-card" data-animate>
                <div class="sn-stat-icon">
                    <i class="fas fa-code-branch"></i>
                </div>
                <div class="sn-stat-content">
                    <span class="sn-stat-number" data-counter="true">500</span>
                    <span class="sn-stat-label">Dev Tool Integrations</span>
                </div>
            </div>
            <div class="sn-stat-card" data-animate>
                <div class="sn-stat-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="sn-stat-content">
                    <span class="sn-stat-number" data-counter="true">2.1M</span>
                    <span class="sn-stat-label">Video Calls / Month</span>
                </div>
            </div>
            <div class="sn-stat-card" data-animate>
                <div class="sn-stat-icon">
                    <i class="fas fa-globe-americas"></i>
                </div>
                <div class="sn-stat-content">
                    <span class="sn-stat-number" data-counter="true">150</span>
                    <span class="sn-stat-label">Countries</span>
                </div>
            </div>
        </div>
        <div class="sn-stats-note">
            <p><i class="fas fa-chart-line"></i> <strong>30% faster</strong> project completion reported by teams using Synapse Nexus.</p>
        </div>
    </div>
</section>
