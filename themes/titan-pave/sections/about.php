<?php
$aboutLabel = theme_get('about.label', 'Who We Are');
$aboutTitle = theme_get('about.title', 'Essex\'s Trusted Paving Experts Since 2001');
$aboutDesc = theme_get('about.description', 'Edi\'s Paving Contractors has been transforming outdoor spaces across Essex for over 23 years. From residential driveways to large commercial projects, we bring unmatched expertise, premium materials, and meticulous attention to detail to every job.');
$aboutImage = theme_get('about.image', $themePath . '/assets/images/about.jpg');
$aboutFeature1 = theme_get('about.feature1', 'Family-owned business with deep roots in the Essex community');
$aboutFeature2 = theme_get('about.feature2', 'Fully insured and certified professionals');
$aboutFeature3 = theme_get('about.feature3', 'Premium materials sourced from trusted suppliers');
$aboutFeature4 = theme_get('about.feature4', 'Competitive pricing with free, no-obligation quotes');
?>
<section class="section about-section" id="about">
    <div class="about-shape"></div>
    <div class="container">
        <div class="about-grid">
            <div class="about-visual" data-animate>
                <div class="about-image-wrapper">
                    <div class="about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>')"></div>
                    <div class="about-image-accent"></div>
                </div>
                <div class="about-experience">
                    <span class="exp-number">23+</span>
                    <span class="exp-text">Years of<br>Excellence</span>
                </div>
            </div>
            <div class="about-content" data-animate>
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-text" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <ul class="about-features">
                    <li data-animate>
                        <i class="fas fa-check-circle"></i>
                        <span data-ts="about.feature1"><?= esc($aboutFeature1) ?></span>
                    </li>
                    <li data-animate>
                        <i class="fas fa-check-circle"></i>
                        <span data-ts="about.feature2"><?= esc($aboutFeature2) ?></span>
                    </li>
                    <li data-animate>
                        <i class="fas fa-check-circle"></i>
                        <span data-ts="about.feature3"><?= esc($aboutFeature3) ?></span>
                    </li>
                    <li data-animate>
                        <i class="fas fa-check-circle"></i>
                        <span data-ts="about.feature4"><?= esc($aboutFeature4) ?></span>
                    </li>
                </ul>
                <a href="#contact" class="btn btn-primary">
                    <span>Start Your Project</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>