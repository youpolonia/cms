<?php
$aboutLabel = theme_get('about.label', 'Our Philosophy');
$aboutTitle = theme_get('about.title', 'Less Is More');
$aboutDesc = theme_get('about.description', 'We believe great coffee doesn\'t need complexity. Just exceptional beans, precise technique, and a calm moment to enjoy it.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about.jpg');
$aboutFeature1Title = theme_get('about.feature1_title', 'Direct Trade');
$aboutFeature1Desc = theme_get('about.feature1_desc', 'We work directly with farmers in Ethiopia, Colombia, and Guatemala.');
$aboutFeature2Title = theme_get('about.feature2_title', 'Roasted Weekly');
$aboutFeature2Desc = theme_get('about.feature2_desc', 'Small-batch roasting ensures peak freshness in every cup.');
$aboutFeature3Title = theme_get('about.feature3_title', 'Pour-Over Bar');
$aboutFeature3Desc = theme_get('about.feature3_desc', 'Watch your coffee brewed by hand, exactly how you like it.');
?>
<section class="section about-section" id="about">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-col" data-animate>
                <div class="about-image-wrapper">
                    <img src="<?= esc($aboutImage) ?>" alt="Nordic Brew interior" loading="lazy" data-ts-bg="about.image">
                </div>
            </div>
            <div class="about-content-col" data-animate>
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="about-feature">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <h4 data-ts="about.feature1_title"><?= esc($aboutFeature1Title) ?></h4>
                            <p data-ts="about.feature1_desc"><?= esc($aboutFeature1Desc) ?></p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-fire"></i>
                        <div>
                            <h4 data-ts="about.feature2_title"><?= esc($aboutFeature2Title) ?></h4>
                            <p data-ts="about.feature2_desc"><?= esc($aboutFeature2Desc) ?></p>
                        </div>
                    </div>
                    <div class="about-feature">
                        <i class="fas fa-hand-holding-water"></i>
                        <div>
                            <h4 data-ts="about.feature3_title"><?= esc($aboutFeature3Title) ?></h4>
                            <p data-ts="about.feature3_desc"><?= esc($aboutFeature3Desc) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
