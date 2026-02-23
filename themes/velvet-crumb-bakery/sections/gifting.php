<?php
$giftingLabel = theme_get('gifting.label', 'Corporate & Luxury Gifting');
$giftingTitle = theme_get('gifting.title', 'Elevate Your Corporate Gifting');
$giftingDesc = theme_get('gifting.description', 'Impress clients, reward employees, and celebrate milestones with our bespoke cheesecake gifting program. Custom branding and white-glove delivery available.');
$giftingBtnText = theme_get('gifting.btn_text', 'Request Gifting Catalog');
$giftingBtnLink = theme_get('gifting.btn_link', '#contact');

$giftingImages = [
    'https://images.pexels.com/photos/30846574/pexels-photo-30846574.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/30888593/pexels-photo-30888593.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="vcb-section vcb-gifting" id="gifting">
    <div class="container">
        <div class="vcb-section-header" data-animate>
            <span class="vcb-section-label" data-ts="gifting.label"><?= esc($giftingLabel) ?></span>
            <div class="vcb-section-divider"></div>
            <h2 class="vcb-section-title" data-ts="gifting.title"><?= esc($giftingTitle) ?></h2>
            <p class="vcb-section-desc" data-ts="gifting.description"><?= esc($giftingDesc) ?></p>
        </div>

        <div class="vcb-gifting-tiers">
            <div class="vcb-tier-card" data-animate>
                <div class="vcb-tier-header">
                    <h3 class="vcb-tier-name">Executive</h3>
                    <span class="vcb-tier-price">From $75</span>
                </div>
                <div class="vcb-tier-img">
                    <img src="<?= esc($giftingImages[0]) ?>" alt="Executive gifting box" loading="lazy">
                </div>
                <ul class="vcb-tier-features">
                    <li><i class="fas fa-check"></i> Premium wooden gift box</li>
                    <li><i class="fas fa-check"></i> Custom flavor selection</li>
                    <li><i class="fas fa-check"></i> Handwritten note</li>
                    <li><i class="fas fa-check"></i> Gold foil branding</li>
                </ul>
                <div class="vcb-tier-cta">
                    <a href="#contact" class="vcb-btn vcb-btn-outline">Inquire</a>
                </div>
            </div>

            <div class="vcb-tier-card vcb-tier-featured" data-animate>
                <div class="vcb-tier-badge">Most Popular</div>
                <div class="vcb-tier-header">
                    <h3 class="vcb-tier-name">Corporate</h3>
                    <span class="vcb-tier-price">From $250</span>
                </div>
                <div class="vcb-tier-img">
                    <img src="<?= esc($giftingImages[1]) ?>" alt="Corporate gifting package" loading="lazy">
                </div>
                <ul class="vcb-tier-features">
                    <li><i class="fas fa-check"></i> 10+ cheesecake assortment</li>
                    <li><i class="fas fa-check"></i> Full custom branding</li>
                    <li><i class="fas fa-check"></i> Priority same-day delivery</li>
                    <li><i class="fas fa-check"></i> Dedicated account manager</li>
                    <li><i class="fas fa-check"></i> Volume discounts</li>
                </ul>
                <div class="vcb-tier-cta">
                    <a href="#contact" class="vcb-btn vcb-btn-primary">Get Quote</a>
                </div>
            </div>

            <div class="vcb-tier-card" data-animate>
                <div class="vcb-tier-header">
                    <h3 class="vcb-tier-name">Luxury Event</h3>
                    <span class="vcb-tier-price">Custom Quote</span>
                </div>
                <div class="vcb-tier-img">
                    <div class="vcb-tier-placeholder">
                        <i class="fas fa-glass-cheers"></i>
                        <span>Custom Designs</span>
                    </div>
                </div>
                <ul class="vcb-tier-features">
                    <li><i class="fas fa-check"></i> Multi-tier cheesecake towers</li>
                    <li><i class="fas fa-check"></i> Wedding & event catering</li>
                    <li><i class="fas fa-check"></i> Full-service setup</li>
                    <li><i class="fas fa-check"></i> Custom flavor development</li>
                </ul>
                <div class="vcb-tier-cta">
                    <a href="#contact" class="vcb-btn vcb-btn-outline">Schedule Consultation</a>
                </div>
            </div>
        </div>

        <div class="vcb-gifting-cta" data-animate>
            <div class="vcb-gifting-cta-content">
                <h3>Ready to impress?</h3>
                <p>Our gifting specialists will create a custom proposal within 24 hours.</p>
            </div>
            <a href="<?= esc($giftingBtnLink) ?>" class="vcb-btn vcb-btn-secondary" data-ts="gifting.btn_text" data-ts-href="gifting.btn_link">
                <?= esc($giftingBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
