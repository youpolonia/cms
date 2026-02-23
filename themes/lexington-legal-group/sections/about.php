<?php
$aboutLabel = theme_get('about.label', 'Our Legacy');
$aboutTitle = theme_get('about.title', 'A Tradition of Excellence in Legal Practice');
$aboutDesc = theme_get('about.description', 'For three decades, Lexington Legal Group has provided sophisticated legal solutions to corporations, entrepreneurs, and innovators. Our depth of experience and forward-thinking approach sets us apart.');
$aboutImage = theme_get('about.image', '');
?>
<section class="llg-section llg-about" id="about">
    <div class="container">
        <div class="llg-about-grid">
            <div class="llg-about-content" data-animate>
                <div class="llg-section-header">
                    <span class="llg-section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="llg-section-divider"></div>
                    <h2 class="llg-section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                    <p class="llg-section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                </div>
                <div class="llg-about-features">
                    <div class="llg-feature">
                        <div class="llg-feature-icon">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                        <div class="llg-feature-text">
                            <h4>Ethical Integrity</h4>
                            <p>We uphold the highest standards of professional ethics and confidentiality in every engagement.</p>
                        </div>
                    </div>
                    <div class="llg-feature">
                        <div class="llg-feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="llg-feature-text">
                            <h4>Strategic Vision</h4>
                            <p>Our counsel extends beyond legal compliance to drive business growth and competitive advantage.</p>
                        </div>
                    </div>
                    <div class="llg-feature">
                        <div class="llg-feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="llg-feature-text">
                            <h4>Client Partnership</h4>
                            <p>We build long-term relationships, becoming trusted advisors to our clients' leadership teams.</p>
                        </div>
                    </div>
                </div>
                <a href="/about" class="llg-btn llg-btn--secondary">
                    <span>Learn Our Story</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="llg-about-visual" data-animate>
                <?php if ($aboutImage): ?>
                    <div class="llg-about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>');"></div>
                <?php else: ?>
                    <div class="llg-about-placeholder">
                        <i class="fas fa-gavel"></i>
                    </div>
                <?php endif; ?>
                <div class="llg-about-accent"></div>
            </div>
        </div>
    </div>
</section>
