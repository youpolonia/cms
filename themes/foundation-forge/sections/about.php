<?php
$aboutLabel = theme_get('about.label', 'OUR COMPANY');
$aboutTitle = theme_get('about.title', 'Built on Integrity & Engineering Excellence');
$aboutDesc = theme_get('about.description', 'For over two decades, Foundation Forge has been the trusted partner for commercial and municipal clients requiring precise, durable, and code-compliant paving and groundwork solutions.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about-team.jpg');
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-content" data-animate>
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="feature">
                        <i class="fas fa-award"></i>
                        <div>
                            <h4>Licensed & Insured</h4>
                            <p>Fully bonded with $5M liability coverage and all required state certifications.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <div>
                            <h4>Expert Team</h4>
                            <p>Our crew includes certified project managers, civil engineers, and skilled operators.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <div>
                            <h4>Safety First</h4>
                            <p>OSHA-compliant protocols with zero lost-time incidents for three consecutive years.</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <h4>Sustainable Practices</h4>
                            <p>We utilize recycled materials and erosion control measures to minimize environmental impact.</p>
                        </div>
                    </div>
                </div>
                <a href="/about" class="btn btn-primary">
                    Learn Our Story <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="about-media" data-animate>
                <div class="about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>');"></div>
                <div class="about-badge">
                    <span class="badge-number">25</span>
                    <span class="badge-text">Years of<br>Trusted Service</span>
                </div>
            </div>
        </div>
    </div>
</section>
