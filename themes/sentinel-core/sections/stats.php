<?php
$statsLabel = theme_get('stats.label', 'TRUST METRICS');
$statsTitle = theme_get('stats.title', 'Enterprise‑Grade Protection, Quantified');
$statsDesc = theme_get('stats.description', 'Our AI‑driven platform delivers measurable security outcomes for organizations worldwide.');
?>
<section class="sc-section sc-section-stats" id="stats" style="background-color: var(--surface);">
    <div class="container">
        <div class="sc-section-header" data-animate>
            <span class="sc-section-label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="sc-section-divider"></div>
            <h2 class="sc-section-title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="sc-section-desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>

        <div class="sc-stats-grid">
            <div class="sc-stat-card" data-animate>
                <div class="sc-stat-icon"><i class="fas fa-shield-alt"></i></div>
                <div class="sc-stat-content">
                    <div class="sc-stat-number" data-counter="99.9">99.9%</div>
                    <div class="sc-stat-label">Threat Prevention Rate</div>
                    <p class="sc-stat-desc">AI‑blocked zero‑day exploits across all endpoints.</p>
                </div>
            </div>

            <div class="sc-stat-card" data-animate>
                <div class="sc-stat-icon"><i class="fas fa-bolt"></i></div>
                <div class="sc-stat-content">
                    <div class="sc-stat-number" data-counter="45">45</div>
                    <div class="sc-stat-label">Seconds to Response</div>
                    <p class="sc-stat-desc">Average automated incident containment time.</p>
                </div>
            </div>

            <div class="sc-stat-card" data-animate>
                <div class="sc-stat-icon"><i class="fas fa-globe"></i></div>
                <div class="sc-stat-content">
                    <div class="sc-stat-number" data-counter="2500">2,500+</div>
                    <div class="sc-stat-label">Protected Enterprises</div>
                    <p class="sc-stat-desc">Global organizations secured by Sentinel Core.</p>
                </div>
            </div>

            <div class="sc-stat-card" data-animate>
                <div class="sc-stat-icon"><i class="fas fa-chart-line"></i></div>
                <div class="sc-stat-content">
                    <div class="sc-stat-number" data-counter="98">98%</div>
                    <div class="sc-stat-label">Operational Efficiency</div>
                    <p class="sc-stat-desc">Reduction in manual security analyst workload.</p>
                </div>
            </div>
        </div>

        <div class="sc-stats-note">
            <p><i class="fas fa-info-circle"></i> Metrics based on aggregated platform data from the last 12 months.</p>
        </div>
    </div>
</section>
