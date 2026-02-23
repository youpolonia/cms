<?php
$heroHeadline = theme_get('hero.headline', 'Artisan Cheesecakes of Unparalleled Luxury');
$heroSubtitle = theme_get('hero.subtitle', 'Experience 30+ exquisite flavors, seasonal masterpieces, and same-day delivery for the ultimate indulgence.');
$heroBtnText = theme_get('hero.btn_text', 'Explore Our Flavors');
$heroBtnLink = theme_get('hero.btn_link', '#flavors');
$heroBgImage = theme_get('hero.bg_image', 'https://images.pexels.com/photos/30890572/pexels-photo-30890572.jpeg?auto=compress&cs=tinysrgb&h=650&w=940');
$heroBadge = theme_get('hero.badge', 'Since 2015');
?>
<section class="vcb-hero" id="hero">
    <div class="vcb-hero-bg" style="background-image: url('<?= esc($heroBgImage) ?>');" data-ts-bg="hero.bg_image"></div>
    <div class="vcb-hero-overlay"></div>
    <div class="container">
        <div class="vcb-hero-content" data-animate>
            <?php if ($heroBadge): ?>
            <span class="vcb-hero-badge" data-ts="hero.badge"><?= esc($heroBadge) ?></span>
            <?php endif; ?>
            <h1 class="vcb-hero-headline" data-ts="hero.headline"><?= esc($heroHeadline) ?></h1>
            <p class="vcb-hero-subtitle" data-ts="hero.subtitle"><?= esc($heroSubtitle) ?></p>
            <div class="vcb-hero-actions">
                <a href="<?= esc($heroBtnLink) ?>" class="vcb-btn vcb-btn-primary vcb-hero-cta" data-ts="hero.btn_text" data-ts-href="hero.btn_link">
                    <?= esc($heroBtnText) ?>
                </a>
                <a href="#delivery" class="vcb-btn vcb-btn-outline">
                    <i class="fas fa-truck"></i>
                    <span>Same-Day Delivery</span>
                </a>
            </div>
            <div class="vcb-hero-stats">
                <div class="vcb-stat">
                    <span class="vcb-stat-number">30+</span>
                    <span class="vcb-stat-label">Signature Flavors</span>
                </div>
                <div class="vcb-stat">
                    <span class="vcb-stat-number">24h</span>
                    <span class="vcb-stat-label">Aged to Perfection</span>
                </div>
                <div class="vcb-stat">
                    <span class="vcb-stat-number">100%</span>
                    <span class="vcb-stat-label">Artisan Crafted</span>
                </div>
            </div>
        </div>
    </div>
    <div class="vcb-hero-scroll">
        <a href="#flavors" aria-label="Scroll down">
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</section>
