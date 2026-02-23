<?php
$aboutLabel = theme_get('about.label', 'Our Mission');
$aboutTitle = theme_get('about.title', 'Transforming Healthcare with Genomic Insight');
$aboutDesc = theme_get('about.description', 'We combine cutting-edge genomic sequencing with advanced AI analytics to deliver personalized treatment plans, empowering patients and physicians with actionable health intelligence.');
$aboutImage = theme_get('about.image', '');
?>
<section class="gp-about" id="about">
    <div class="container">
        <div class="gp-about__grid">
            <div class="gp-about__content" data-animate>
                <div class="gp-about__header">
                    <span class="gp-about__label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="gp-about__divider"></div>
                    <h2 class="gp-about__title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                    <p class="gp-about__desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                </div>
                <div class="gp-about__features">
                    <div class="gp-about__feature">
                        <div class="gp-about__feature-icon">
                            <i class="fas fa-dna"></i>
                        </div>
                        <div class="gp-about__feature-content">
                            <h4>Whole Genome Sequencing</h4>
                            <p>Comprehensive analysis of your entire genetic code for unprecedented insight.</p>
                        </div>
                    </div>
                    <div class="gp-about__feature">
                        <div class="gp-about__feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="gp-about__feature-content">
                            <h4>AI-Powered Analytics</h4>
                            <p>Advanced algorithms identify patterns and predict health outcomes with precision.</p>
                        </div>
                    </div>
                    <div class="gp-about__feature">
                        <div class="gp-about__feature-icon">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="gp-about__feature-content">
                            <h4>Clinical Integration</h4>
                            <p>Seamless collaboration with healthcare providers for actionable treatment plans.</p>
                        </div>
                    </div>
                </div>
                <a href="/about" class="gp-about__link">
                    Learn more about our approach
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="gp-about__visual" data-animate>
                <?php if ($aboutImage): ?>
                    <div class="gp-about__image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>');"></div>
                <?php else: ?>
                    <div class="gp-about__placeholder">
                        <i class="fas fa-dna"></i>
                    </div>
                <?php endif; ?>
                <div class="gp-about__pattern"></div>
            </div>
        </div>
    </div>
</section>
