<?php
$featuresLabel = theme_get('features.label', 'Platform Features');
$featuresTitle = theme_get('features.title', 'Everything You Need to Grow');
$featuresDesc = theme_get('features.description', 'Powerful tools designed specifically for e-commerce success.');
$feature1Title = theme_get('features.feature1_title', 'Real-Time Dashboards');
$feature1Desc = theme_get('features.feature1_desc', 'Monitor your store performance with live data updates. Track sales, traffic, and conversions as they happen.');
$feature2Title = theme_get('features.feature2_title', 'Predictive Analytics');
$feature2Desc = theme_get('features.feature2_desc', 'AI-powered forecasting helps you anticipate trends, optimize inventory, and plan campaigns effectively.');
$feature3Title = theme_get('features.feature3_title', 'One-Click Integrations');
$feature3Desc = theme_get('features.feature3_desc', 'Connect Shopify, WooCommerce, Stripe, and 50+ other tools instantly. No coding required.');
$feature4Title = theme_get('features.feature4_title', 'Customer Segmentation');
$feature4Desc = theme_get('features.feature4_desc', 'Identify your most valuable customers and create targeted campaigns that convert.');
$feature5Title = theme_get('features.feature5_title', 'Revenue Attribution');
$feature5Desc = theme_get('features.feature5_desc', 'Understand exactly which channels and campaigns drive your sales with multi-touch attribution.');
$feature6Title = theme_get('features.feature6_title', 'Custom Reports');
$feature6Desc = theme_get('features.feature6_desc', 'Build and share beautiful reports with your team. Export to PDF, schedule automated delivery.');
?>
<section class="ea-features" id="features">
    <div class="ea-features-container">
        <div class="ea-features-header" data-animate>
            <span class="ea-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <h2 class="ea-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="ea-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        <div class="ea-features-grid">
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature1_title"><?= esc($feature1Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature1_desc"><?= esc($feature1Desc) ?></p>
            </div>
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature2_title"><?= esc($feature2Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature2_desc"><?= esc($feature2Desc) ?></p>
            </div>
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-plug"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature3_title"><?= esc($feature3Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature3_desc"><?= esc($feature3Desc) ?></p>
            </div>
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature4_title"><?= esc($feature4Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature4_desc"><?= esc($feature4Desc) ?></p>
            </div>
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-route"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature5_title"><?= esc($feature5Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature5_desc"><?= esc($feature5Desc) ?></p>
            </div>
            <div class="ea-feature-card" data-animate>
                <div class="ea-feature-icon">
                    <i class="fas fa-file-chart-line"></i>
                </div>
                <h3 class="ea-feature-title" data-ts="features.feature6_title"><?= esc($feature6Title) ?></h3>
                <p class="ea-feature-desc" data-ts="features.feature6_desc"><?= esc($feature6Desc) ?></p>
            </div>
        </div>
    </div>
</section>
