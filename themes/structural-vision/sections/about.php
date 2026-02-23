<?php
$aboutLabel = theme_get('about.label', 'About Us');
$aboutTitle = theme_get('about.title', 'Trusted Paving Contractors Since 2001');
$aboutDesc = theme_get('about.description', 'With over 23 years of experience, Edi\'s Paving Contractors has built a reputation for delivering exceptional quality workmanship across Essex. From residential driveways to large commercial projects, our dedicated team combines traditional craftsmanship with modern techniques to transform outdoor spaces.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about-team.jpg');
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-wrapper">
            <div class="about-image-col" data-animate>
                <div class="about-image-frame">
                    <img src="<?= esc($aboutImage) ?>" 
                         alt="Edi's Paving Team" 
                         data-ts-bg="about.image"
                         loading="lazy">
                    <div class="experience-badge">
                        <span class="exp-number">23</span>
                        <span class="exp-text">Years of<br>Excellence</span>
                    </div>
                </div>
            </div>
            <div class="about-content-col" data-animate>
                <span class="section-label" data-ts="about.label">
                    <i class="fas fa-hard-hat"></i>
                    <?= esc($aboutLabel) ?>
                </span>
                <h2 class="about-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Quality Guaranteed</strong>
                            <span>Premium materials & expert workmanship</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Fully Insured</strong>
                            <span>Complete peace of mind on every project</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div class="feature-text">
                            <strong>Free Consultations</strong>
                            <span>No-obligation quotes & expert advice</span>
                        </div>
                    </div>
                </div>
                <a href="#contact" class="btn btn-primary">
                    Discuss Your Project
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>