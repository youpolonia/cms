<?php
$servicesLabel = theme_get('services.label', 'What We Offer');
$servicesTitle = theme_get('services.title', 'Everything You Need to Climb Higher');
$servicesDesc = theme_get('services.description', 'From beginner-friendly auto-belay walls to advanced training boards, we\'ve got the facilities and expertise to help you progress.');

$service1Icon = theme_get('services.item1_icon', 'fas fa-mountain');
$service1Title = theme_get('services.item1_title', 'Bouldering Walls');
$service1Desc = theme_get('services.item1_desc', 'Over 50 routes across all difficulty levels, reset weekly by our expert route setters.');

$service2Icon = theme_get('services.item2_icon', 'fas fa-shield-alt');
$service2Title = theme_get('services.item2_title', 'Auto-Belay Systems');
$service2Desc = theme_get('services.item2_desc', '12 auto-belay walls for solo climbing sessions. Perfect for training without a partner.');

$service3Icon = theme_get('services.item3_icon', 'fas fa-dumbbell');
$service3Title = theme_get('services.item3_title', 'Training Boards');
$service3Desc = theme_get('services.item3_desc', 'Hangboards, campus boards, and system walls to build finger strength and technique.');

$service4Icon = theme_get('services.item4_icon', 'fas fa-child');
$service4Title = theme_get('services.item4_title', 'Youth Programs');
$service4Desc = theme_get('services.item4_desc', 'Structured climbing classes for kids aged 5-17. Build confidence and coordination.');

$service5Icon = theme_get('services.item5_icon', 'fas fa-blender');
$service5Title = theme_get('services.item5_title', 'Protein Café');
$service5Desc = theme_get('services.item5_desc', 'Fuel your climb with smoothies, protein shakes, healthy snacks, and premium coffee.');

$service6Icon = theme_get('services.item6_icon', 'fas fa-user-friends');
$service6Title = theme_get('services.item6_title', 'Personal Coaching');
$service6Desc = theme_get('services.item6_desc', 'One-on-one sessions with certified climbing coaches to fast-track your progress.');
?>
<section class="sp-services" id="services">
    <div class="sp-services-container">
        <div class="sp-services-header" data-animate>
            <span class="sp-section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="sp-section-divider"></div>
            <h2 class="sp-section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="sp-section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        
        <div class="sp-services-grid">
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service1Icon) ?>" data-ts="services.item1_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item1_title"><?= esc($service1Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item1_desc"><?= esc($service1Desc) ?></p>
            </div>
            
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service2Icon) ?>" data-ts="services.item2_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item2_title"><?= esc($service2Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item2_desc"><?= esc($service2Desc) ?></p>
            </div>
            
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service3Icon) ?>" data-ts="services.item3_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item3_title"><?= esc($service3Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item3_desc"><?= esc($service3Desc) ?></p>
            </div>
            
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service4Icon) ?>" data-ts="services.item4_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item4_title"><?= esc($service4Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item4_desc"><?= esc($service4Desc) ?></p>
            </div>
            
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service5Icon) ?>" data-ts="services.item5_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item5_title"><?= esc($service5Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item5_desc"><?= esc($service5Desc) ?></p>
            </div>
            
            <div class="sp-service-card" data-animate>
                <div class="sp-service-icon">
                    <i class="<?= esc($service6Icon) ?>" data-ts="services.item6_icon"></i>
                </div>
                <h3 class="sp-service-title" data-ts="services.item6_title"><?= esc($service6Title) ?></h3>
                <p class="sp-service-desc" data-ts="services.item6_desc"><?= esc($service6Desc) ?></p>
            </div>
        </div>
    </div>
</section>
