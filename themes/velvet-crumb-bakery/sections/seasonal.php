<?php
$seasonalLabel = theme_get('seasonal.label', 'Limited Edition');
$seasonalTitle = theme_get('seasonal.title', 'Seasonal Specials');
$seasonalDesc = theme_get('seasonal.description', 'Our pastry chefs create exclusive flavors inspired by the finest seasonal ingredients, available for a limited time only.');
$seasonalBtnText = theme_get('seasonal.btn_text', 'Order Seasonal Special');
$seasonalBtnLink = theme_get('seasonal.btn_link', '#contact');

$seasonalImages = [
    'https://images.pexels.com/photos/30632210/pexels-photo-30632210.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/33565479/pexels-photo-33565479.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/30846579/pexels-photo-30846579.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="vcb-section vcb-seasonal" id="seasonal">
    <div class="container">
        <div class="vcb-section-header" data-animate>
            <span class="vcb-section-label" data-ts="seasonal.label"><?= esc($seasonalLabel) ?></span>
            <div class="vcb-section-divider"></div>
            <h2 class="vcb-section-title" data-ts="seasonal.title"><?= esc($seasonalTitle) ?></h2>
            <p class="vcb-section-desc" data-ts="seasonal.description"><?= esc($seasonalDesc) ?></p>
        </div>

        <div class="vcb-seasonal-showcase">
            <div class="vcb-seasonal-main" data-animate>
                <div class="vcb-seasonal-img">
                    <img src="<?= esc($seasonalImages[0]) ?>" alt="Spring Berry Trio Cheesecake" loading="lazy">
                    <div class="vcb-seasonal-badge">Spring 2024</div>
                </div>
                <div class="vcb-seasonal-content">
                    <h3 class="vcb-seasonal-name">Spring Berry Trio</h3>
                    <p class="vcb-seasonal-desc">A delicate blend of fresh strawberries, raspberries, and blueberries in a light vanilla bean cheesecake with almond crust.</p>
                    <div class="vcb-seasonal-details">
                        <div class="vcb-seasonal-detail">
                            <i class="fas fa-calendar"></i>
                            <span>Available: March – June</span>
                        </div>
                        <div class="vcb-seasonal-detail">
                            <i class="fas fa-clock"></i>
                            <span>48-hour advance order required</span>
                        </div>
                    </div>
                    <div class="vcb-seasonal-actions">
                        <span class="vcb-seasonal-price">$58</span>
                        <a href="<?= esc($seasonalBtnLink) ?>" class="vcb-btn vcb-btn-accent" data-ts="seasonal.btn_text" data-ts-href="seasonal.btn_link">
                            <?= esc($seasonalBtnText) ?>
                        </a>
                    </div>
                </div>
            </div>

            <div class="vcb-seasonal-secondary">
                <div class="vcb-seasonal-item" data-animate>
                    <div class="vcb-seasonal-item-img">
                        <img src="<?= esc($seasonalImages[1]) ?>" alt="Pumpkin Spice Cheesecake" loading="lazy">
                    </div>
                    <div class="vcb-seasonal-item-content">
                        <h4>Pumpkin Spice</h4>
                        <p>Rich pumpkin with warm spices and gingersnap crust.</p>
                        <span class="vcb-seasonal-item-tag">Fall</span>
                    </div>
                </div>

                <div class="vcb-seasonal-item" data-animate>
                    <div class="vcb-seasonal-item-img">
                        <img src="<?= esc($seasonalImages[2]) ?>" alt="Peppermint Mocha Cheesecake" loading="lazy">
                    </div>
                    <div class="vcb-seasonal-item-content">
                        <h4>Peppermint Mocha</h4>
                        <p>Festive chocolate coffee with crushed candy cane topping.</p>
                        <span class="vcb-seasonal-item-tag">Winter</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="vcb-seasonal-notice" data-animate>
            <i class="fas fa-bell"></i>
            <p>Join our newsletter to be first notified about new seasonal releases.</p>
        </div>
    </div>
</section>
