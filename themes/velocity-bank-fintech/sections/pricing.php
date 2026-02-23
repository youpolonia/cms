<?php
$pricingLabel = theme_get('pricing.label', 'PRICING PLANS');
$pricingTitle = theme_get('pricing.title', 'Choose Your Financial Freedom');
$pricingDesc = theme_get('pricing.description', 'Transparent pricing designed to scale with your freelance business. No hidden fees, no surprises.');

$plan1Name = theme_get('pricing.plan1_name', 'Starter');
$plan1Price = theme_get('pricing.plan1_price', '$0');
$plan1Period = theme_get('pricing.plan1_period', '/month');
$plan1Desc = theme_get('pricing.plan1_desc', 'Perfect for getting started');
$plan1Feature1 = theme_get('pricing.plan1_feature1', '1 Multi-currency account');
$plan1Feature2 = theme_get('pricing.plan1_feature2', 'Up to 10 invoices/month');
$plan1Feature3 = theme_get('pricing.plan1_feature3', 'Basic expense tracking');
$plan1Feature4 = theme_get('pricing.plan1_feature4', 'Email support');
$plan1BtnText = theme_get('pricing.plan1_btn_text', 'Get Started Free');
$plan1BtnLink = theme_get('pricing.plan1_btn_link', '#contact');

$plan2Name = theme_get('pricing.plan2_name', 'Professional');
$plan2Price = theme_get('pricing.plan2_price', '$19');
$plan2Period = theme_get('pricing.plan2_period', '/month');
$plan2Desc = theme_get('pricing.plan2_desc', 'For growing freelancers');
$plan2Badge = theme_get('pricing.plan2_badge', 'Most Popular');
$plan2Feature1 = theme_get('pricing.plan2_feature1', 'Unlimited multi-currency accounts');
$plan2Feature2 = theme_get('pricing.plan2_feature2', 'Unlimited invoices');
$plan2Feature3 = theme_get('pricing.plan2_feature3', 'AI expense categorization');
$plan2Feature4 = theme_get('pricing.plan2_feature4', 'Real-time insights dashboard');
$plan2Feature5 = theme_get('pricing.plan2_feature5', 'Priority support');
$plan2BtnText = theme_get('pricing.plan2_btn_text', 'Start 14-Day Trial');
$plan2BtnLink = theme_get('pricing.plan2_btn_link', '#contact');

