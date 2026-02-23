<?php
$pricingLabel = theme_get('pricing.label', 'Transparent Pricing');
$pricingTitle = theme_get('pricing.title', 'Choose Your Perfect Hosting Plan');
$pricingDesc = theme_get('pricing.description', 'All plans include daily backups, staging environments, free SSL, CDN, and 24/7 expert support. Scale as you grow.');

$plan1Name = theme_get('pricing.plan1_name', 'Starter');
$plan1Price = theme_get('pricing.plan1_price', '$29');
$plan1Period = theme_get('pricing.plan1_period', '/month');
$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for small business sites and personal blogs');
$plan1Features = theme_get('pricing.plan1_features', "1 WordPress Site\n25,000 Monthly Visits\n10GB Storage\nDaily Backups\nFree SSL & CDN\nStaging Environment");

$plan2Name = theme_get('pricing.plan2_name', 'Professional');
$plan2Price = theme_get('pricing.plan2_price', '$79');
$plan2Period = theme_get('pricing.plan2_period', '/month');
$plan2Desc = theme_get('pricing.plan2_desc', 'For growing businesses and content-heavy sites');
$plan2Features = theme_get('pricing.plan2_features', "3 WordPress Sites\n100,000 Monthly Visits\n30GB Storage\nDaily Backups\nFree SSL & CDN\nStaging Environment\nPriority Support");
$plan2Highlight = true;

$plan3Name = theme_get('pricing.plan3_name', 'Enterprise');
$plan3Price = theme_get('pricing.plan3_price', '$199');
$plan3Period = theme_get('pricing.plan3_period', '/month');
$plan3Desc = theme_get('pricing.plan3_desc', 'For high-traffic sites requiring maximum performance');
$plan3Features = theme_get('pricing.plan3_features', "10 WordPress Sites\n500,000 Monthly Visits\n100GB Storage\nHourly Backups\nFree SSL & CDN\nMultiple Staging Environments\n24/7 Dedicated Support\nWhite-Label Options");

$pricingBtnText = theme_get('pricing.btn_text', 'View All Plans');
$pricingBtnLink = theme_get('pricing.btn_link', '/pricing');
?>
<section class="vp-section vp-pricing-section" id="pricing">
    <div class="container">
        <div class="vp-section-header" data-animate>
            <span class="vp-section-label" data-ts="pricing.label"><?= esc($pricingLabel) ?></span>
            <div class="vp-section-divider"></div>
            <h2 class="vp-section-title" data-ts="pricing.title"><?= esc($pricingTitle) ?></h2>
            <p class="vp-section-desc" data-ts="pricing.description"><?= esc($pricingDesc) ?></p>
        </div>
        
        <div class="vp-pricing-grid">
            <div class="vp-pricing-card" data-animate>
                <div class="vp-pricing-header">
                    <h3 class="vp-pricing-name" data-ts="pricing.plan1_name"><?= esc($plan1Name) ?></h3>
                    <p class="vp-pricing-desc" data-ts="pricing.plan1_desc"><?= esc($plan1Desc) ?></p>
                    <div class="vp-pricing-price">
                        <span class="vp-price-amount" data-ts="pricing.plan1_price"><?= esc($plan1Price) ?></span>
                        <span class="vp-price-period" data-ts="pricing.plan1_period"><?= esc($plan1Period) ?></span>
                    </div>
                </div>
                <ul class="vp-pricing-features">
                    <?php 
                    $features1 = explode("\n", $plan1Features);
                    foreach ($features1 as $feature): 
                        $feature = trim($feature);
                        if ($feature):
                    ?>
                    <li><i class="fas fa-check"></i> <?= esc($feature) ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
                <a href="#contact" class="vp-btn vp-btn-outline">Get Started</a>
            </div>
            
            <div class="vp-pricing-card vp-pricing-featured" data-animate>
                <div class="vp-pricing-badge">Most Popular</div>
                <div class="vp-pricing-header">
                    <h3 class="vp-pricing-name" data-ts="pricing.plan2_name"><?= esc($plan2Name) ?></h3>
                    <p class="vp-pricing-desc" data-ts="pricing.plan2_desc"><?= esc($plan2Desc) ?></p>
                    <div class="vp-pricing-price">
                        <span class="vp-price-amount" data-ts="pricing.plan2_price"><?= esc($plan2Price) ?></span>
                        <span class="vp-price-period" data-ts="pricing.plan2_period"><?= esc($plan2Period) ?></span>
                    </div>
                </div>
                <ul class="vp-pricing-features">
                    <?php 
                    $features2 = explode("\n", $plan2Features);
                    foreach ($features2 as $feature): 
                        $feature = trim($feature);
                        if ($feature):
                    ?>
                    <li><i class="fas fa-check"></i> <?= esc($feature) ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
                <a href="#contact" class="vp-btn vp-btn-primary">Get Started</a>
            </div>
            
            <div class="vp-pricing-card" data-animate>
                <div class="vp-pricing-header">
                    <h3 class="vp-pricing-name" data-ts="pricing.plan3_name"><?= esc($plan3Name) ?></h3>
                    <p class="vp-pricing-desc" data-ts="pricing.plan3_desc"><?= esc($plan3Desc) ?></p>
                    <div class="vp-pricing-price">
                        <span class="vp-price-amount" data-ts="pricing.plan3_price"><?= esc($plan3Price) ?></span>
                        <span class="vp-price-period" data-ts="pricing.plan3_period"><?= esc($plan3Period) ?></span>
                    </div>
                </div>
                <ul class="vp-pricing-features">
                    <?php 
                    $features3 = explode("\n", $plan3Features);
                    foreach ($features3 as $feature): 
                        $feature = trim($feature);
                        if ($feature):
                    ?>
                    <li><i class="fas fa-check"></i> <?= esc($feature) ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
                <a href="#contact" class="vp-btn vp-btn-outline">Get Started</a>
            </div>
        </div>
        
        <div class="vp-pricing-footer" data-animate>
            <p>Need a custom solution? <a href="<?= esc($pricingBtnLink) ?>" data-ts="pricing.btn_text" data-ts-href="pricing.btn_link"><?= esc($pricingBtnText) ?></a></p>
        </div>
    </div>
</section>
