<?php
$servicesLabel = theme_get('services.label', 'What We Offer');
$servicesTitle = theme_get('services.title', 'Comprehensive Landscape Services');
$servicesDesc = theme_get('services.description', 'From initial design to final installation, we handle every aspect of your outdoor transformation with unmatched craftsmanship and attention to detail.');
?>
<section class="tf-section tf-services" id="services">
    <div class="container">
        <div class="tf-section-header" data-animate>
            <span class="tf-section-label" data-ts="services.label"><?= esc($servicesLabel) ?></span>
            <div class="tf-section-divider"></div>
            <h2 class="tf-section-title" data-ts="services.title"><?= esc($servicesTitle) ?></h2>
            <p class="tf-section-desc" data-ts="services.description"><?= esc($servicesDesc) ?></p>
        </div>
        <div class="tf-services-grid">
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-home"></i>
                </div>
                <h3 class="tf-service-card-title">Patios & Walkways</h3>
                <p class="tf-service-card-desc">Custom-designed outdoor living spaces using pavers, natural stone, or concrete for durability and style.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> Paver Installation</li>
                    <li><i class="fas fa-check"></i> Stamped Concrete</li>
                    <li><i class="fas fa-check"></i> Natural Stone</li>
                </ul>
            </div>
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h3 class="tf-service-card-title">Driveways</h3>
                <p class="tf-service-card-desc">Durable, attractive driveways that enhance curb appeal and withstand heavy vehicle traffic.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> Permeable Pavers</li>
                    <li><i class="fas fa-check"></i> Asphalt & Concrete</li>
                    <li><i class="fas fa-check"></i> Decorative Borders</li>
                </ul>
            </div>
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="tf-service-card-title">Retaining Walls</h3>
                <p class="tf-service-card-desc">Structural solutions for erosion control, terracing, and creating usable space on sloped properties.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> Segmental Block Walls</li>
                    <li><i class="fas fa-check"></i> Natural Stone Walls</li>
                    <li><i class="fas fa-check"></i> Timber Retaining</li>
                </ul>
            </div>
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-border-style"></i>
                </div>
                <h3 class="tf-service-card-title">Fencing</h3>
                <p class="tf-service-card-desc">Privacy, security, and aesthetic fencing solutions using wood, vinyl, aluminum, or composite materials.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> Privacy Fences</li>
                    <li><i class="fas fa-check"></i> Decorative Iron</li>
                    <li><i class="fas fa-check"></i> Custom Gates</li>
                </ul>
            </div>
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 class="tf-service-card-title">Artificial Grass</h3>
                <p class="tf-service-card-desc">Low-maintenance, lush green lawns year-round with premium synthetic turf systems.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> Pet-Friendly Turf</li>
                    <li><i class="fas fa-check"></i> Putting Greens</li>
                    <li><i class="fas fa-check"></i> Play Areas</li>
                </ul>
            </div>
            <div class="tf-service-card" data-animate>
                <div class="tf-service-card-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h3 class="tf-service-card-title">Complete Design</h3>
                <p class="tf-service-card-desc">End-to-end landscape design and installation, transforming your vision into a cohesive outdoor environment.</p>
                <ul class="tf-service-card-features">
                    <li><i class="fas fa-check"></i> 3D Renderings</li>
                    <li><i class="fas fa-check"></i> Lighting Design</li>
                    <li><i class="fas fa-check"></i> Irrigation Systems</li>
                </ul>
            </div>
        </div>
        <div class="tf-services-cta" data-animate>
            <a href="/services" class="tf-btn tf-btn-primary">
                View All Services
                <i class="fas fa-arrow-right tf-btn-icon"></i>
            </a>
        </div>
    </div>
</section>
