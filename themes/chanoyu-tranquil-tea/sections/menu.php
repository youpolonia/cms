<?php
$menuLabel = theme_get('menu.label', 'Our Offerings');
$menuTitle = theme_get('menu.title', 'Curated Tea & Culinary Experiences');
$menuDesc = theme_get('menu.description', 'From ceremonial matcha to seasonal kaiseki, each offering is crafted with reverence and artistry.');

$menu1Title = theme_get('menu.item1_title', 'Traditional Matcha Ceremony');
$menu1Desc = theme_get('menu.item1_desc', 'Authentic preparation of premium matcha in a private tatami room setting');
$menu1Price = theme_get('menu.item1_price', '£45');

$menu2Title = theme_get('menu.item2_title', 'Wagashi Selection');
$menu2Desc = theme_get('menu.item2_desc', 'Seasonal handcrafted confections paired with ceremonial tea');
$menu2Price = theme_get('menu.item2_price', '£28');

$menu3Title = theme_get('menu.item3_title', 'Kaiseki Tasting Menu');
$menu3Desc = theme_get('menu.item3_desc', 'Seven-course seasonal journey featuring locally sourced ingredients');
$menu3Price = theme_get('menu.item3_price', '£85');

$menu4Title = theme_get('menu.item4_title', 'Tea Ceremony Workshop');
$menu4Desc = theme_get('menu.item4_desc', 'Learn the art of tea preparation with our master tea artisan');
$menu4Price = theme_get('menu.item4_price', '£120');

$menu5Title = theme_get('menu.item5_title', 'Premium Tea Collection');
$menu5Desc = theme_get('menu.item5_desc', 'Selection of rare single-origin teas from Kyoto and Uji');
$menu5Price = theme_get('menu.item5_price', '£18');

$menu6Title = theme_get('menu.item6_title', 'Garden Meditation');
$menu6Desc = theme_get('menu.item6_desc', 'Guided mindfulness session in our bamboo garden with tea service');
$menu6Price = theme_get('menu.item6_price', '£38');
?>
<section class="ch-menu" id="menu">
    <div class="container">
        <div class="ch-section-header ch-section-header-center">
            <span class="ch-section-label" data-ts="menu.label" data-animate>
                <i class="fas fa-utensils"></i>
                <?= esc($menuLabel) ?>
            </span>
            <div class="ch-section-divider ch-section-divider-center"></div>
            <h2 class="ch-section-title" data-ts="menu.title" data-animate>
                <?= esc($menuTitle) ?>
            </h2>
            <p class="ch-section-desc" data-ts="menu.description" data-animate>
                <?= esc($menuDesc) ?>
            </p>
        </div>

        <div class="ch-menu-grid">
            <div class="ch-menu-item" data-animate>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item1_title">
                        <?= esc($menu1Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item1_desc">
                    <?= esc($menu1Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item1_price">
                        <?= esc($menu1Price) ?>
                    </span>
                </div>
            </div>

            <div class="ch-menu-item" data-animate>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-cookie-bite"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item2_title">
                        <?= esc($menu2Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item2_desc">
                    <?= esc($menu2Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item2_price">
                        <?= esc($menu2Price) ?>
                    </span>
                </div>
            </div>

            <div class="ch-menu-item ch-menu-item-featured" data-animate>
                <div class="ch-menu-featured-badge">
                    <i class="fas fa-star"></i>
                    <span>Signature</span>
                </div>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item3_title">
                        <?= esc($menu3Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item3_desc">
                    <?= esc($menu3Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item3_price">
                        <?= esc($menu3Price) ?>
                    </span>
                </div>
            </div>

            <div class="ch-menu-item" data-animate>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item4_title">
                        <?= esc($menu4Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item4_desc">
                    <?= esc($menu4Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item4_price">
                        <?= esc($menu4Price) ?>
                    </span>
                </div>
            </div>

            <div class="ch-menu-item" data-animate>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-mug-hot"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item5_title">
                        <?= esc($menu5Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item5_desc">
                    <?= esc($menu5Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item5_price">
                        <?= esc($menu5Price) ?>
                    </span>
                </div>
            </div>

            <div class="ch-menu-item" data-animate>
                <div class="ch-menu-item-header">
                    <div class="ch-menu-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3 class="ch-menu-item-title" data-ts="menu.item6_title">
                        <?= esc($menu6Title) ?>
                    </h3>
                </div>
                <p class="ch-menu-item-desc" data-ts="menu.item6_desc">
                    <?= esc($menu6Desc) ?>
                </p>
                <div class="ch-menu-item-footer">
                    <span class="ch-menu-price" data-ts="menu.item6_price">
                        <?= esc($menu6Price) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
