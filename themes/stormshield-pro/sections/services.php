<?php
$servicesLabel = theme_get('services.label', 'What We Do');
$servicesTitle = theme_get('services.title', 'Emergency Roofing Services');
$servicesDesc = theme_get('services.description', 'When disaster strikes, our expert team responds fast with comprehensive solutions to protect your home from further damage.');

$services = [
    [
        'icon' => 'fa-cloud-showers-heavy',
        'title' => theme_get('services.item1_title', 'Storm Damage Repair'),
        'desc' => theme_get('services.item1_desc', 'Hail, wind, and debris damage repaired quickly to prevent water intrusion and structural issues.'),
        'ts_title' => 'services.item1_title',
        'ts_desc' => 'services.item1_desc'
    ],
    [
        'icon' => 'fa-tint',
        'title' => theme_get('services.item2_title', 'Leak Detection'),
        'desc' => theme_get('services.item2_desc', 'Advanced moisture detection technology pinpoints leaks fast, saving you from costly water damage.'),
        'ts_title' => 'services.item2_title',
        'ts_desc' => 'services.item2_desc'
    ],
    [
        'icon' => 'fa-th-large',
        'title' => theme_get('services.item3_title', 'Missing Tile Replacement'),
        'desc' => theme_get('services.item3_desc', 'Swift replacement of blown-off or damaged tiles and shingles to restore your roof\'s integrity.'),
        'ts_title' => 'services.item3_title',
        'ts_desc' => 'services.item3_desc'
    ],
    [
        'icon' => 'fa-umbrella',
        'title' => theme_get('services.item4_title', 'Temporary Weatherproofing'),
        'desc' => theme_get('services.item4_desc', 'Emergency tarping and sealing to protect your home until permanent repairs can be completed.'),
        'ts_title' => 'services.item4_title',
        'ts_desc' => 'services.item4_desc'
    ],
    [
        'icon' => 'fa-search',
        'title' => theme_get('services.item5_title', 'Post-Storm Inspections'),
        'desc' => theme_get('services.item5_desc', 'Thorough damage assessments and documentation for insurance claims after severe weather.'),
        'ts_title' => 'services.item5_title',
        'ts_desc' => 'services.item5_desc'
    ],
    [
        'icon' => 'fa-file-invoice-dollar',
        'title' => theme_get('services.item6_title', 'Insurance Assistance'),
        'desc' => theme_get('services.item6_desc', 'We work directly with your insurance company to streamline the claims process.'),
        'ts_title' => 'services.item6_title',
        'ts_desc' => 'services.item6_desc'
    ]
];
?>
<section class="ssp-services" id="services">
    <div class="ssp-services-container">
        <div class="ssp-section-header" data-animate>
            <span class="ssp-section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="ssp-section-divider">
                <span></span>
                <i class="fas fa-hard-hat"></i>
                <span></span>
            </div>
            <h2 class="ssp-section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="ssp-section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        
        <div class="ssp-services-grid">
            <?php foreach ($services as $index => $service): ?>
            <div class="ssp-service-card" data-animate style="--delay: <?= $index * 0.1 ?>s;">
                <div class="ssp-service-icon">
                    <i class="fas <?= $service['icon'] ?>"></i>
                </div>
                <h3 class="ssp-service-title" data-ts="<?= $service['ts_title'] ?>"><?= esc($service['title']) ?></h3>
                <p class="ssp-service-desc" data-ts="<?= $service['ts_desc'] ?>"><?= esc($service['desc']) ?></p>
                <a href="/services" class="ssp-service-link">
                    Learn More <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
