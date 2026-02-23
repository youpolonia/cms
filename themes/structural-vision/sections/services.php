<?php
$servicesLabel = theme_get('services.label', 'What We Do');
$servicesTitle = theme_get('services.title', 'Professional Paving Services');
$servicesDesc = theme_get('services.description', 'Comprehensive paving and groundwork solutions tailored to your needs');

$servicesList = [
    [
        'icon' => 'fa-th-large',
        'title' => 'Block Paving',
        'desc' => 'Beautiful, durable block paving for driveways, patios and pathways. Wide selection of patterns and colours.'
    ],
    [
        'icon' => 'fa-road',
        'title' => 'Asphalt Driveways',
        'desc' => 'Professional tarmac and asphalt installations. Cost-effective, hard-wearing surfaces for all applications.'
    ],
    [
        'icon' => 'fa-layer-group',
        'title' => 'Groundworks',
        'desc' => 'Complete groundwork services including excavation, drainage, and foundations for any project scale.'
    ],
    [
        'icon' => 'fa-border-all',
        'title' => 'Patios',
        'desc' => 'Stunning patio designs using natural stone, porcelain, and concrete. Transform your outdoor living space.'
    ],
    [
        'icon' => 'fa-leaf',
        'title' => 'Landscaping',
        'desc' => 'Full landscaping services to complement your paving. Turf, planting beds, and decorative features.'
    ],
    [
        'icon' => 'fa-wrench',
        'title' => 'Repairs & Maintenance',
        'desc' => 'Expert repair and maintenance services to keep your surfaces looking their best for years to come.'
    ]
];
?>
<section class="section services-section" id="services">
    <div class="services-pattern"></div>
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="services.label">
                <i class="fas fa-tools"></i>
                <?= esc($servicesLabel) ?>
            </span>
            <h2 class="section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="services-grid">
            <?php foreach ($servicesList as $index => $service): ?>
            <div class="service-card" data-animate style="--delay: <?= $index * 0.1 ?>s">
                <div class="service-icon">
                    <i class="fas <?= $service['icon'] ?>"></i>
                </div>
                <h3 class="service-title"><?= esc($service['title']) ?></h3>
                <p class="service-desc"><?= esc($service['desc']) ?></p>
                <a href="#contact" class="service-link">
                    Get Quote <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
