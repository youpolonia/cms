<?php
/**
 * Starter Restaurant — About Section
 * Asymmetric layout with decorative gold frame and experience counter
 * Variables inherited from parent scope
 */

$feature1Title = theme_get('about.feature1_title', 'Fresh Ingredients');
$feature1Desc  = theme_get('about.feature1_desc', 'Locally sourced, seasonal produce from trusted farms');
$feature2Title = theme_get('about.feature2_title', 'Award-Winning Chef');
$feature2Desc  = theme_get('about.feature2_desc', '15 years of culinary excellence and passion');
$feature3Title = theme_get('about.feature3_title', 'Fine Wine Selection');
$feature3Desc  = theme_get('about.feature3_desc', 'Curated cellar with over 200 Italian and French wines');
$aboutExpNum   = theme_get('about.exp_number', '15+');
$aboutExpText  = theme_get('about.exp_text', 'Years of Excellence');
?>
<!-- About Section -->
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-images" data-animate-left>
                <div class="about-img-main" data-ts-bg="about.image"<?php if ($aboutImage): ?> style="background: url(<?= esc($aboutImage) ?>) center/cover no-repeat"<?php endif; ?>>
                    <?php if (!$aboutImage): ?>
                    <?php if (function_exists('cms_admin_image_placeholder')): ?>
                        <?php cms_admin_image_placeholder('about.image', 'fas fa-utensils'); ?>
                    <?php else: ?>
                        <div class="img-placeholder"><i class="fas fa-utensils"></i></div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="about-exp">
                    <span class="exp-number" data-ts="about.exp_number"><?= esc($aboutExpNum) ?></span>
                    <span data-ts="about.exp_text"><?= esc($aboutExpText) ?></span>
                </div>
            </div>
            <div class="about-content" data-animate-right>
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <div class="ornament" style="margin:16px 0 24px;justify-content:flex-start">
                    <i class="fas fa-diamond"></i>
                </div>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-lead" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                        <div>
                            <h4 data-ts="about.feature1_title"><?= esc($feature1Title) ?></h4>
                            <p data-ts="about.feature1_desc"><?= esc($feature1Desc) ?></p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-award"></i></div>
                        <div>
                            <h4 data-ts="about.feature2_title"><?= esc($feature2Title) ?></h4>
                            <p data-ts="about.feature2_desc"><?= esc($feature2Desc) ?></p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon"><i class="fas fa-wine-glass-alt"></i></div>
                        <div>
                            <h4 data-ts="about.feature3_title"><?= esc($feature3Title) ?></h4>
                            <p data-ts="about.feature3_desc"><?= esc($feature3Desc) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
