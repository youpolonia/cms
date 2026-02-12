<?php
/**
 * Starter Restaurant — About Section
 * Variables inherited from parent scope: $aboutLabel, $aboutTitle, $aboutDesc, $aboutImage
 */
?>
<!-- About Section -->
<section class="section about-section">
    <div class="container">
        <div class="about-grid">
            <div class="about-images">
                <div class="about-img-main" data-ts-bg="about.image"<?php if ($aboutImage): ?> style="background: url(<?= esc($aboutImage) ?>) center/cover no-repeat"<?php endif; ?>>
                    <?php if (!$aboutImage): ?>
                    <div class="img-placeholder"><i class="fas fa-utensils"></i></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="about-content">
                <span class="section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                <h2 class="section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                <p class="about-lead" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                <div class="about-features">
                    <div class="feature">
                        <i class="fas fa-leaf"></i>
                        <div>
                            <h4>Fresh Ingredients</h4>
                            <p>Locally sourced, seasonal produce from trusted farms</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-award"></i>
                        <div>
                            <h4>Award-Winning Chef</h4>
                            <p>15 years of culinary excellence and passion</p>
                        </div>
                    </div>
                    <div class="feature">
                        <i class="fas fa-wine-glass-alt"></i>
                        <div>
                            <h4>Fine Wine Selection</h4>
                            <p>Curated cellar with over 200 Italian and French wines</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
