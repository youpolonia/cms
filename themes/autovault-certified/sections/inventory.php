<?php
// Inventory section variables
$inventoryLabel = theme_get('inventory.label', 'Our Selection');
$inventoryTitle = theme_get('inventory.title', 'Certified Pre-Owned Vehicles');
$inventoryDesc = theme_get('inventory.description', 'Every vehicle undergoes a rigorous 150-point inspection and comes with a comprehensive warranty. Drive with confidence.');
$inventoryBtnText = theme_get('inventory.btn_text', 'View Full Inventory');
$inventoryBtnLink = theme_get('inventory.btn_link', '/inventory');

// Sample vehicle data (in a real CMS, this would come from a database)
$vehicles = [
    [
        'image' => 'https://images.pexels.com/photos/4489766/pexels-photo-4489766.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'alt' => 'Mechanics working on a vintage car restoration in a dimly lit garage, capturing the essence of classic automotive care.',
        'year' => '2022',
        'make' => 'BMW',
        'model' => 'X5',
        'trim' => 'xDrive40i',
        'price' => '$52,990',
        'mileage' => '18,500',
        'features' => ['Certified', 'AWD', 'Premium Package']
    ],
    [
        'image' => 'https://images.pexels.com/photos/7019364/pexels-photo-7019364.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'alt' => 'Mechanic in garage inspecting a red truck to ensure optimal performance.',
        'year' => '2021',
        'make' => 'Ford',
        'model' => 'F-150',
        'trim' => 'Lariat',
        'price' => '$45,750',
        'mileage' => '22,100',
        'features' => ['Certified', '4x4', 'Tow Package']
    ],
    [
        'image' => 'https://images.pexels.com/photos/29198156/pexels-photo-29198156.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'alt' => 'A classic car is elevated on a lift in a professional auto repair garage, showcasing its undercarriage.',
        'year' => '2023',
        'make' => 'Tesla',
        'model' => 'Model 3',
        'trim' => 'Long Range',
        'price' => '$38,900',
        'mileage' => '12,800',
        'features' => ['Certified', 'Electric', 'Autopilot']
    ],
    [
        'image' => 'https://images.pexels.com/photos/4489721/pexels-photo-4489721.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
        'alt' => 'Automobile mechanic fixing a car in a repair garage, focus on service and repair work',
        'year' => '2020',
        'make' => 'Mercedes-Benz',
        'model' => 'GLC 300',
        'trim' => '4MATIC',
        'price' => '$41,200',
        'mileage' => '30,500',
        'features' => ['Certified', 'Luxury', 'Safety Package']
    ]
];
?>
<section class="av-section av-inventory" id="inventory">
    <div class="container">
        <div class="av-section-header" data-animate>
            <span class="av-section-label" data-ts="inventory.label"><?= esc($inventoryLabel) ?></span>
            <div class="av-section-divider"></div>
            <h2 class="av-section-title" data-ts="inventory.title"><?= esc($inventoryTitle) ?></h2>
            <p class="av-section-desc" data-ts="inventory.description"><?= esc($inventoryDesc) ?></p>
        </div>

        <div class="av-inventory-grid">
            <?php foreach ($vehicles as $vehicle): ?>
            <div class="av-vehicle-card" data-animate>
                <div class="av-vehicle-image">
                    <img src="<?= esc($vehicle['image']) ?>" alt="<?= esc($vehicle['alt']) ?>" loading="lazy">
                    <div class="av-vehicle-badge">Certified</div>
                </div>
                <div class="av-vehicle-content">
                    <div class="av-vehicle-header">
                        <h3 class="av-vehicle-title"><?= esc($vehicle['year']) ?> <?= esc($vehicle['make']) ?> <?= esc($vehicle['model']) ?></h3>
                        <span class="av-vehicle-trim"><?= esc($vehicle['trim']) ?></span>
                    </div>
                    <div class="av-vehicle-price"><?= esc($vehicle['price']) ?></div>
                    <div class="av-vehicle-details">
                        <div class="av-vehicle-detail">
                            <i class="fas fa-tachometer-alt"></i>
                            <span><?= esc($vehicle['mileage']) ?> miles</span>
                        </div>
                        <div class="av-vehicle-detail">
                            <i class="fas fa-cog"></i>
                            <span>Auto</span>
                        </div>
                    </div>
                    <div class="av-vehicle-features">
                        <?php foreach ($vehicle['features'] as $feature): ?>
                        <span class="av-vehicle-feature"><?= esc($feature) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <a href="#" class="av-btn av-btn-outline av-btn-block">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="av-section-actions" data-animate>
            <a href="<?= esc($inventoryBtnLink) ?>" class="av-btn av-btn-primary" data-ts="inventory.btn_text" data-ts-href="inventory.btn_link">
                <?= esc($inventoryBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
