<?php
$aboutLabel = theme_get('about.label', 'Our Expertise');
$aboutTitle = theme_get('about.title', 'Crafting Outdoor Masterpieces');
$aboutDesc = theme_get('about.description', 'With over 15 years of experience, TerraForm Landscapes combines artistic vision with technical precision to create functional, beautiful outdoor environments that stand the test of time.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about-image.jpg');
?>
<section class="tf-section tf-about" id="about">
    <div class="container">
        <div class="tf-about-grid">
            <div class="tf-about-content" data-animate>
                <div class="tf-section-header tf-text-left">
                    <span class="tf-section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="tf-section-divider tf-divider-left"></div>
                    <h2 class="tf-section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                    <p class="tf-section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                </div>
                <div class="tf-about-features">
                    <div class="tf-about-feature">
                        <div class="tf-about-feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="tf-about-feature-text">
                            <h4>Licensed & Insured</h4>
                            <p>Fully certified professionals with comprehensive liability coverage.</p>
                        </div>
                    </div>
                    <div class="tf-about-feature">
                        <div class="tf-about-feature-icon">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="tf-about-feature-text">
                            <h4>On-Time Completion</h4>
                            <p>We respect your schedule with realistic timelines and clear communication.</p>
                        </div>
                    </div>
                    <div class="tf-about-feature">
                        <div class="tf-about-feature-icon">
                            <i class="fas fa-recycle"></i>
                        </div>
                        <div class="tf-about-feature-text">
                            <h4>Sustainable Practices</h4>
                            <p>Eco-friendly materials and water-wise design solutions.</p>
                        </div>
                    </div>
                </div>
                <a href="/about" class="tf-btn tf-btn-secondary">
                    Learn More About Us
                    <i class="fas fa-arrow-right tf-btn-icon"></i>
                </a>
            </div>
            <div class="tf-about-image" data-animate>
                <div class="tf-about-image-frame">
                    <img src="<?= esc($aboutImage) ?>" alt="Professional landscaping team at work" data-ts-bg="about.image" loading="lazy">
                    <div class="tf-about-image-badge">
                        <span class="tf-about-image-badge-number">15</span>
                        <span class="tf-about-image-badge-text">Years of Excellence</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
