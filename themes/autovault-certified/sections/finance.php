<?php
// Finance section variables
$financeLabel = theme_get('finance.label', 'Financing Made Easy');
$financeTitle = theme_get('finance.title', 'Flexible Finance & Warranty Options');
$financeDesc = theme_get('finance.description', 'Get approved quickly with competitive rates. Choose from extended warranty packages and part-exchange your current vehicle.');
$financeBtnText = theme_get('finance.btn_text', 'Apply for Financing');
$financeBtnLink = theme_get('finance.btn_link', '/finance');

// Finance options
$financeOptions = [
    [
        'icon' => 'fas fa-percentage',
        'title' => 'Low APR Financing',
        'description' => 'Rates as low as 3.9% APR for qualified buyers. Flexible terms from 24 to 72 months.',
        'features' => ['Quick approval', 'No hidden fees', 'Online application']
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'title' => 'Extended Warranty',
        'description' => 'Comprehensive coverage up to 7 years/100,000 miles. Includes roadside assistance and rental car coverage.',
        'features' => ['Bumper-to-bumper', 'Transferable', '24/7 support']
    ],
    [
        'icon' => 'fas fa-exchange-alt',
        'title' => 'Part-Exchange',
        'description' => 'Get a fair value for your current vehicle. We handle all paperwork and transfer seamlessly.',
        'features' => ['Free valuation', 'Instant offer', 'We handle paperwork']
    ],
    [
        'icon' => 'fas fa-file-contract',
        'title' => 'Lease Options',
        'description' => 'Low monthly payments with option to purchase at lease end. Maintenance packages available.',
        'features' => ['Low monthly payments', 'Purchase option', 'Maintenance included']
    ]
];

// Stats
$stats = [
    ['value' => '48h', 'label' => 'Average Approval Time'],
    ['value' => '95%', 'label' => 'Customer Satisfaction'],
    ['value' => '150+', 'label' => 'Point Inspection'],
    ['value' => '7yr', 'label' => 'Max Warranty Coverage']
];
?>
<section class="av-section av-finance" id="finance">
    <div class="container">
        <div class="av-section-header" data-animate>
            <span class="av-section-label" data-ts="finance.label"><?= esc($financeLabel) ?></span>
            <div class="av-section-divider"></div>
            <h2 class="av-section-title" data-ts="finance.title"><?= esc($financeTitle) ?></h2>
            <p class="av-section-desc" data-ts="finance.description"><?= esc($financeDesc) ?></p>
        </div>

        <div class="av-finance-grid">
            <?php foreach ($financeOptions as $option): ?>
            <div class="av-finance-card" data-animate>
                <div class="av-finance-icon">
                    <i class="<?= esc($option['icon']) ?>"></i>
                </div>
                <h3 class="av-finance-title"><?= esc($option['title']) ?></h3>
                <p class="av-finance-desc"><?= esc($option['description']) ?></p>
                <ul class="av-finance-features">
                    <?php foreach ($option['features'] as $feature): ?>
                    <li class="av-finance-feature">
                        <i class="fas fa-check"></i>
                        <span><?= esc($feature) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="av-stats-grid" data-animate>
            <?php foreach ($stats as $stat): ?>
            <div class="av-stat-item">
                <div class="av-stat-value"><?= esc($stat['value']) ?></div>
                <div class="av-stat-label"><?= esc($stat['label']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="av-finance-cta" data-animate>
            <div class="av-finance-cta-content">
                <h3 class="av-finance-cta-title">Ready to Drive Your Dream Car?</h3>
                <p class="av-finance-cta-desc">Get a personalized finance quote in minutes with no impact on your credit score.</p>
            </div>
            <div class="av-finance-cta-actions">
                <a href="<?= esc($financeBtnLink) ?>" class="av-btn av-btn-primary av-btn-large" data-ts="finance.btn_text" data-ts-href="finance.btn_link">
                    <?= esc($financeBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', theme_get('header.phone', '1-800-555-1234'))) ?>" class="av-btn av-btn-outline av-btn-large">
                    <i class="fas fa-phone"></i>
                    Call Now
                </a>
            </div>
        </div>
    </div>
</section>
