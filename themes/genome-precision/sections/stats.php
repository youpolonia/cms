<?php
$statsLabel = theme_get('stats.label', 'By The Numbers');
$statsTitle = theme_get('stats.title', 'Proven Impact in Genomic Medicine');
$statsDesc = theme_get('stats.description', 'Our data-driven approach delivers measurable results for patients, researchers, and healthcare systems worldwide.');
?>
<section class="gp-stats">
    <div class="container">
        <div class="gp-stats__header" data-animate>
            <span class="gp-stats__label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="gp-stats__divider"></div>
            <h2 class="gp-stats__title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="gp-stats__desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="gp-stats__grid">
            <div class="gp-stats__card" data-animate>
                <div class="gp-stats__card-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="gp-stats__card-number" data-count="250">0</div>
                <div class="gp-stats__card-label">Peer-Reviewed Publications</div>
                <div class="gp-stats__card-desc">Contributing to global genomic research</div>
            </div>
            <div class="gp-stats__card" data-animate>
                <div class="gp-stats__card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="gp-stats__card-number" data-count="5000">0</div>
                <div class="gp-stats__card-label">Patients Empowered</div>
                <div class="gp-stats__card-desc">With personalized treatment plans</div>
            </div>
            <div class="gp-stats__card" data-animate>
                <div class="gp-stats__card-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="gp-stats__card-number" data-count="15">0</div>
                <div class="gp-stats__card-label">Clinical Trials Supported</div>
                <div class="gp-stats__card-desc">Accelerating drug development</div>
            </div>
            <div class="gp-stats__card" data-animate>
                <div class="gp-stats__card-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="gp-stats__card-number" data-count="100">100</div>
                <div class="gp-stats__card-label">Data Security Certifications</div>
                <div class="gp-stats__card-desc">Ensuring privacy and compliance</div>
            </div>
        </div>
    </div>
</section>
