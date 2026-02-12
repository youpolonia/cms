<?php
/**
 * Starter Restaurant — About Section
 * Variables inherited from parent scope: $aboutLabel, $aboutTitle, $aboutDesc, $aboutImage, $pages
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
                    <?php if (!empty($pages)): ?>
                        <?php foreach (array_slice($pages, 0, 3) as $p): ?>
                        <div class="feature">
                            <i class="fas fa-star"></i>
                            <div>
                                <h4><?= esc($p['title']) ?></h4>
                                <p><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 80, '...')) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
