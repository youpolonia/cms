<?php
$servicesLabel = theme_get('services.label', 'What We Do');
$servicesTitle = theme_get('services.title', 'Comprehensive Paving & Groundwork Services');
$servicesDesc = theme_get('services.description', 'From initial groundworks to the finishing touches, we deliver complete solutions for all your outdoor surfacing needs.');

$services = [
    [
        'icon' => 'fas fa-th-large',
        'title' => theme_get('services.service1_title', 'Block Paving'),
        'desc' => theme_get('services.service1_desc', 'Beautiful, durable block paving for driveways, patios, and pathways. Wide range of styles and patterns available.'),
        'ts_title' => 'services.service1_title',
        'ts_desc' => 'services.service1_desc'
    ],
    [
        'icon' => 'fas fa-road',
        'title' => theme_get('services.service2_title', 'Asphalt Driveways'),
        'desc' => theme_get('services.service2_desc', 'Professional tarmac and asphalt installations. Perfect for larger driveways and commercial applications.'),
        'ts_title' => 'services.service2_title',
        'ts_desc' => 'services.service2_desc'
    ],
    [
        'icon' => 'fas fa-hard-hat',
        'title' => theme_get('services.service3_title', 'Groundworks'),
        'desc' => theme_get('services.service3_desc', 'Excavation, drainage, foundations, and site preparation. The essential first step for any construction project.'),
        'ts_title' => 'services.service3_title',
        'ts_desc' => 'services.service3_desc'
    ],
    [
        'icon' => 'fas fa-border-all',
        'title' => theme_get('services.service4_title', 'Patios'),
        'desc' => theme_get('services.service4_desc', 'Create your perfect outdoor living space with custom patios using natural stone, porcelain, or concrete slabs.'),
        'ts_title' => 'services.service4_title',
        'ts_desc' => 'services.service4_desc'
    ],
    [
        'icon' => 'fas fa-leaf',
        'title' => theme_get('services.service5_title', 'Landscaping'),
        'desc' => theme_get('services.service5_desc', 'Complete garden transformations including turfing, fencing, retaining walls, and decorative features.'),
        'ts_title' => 'services.service5_title',
        'ts_desc' => 'services.service5_desc'
    ],
    [
        'icon' => 'fas fa-building',
        'title' => theme_get('services.service6_title', 'Commercial Projects'),
        'desc' => theme_get('services.service6_desc', 'Large-scale paving solutions for car parks, industrial yards, retail spaces, and public areas.'),
        'ts_title' => 'services.service6_title',
        'ts_desc' => 'services.service6_desc'
    ]
];
?>
<section class="section services-section" id="services">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <?php foreach ($services as $index => $service): ?>
            <div class="service-card" data-animate style="--delay: <?= $index * 0.1 ?>s">
                <div class="service-icon">
                    <i class="<?= $service['icon'] ?>"></i>
                </div>
                <div class="service-content">
                    <h3 class="service-title" data-ts="<?= $service['ts_title'] ?>"><?= esc($service['title']) ?></h3>
                    <p class="service-desc" data-ts="<?= $service['ts_desc'] ?>"><?= esc($service['desc']) ?></p>
                </div>
                <div class="service-number"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>