$plan3Name = theme_get('pricing.plan3_name', 'Business');
$plan3Price = theme_get('pricing.plan3_price', '$49');
$plan3Period = theme_get('pricing.plan3_period', '/month');
$plan3Desc = theme_get('pricing.plan3_desc', 'For established professionals');
$plan3Feature1 = theme_get('pricing.plan3_feature1', 'Everything in Professional');
$plan3Feature2 = theme_get('pricing.plan3_feature2', 'Team collaboration (up to 5)');
$plan3Feature3 = theme_get('pricing.plan3_feature3', 'Advanced analytics & reports');
$plan3Feature4 = theme_get('pricing.plan3_feature4', 'API access');
$plan3Feature5 = theme_get('pricing.plan3_feature5', 'Dedicated account manager');
$plan3BtnText = theme_get('pricing.plan3_btn_text', 'Contact Sales');
$plan3BtnLink = theme_get('pricing.plan3_btn_link', '#contact');
?>
<section class="vbf-pricing" id="pricing">
    <div class="vbf-pricing-bg-grid"></div>
    
    <div class="container">
        <div class="vbf-section-header" data-animate>
            <span class="vbf-section-label" data-ts="pricing.label"><?= esc($pricingLabel) ?></span>
            <div class="vbf-section-divider"></div>
            <h2 class="vbf-section-title" data-ts="pricing.title"><?= esc($pricingTitle) ?></h2>
            <p class="vbf-section-desc" data-ts="pricing.description"><?= esc($pricingDesc) ?></p>
        </div>
        
        <div class="vbf-pricing-grid">
            <div class="vbf-pricing-card" data-animate>
                <div class="vbf-pricing-card-header">
                    <h3 class="vbf-pricing-name" data-ts="pricing.plan1_name"><?= esc($plan1Name) ?></h3>
                    <p class="vbf-pricing-desc" data-ts="pricing.plan1_desc"><?= esc($plan1Desc) ?></p>
                </div>
                <div class="vbf-pricing-price">
                    <span class="vbf-pricing-amount" data-ts="pricing.plan1_price"><?= esc($plan1Price) ?></span>
                    <span class="vbf-pricing-period" data-ts="pricing.plan1_period"><?= esc($plan1Period) ?></span>
                </div>
                <ul class="vbf-pricing-features">
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan1_feature1"><?= esc($plan1Feature1) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan1_feature2"><?= esc($plan1Feature2) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan1_feature3"><?= esc($plan1Feature3) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan1_feature4"><?= esc($plan1Feature4) ?></span>
                    </li>
                </ul>
                <a href="<?= esc($plan1BtnLink) ?>" 
                   class="vbf-pricing-btn" 
                   data-ts="pricing.plan1_btn_text" 
                   data-ts-href="pricing.plan1_btn_link">
                    <?= esc($plan1BtnText) ?>
                </a>
            </div>
            
            <div class="vbf-pricing-card vbf-pricing-card--featured" data-animate>
                <div class="vbf-pricing-badge" data-ts="pricing.plan2_badge">
                    <?= esc($plan2Badge) ?>
                </div>
                <div class="vbf-pricing-card-header">
                    <h3 class="vbf-pricing-name" data-ts="pricing.plan2_name"><?= esc($plan2Name) ?></h3>
                    <p class="vbf-pricing-desc" data-ts="pricing.plan2_desc"><?= esc($plan2Desc) ?></p>
                </div>
                <div class="vbf-pricing-price">
                    <span class="vbf-pricing-amount" data-ts="pricing.plan2_price"><?= esc($plan2Price) ?></span>
                    <span class="vbf-pricing-period" data-ts="pricing.plan2_period"><?= esc($plan2Period) ?></span>
                </div>
                <ul class="vbf-pricing-features">
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan2_feature1"><?= esc($plan2Feature1) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan2_feature2"><?= esc($plan2Feature2) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan2_feature3"><?= esc($plan2Feature3) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan2_feature4"><?= esc($plan2Feature4) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan2_feature5"><?= esc($plan2Feature5) ?></span>
                    </li>
                </ul>
                <a href="<?= esc($plan2BtnLink) ?>" 
                   class="vbf-pricing-btn vbf-pricing-btn--primary" 
                   data-ts="pricing.plan2_btn_text" 
                   data-ts-href="pricing.plan2_btn_link">
                    <?= esc($plan2BtnText) ?>
                </a>
                <div class="vbf-pricing-card-glow"></div>
            </div>
            
            <div class="vbf-pricing-card" data-animate>
                <div class="vbf-pricing-card-header">
                    <h3 class="vbf-pricing-name" data-ts="pricing.plan3_name"><?= esc($plan3Name) ?></h3>
                    <p class="vbf-pricing-desc" data-ts="pricing.plan3_desc"><?= esc($plan3Desc) ?></p>
                </div>
                <div class="vbf-pricing-price">
                    <span class="vbf-pricing-amount" data-ts="pricing.plan3_price"><?= esc($plan3Price) ?></span>
                    <span class="vbf-pricing-period" data-ts="pricing.plan3_period"><?= esc($plan3Period) ?></span>
                </div>
                <ul class="vbf-pricing-features">
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan3_feature1"><?= esc($plan3Feature1) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan3_feature2"><?= esc($plan3Feature2) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan3_feature3"><?= esc($plan3Feature3) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan3_feature4"><?= esc($plan3Feature4) ?></span>
                    </li>
                    <li>
                        <i class="fas fa-check"></i>
                        <span data-ts="pricing.plan3_feature5"><?= esc($plan3Feature5) ?></span>
                    </li>
                </ul>
                <a href="<?= esc($plan3BtnLink) ?>" 
                   class="vbf-pricing-btn" 
                   data-ts="pricing.plan3_btn_text" 
                   data-ts-href="pricing.plan3_btn_link">
                    <?= esc($plan3BtnText) ?>
                </a>
            </div>
        </div>
        
        <div class="vbf-pricing-note" data-animate>
            <i class="fas fa-info-circle"></i>
            <p>All plans include 14-day free trial. No credit card required. Cancel anytime.</p>
        </div>
    </div>
</section>
