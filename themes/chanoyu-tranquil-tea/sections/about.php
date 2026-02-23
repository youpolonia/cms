<?php
$aboutLabel = theme_get('about.label', 'Our Philosophy');
$aboutTitle = theme_get('about.title', 'Where Ancient Ritual Meets Modern Grace');
$aboutDesc = theme_get('about.description', 'Chanoyu embodies the timeless principles of harmony, respect, purity, and tranquility. Each ceremony is a meditation, each moment an invitation to presence.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about-image.jpg');
$aboutFeature1 = theme_get('about.feature1', 'Authentic Ceremonies');
$aboutFeature2 = theme_get('about.feature2', 'Seasonal Kaiseki');
$aboutFeature3 = theme_get('about.feature3', 'Bamboo Garden Setting');
$aboutFeature4 = theme_get('about.feature4', 'Master Tea Artisans');
?>
<section class="ch-about" id="about">
    <div class="container">
        <div class="ch-about-grid">
            <div class="ch-about-content">
                <div class="ch-section-header">
                    <span class="ch-section-label" data-ts="about.label" data-animate>
                        <i class="fas fa-spa"></i>
                        <?= esc($aboutLabel) ?>
                    </span>
                    <div class="ch-section-divider"></div>
                    <h2 class="ch-section-title" data-ts="about.title" data-animate>
                        <?= esc($aboutTitle) ?>
                    </h2>
                    <p class="ch-section-desc" data-ts="about.description" data-animate>
                        <?= esc($aboutDesc) ?>
                    </p>
                </div>

                <div class="ch-about-features" data-animate>
                    <div class="ch-feature-item">
                        <div class="ch-feature-icon">
                            <i class="fas fa-yin-yang"></i>
                        </div>
                        <h4 class="ch-feature-title" data-ts="about.feature1">
                            <?= esc($aboutFeature1) ?>
                        </h4>
                    </div>
                    <div class="ch-feature-item">
                        <div class="ch-feature-icon">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h4 class="ch-feature-title" data-ts="about.feature2">
                            <?= esc($aboutFeature2) ?>
                        </h4>
                    </div>
                    <div class="ch-feature-item">
                        <div class="ch-feature-icon">
                            <i class="fas fa-tree"></i>
                        </div>
                        <h4 class="ch-feature-title" data-ts="about.feature3">
                            <?= esc($aboutFeature3) ?>
                        </h4>
                    </div>
                    <div class="ch-feature-item">
                        <div class="ch-feature-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h4 class="ch-feature-title" data-ts="about.feature4">
                            <?= esc($aboutFeature4) ?>
                        </h4>
                    </div>
                </div>
            </div>

            <div class="ch-about-image-wrap" data-animate>
                <div class="ch-about-image" data-ts-bg="about.image" style="background-image: url('<?= esc($aboutImage) ?>')">
                    <div class="ch-image-overlay">
                        <div class="ch-image-badge">
                            <i class="fas fa-leaf"></i>
                            <span>Since 2003</span>
                        </div>
                    </div>
                </div>
                <div class="ch-about-accent"></div>
            </div>
        </div>
    </div>
</section>
