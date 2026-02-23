<?php
$deliveryLabel = theme_get('delivery.label', 'Convenience');
$deliveryTitle = theme_get('delivery.title', 'Same-Day Local Delivery');
$deliveryDesc = theme_get('delivery.description', 'Enjoy our artisan cheesecakes delivered fresh to your door. We serve the metropolitan area with careful temperature-controlled transport.');
$deliveryBtnText = theme_get('delivery.btn_text', 'Check Delivery Area');
$deliveryBtnLink = theme_get('delivery.btn_link', '#contact');

$deliveryImage = 'https://images.pexels.com/photos/34136550/pexels-photo-34136550.jpeg?auto=compress&cs=tinysrgb&h=650&w=940';
?>
<section class="vcb-section vcb-delivery" id="delivery">
    <div class="container">
        <div class="vcb-delivery-grid">
            <div class="vcb-delivery-content" data-animate>
                <div class="vcb-section-header">
                    <span class="vcb-section-label" data-ts="delivery.label"><?= esc($deliveryLabel) ?></span>
                    <div class="vcb-section-divider"></div>
                    <h2 class="vcb-section-title" data-ts="delivery.title"><?= esc($deliveryTitle) ?></h2>
                    <p class="vcb-section-desc" data-ts="delivery.description"><?= esc($deliveryDesc) ?></p>
                </div>

                <div class="vcb-delivery-features">
                    <div class="vcb-delivery-feature">
                        <div class="vcb-delivery-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="vcb-delivery-feature-content">
                            <h4>Same-Day Service</h4>
                            <p>Order by 2 PM for delivery between 4–8 PM. Next-day delivery also available.</p>
                        </div>
                    </div>

                    <div class="vcb-delivery-feature">
                        <div class="vcb-delivery-icon">
                            <i class="fas fa-temperature-low"></i>
                        </div>
                        <div class="vcb-delivery-feature-content">
                            <h4>Temperature Controlled</h4>
                            <p>Our specialized packaging ensures perfect consistency upon arrival.</p>
                        </div>
                    </div>

                    <div class="vcb-delivery-feature">
                        <div class="vcb-delivery-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="vcb-delivery-feature-content">
                            <h4>Metro Area Coverage</h4>
                            <p>We deliver within 25 miles of our bakery. Enter your ZIP code to check eligibility.</p>
                        </div>
                    </div>
                </div>

                <div class="vcb-delivery-actions">
                    <a href="<?= esc($deliveryBtnLink) ?>" class="vcb-btn vcb-btn-primary" data-ts="delivery.btn_text" data-ts-href="delivery.btn_link">
                        <?= esc($deliveryBtnText) ?>
                    </a>
                    <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '+1-555-123-4567'))) ?>" class="vcb-delivery-phone">
                        <i class="fas fa-phone"></i>
                        <span data-ts="header.phone"><?= esc(theme_get('header.phone', '+1 (555) 123-4567')) ?></span>
                    </a>
                </div>
            </div>

            <div class="vcb-delivery-visual" data-animate>
                <div class="vcb-delivery-img">
                    <img src="<?= esc($deliveryImage) ?>" alt="Delivery van with Velvet Crumb branding" loading="lazy">
                </div>
                <div class="vcb-delivery-note">
                    <i class="fas fa-info-circle"></i>
                    <p>Free delivery on orders over $100 within our primary service area.</p>
                </div>
            </div>
        </div>
    </div>
</section>
