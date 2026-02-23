<?php
$featuresLabel = theme_get('features.label', 'PLATFORM FEATURES');
$featuresTitle = theme_get('features.title', 'Everything You Need to Manage Freelance Finances');
$featuresDesc = theme_get('features.description', 'Powerful AI-driven tools designed specifically for the needs of independent professionals and digital nomads.');

$feature1Icon = theme_get('features.feature1_icon', 'fa-file-invoice-dollar');
$feature1Title = theme_get('features.feature1_title', 'Instant Invoicing');
$feature1Desc = theme_get('features.feature1_desc', 'Generate professional invoices in seconds with customizable templates. Support for multiple currencies and automatic payment tracking.');

$feature2Icon = theme_get('features.feature2_icon', 'fa-globe');
$feature2Title = theme_get('features.feature2_title', 'Multi-Currency Accounts');
$feature2Desc = theme_get('features.feature2_desc', 'Hold and transact in 120+ currencies with real-time exchange rates. Perfect for international freelancers working across borders.');

$feature3Icon = theme_get('features.feature3_icon', 'fa-brain');
$feature3Title = theme_get('features.feature3_title', 'AI Expense Categorization');
$feature3Desc = theme_get('features.feature3_desc', 'Let machine learning automatically categorize your expenses. Smart pattern recognition learns your business habits over time.');

$feature4Icon = theme_get('features.feature4_icon', 'fa-chart-pie');
$feature4Title = theme_get('features.feature4_title', 'Real-Time Analytics');
$feature4Desc = theme_get('features.feature4_desc', 'Visual dashboards show your cash flow, profit margins, and spending patterns at a glance. Make data-driven financial decisions.');

$feature5Icon = theme_get('features.feature5_icon', 'fa-shield-alt');
$feature5Title = theme_get('features.feature5_title', 'Bank-Level Security');
$feature5Desc = theme_get('features.feature5_desc', '256-bit encryption, two-factor authentication, and SOC 2 compliance. Your financial data is protected with enterprise-grade security.');

$feature6Icon = theme_get('features.feature6_icon', 'fa-mobile-alt');
$feature6Title = theme_get('features.feature6_title', 'Mobile-First Design');
$feature6Desc = theme_get('features.feature6_desc', 'Manage your finances on the go with our native iOS and Android apps. Capture receipts, track expenses, and send invoices from anywhere.');
?>
<section class="vbf-features" id="features">
    <div class="vbf-features-bg-accent"></div>
    
    <div class="container">
        <div class="vbf-section-header" data-animate>
            <span class="vbf-section-label" data-ts="features.label"><?= esc($featuresLabel) ?></span>
            <div class="vbf-section-divider"></div>
            <h2 class="vbf-section-title" data-ts="features.title"><?= esc($featuresTitle) ?></h2>
            <p class="vbf-section-desc" data-ts="features.description"><?= esc($featuresDesc) ?></p>
        </div>
        
        <div class="vbf-features-grid">
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature1Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature1_title"><?= esc($feature1Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature1_desc"><?= esc($feature1Desc) ?></p>
            </div>
            
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature2Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature2_title"><?= esc($feature2Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature2_desc"><?= esc($feature2Desc) ?></p>
            </div>
            
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature3Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature3_title"><?= esc($feature3Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature3_desc"><?= esc($feature3Desc) ?></p>
            </div>
            
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature4Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature4_title"><?= esc($feature4Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature4_desc"><?= esc($feature4Desc) ?></p>
            </div>
            
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature5Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature5_title"><?= esc($feature5Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature5_desc"><?= esc($feature5Desc) ?></p>
            </div>
            
            <div class="vbf-feature-card" data-animate>
                <div class="vbf-feature-icon-wrap">
                    <div class="vbf-feature-icon">
                        <i class="fas <?= esc($feature6Icon) ?>"></i>
                    </div>
                    <div class="vbf-feature-icon-glow"></div>
                </div>
                <h3 class="vbf-feature-title" data-ts="features.feature6_title"><?= esc($feature6Title) ?></h3>
                <p class="vbf-feature-desc" data-ts="features.feature6_desc"><?= esc($feature6Desc) ?></p>
            </div>
        </div>
    </div>
</section>
