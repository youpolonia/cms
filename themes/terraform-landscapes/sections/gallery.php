<?php
$galleryLabel = theme_get('gallery.label', 'Our Work');
$galleryTitle = theme_get('gallery.title', 'Recent Projects');
$galleryDesc = theme_get('gallery.description', 'Browse our portfolio of completed landscape transformations showcasing our craftsmanship and design expertise.');
?>
<section class="tf-section tf-gallery" id="gallery">
    <div class="container">
        <div class="tf-section-header" data-animate>
            <span class="tf-section-label" data-ts="gallery.label"><?= esc($galleryLabel) ?></span>
            <div class="tf-section-divider"></div>
            <h2 class="tf-section-title" data-ts="gallery.title"><?= esc($galleryTitle) ?></h2>
            <p class="tf-section-desc" data-ts="gallery.description"><?= esc($galleryDesc) ?></p>
        </div>
        <div class="tf-gallery-grid">
            <a href="<?= $themePath ?>/assets/gallery-1.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-1-thumb.jpg" alt="Modern patio with fire pit" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Modern Patio & Fire Pit</h4>
                        <span class="tf-gallery-category">Patio Design</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
            <a href="<?= $themePath ?>/assets/gallery-2.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-2-thumb.jpg" alt="Decorative driveway with pavers" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Paver Driveway</h4>
                        <span class="tf-gallery-category">Driveways</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
            <a href="<?= $themePath ?>/assets/gallery-3.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-3-thumb.jpg" alt="Retaining wall with landscaping" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Stone Retaining Wall</h4>
                        <span class="tf-gallery-category">Retaining Walls</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
            <a href="<?= $themePath ?>/assets/gallery-4.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-4-thumb.jpg" alt="Privacy fencing with gate" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Custom Privacy Fence</h4>
                        <span class="tf-gallery-category">Fencing</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
            <a href="<?= $themePath ?>/assets/gallery-5.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-5-thumb.jpg" alt="Artificial grass backyard" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Artificial Grass Lawn</h4>
                        <span class="tf-gallery-category">Artificial Grass</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
            <a href="<?= $themePath ?>/assets/gallery-6.jpg" class="tf-gallery-item" data-animate data-fancybox="gallery">
                <img src="<?= $themePath ?>/assets/gallery-6-thumb.jpg" alt="Complete landscape design" loading="lazy">
                <div class="tf-gallery-overlay">
                    <div class="tf-gallery-info">
                        <h4 class="tf-gallery-title">Complete Outdoor Makeover</h4>
                        <span class="tf-gallery-category">Full Design</span>
                    </div>
                    <i class="fas fa-expand tf-gallery-icon"></i>
                </div>
            </a>
        </div>
        <div class="tf-gallery-cta" data-animate>
            <a href="/gallery" class="tf-btn tf-btn-secondary">
                View Full Portfolio
                <i class="fas fa-images tf-btn-icon"></i>
            </a>
            <a href="#contact" class="tf-btn tf-btn-outline">
                Start Your Project
                <i class="fas fa-pencil-alt tf-btn-icon"></i>
            </a>
        </div>
    </div>
</section>
