<?php
$galleryLabel = theme_get('gallery.label', 'Visual Journey');
$galleryTitle = theme_get('gallery.title', 'Moments of Serenity Captured');
$galleryDesc = theme_get('gallery.description', 'Step into our world where every detail reflects harmony, beauty, and the sacred art of tea.');

$gallery1 = theme_get('gallery.image1', $themePath . '/assets/gallery-1.jpg');
$gallery2 = theme_get('gallery.image2', $themePath . '/assets/gallery-2.jpg');
$gallery3 = theme_get('gallery.image3', $themePath . '/assets/gallery-3.jpg');
$gallery4 = theme_get('gallery.image4', $themePath . '/assets/gallery-4.jpg');
$gallery5 = theme_get('gallery.image5', $themePath . '/assets/gallery-5.jpg');
$gallery6 = theme_get('gallery.image6', $themePath . '/assets/gallery-6.jpg');
?>
<section class="ch-gallery" id="gallery">
    <div class="container">
        <div class="ch-section-header ch-section-header-center">
            <span class="ch-section-label" data-ts="gallery.label" data-animate>
                <i class="fas fa-camera"></i>
                <?= esc($galleryLabel) ?>
            </span>
            <div class="ch-section-divider ch-section-divider-center"></div>
            <h2 class="ch-section-title" data-ts="gallery.title" data-animate>
                <?= esc($galleryTitle) ?>
            </h2>
            <p class="ch-section-desc" data-ts="gallery.description" data-animate>
                <?= esc($galleryDesc) ?>
            </p>
        </div>

        <div class="ch-gallery-masonry">
            <div class="ch-gallery-item ch-gallery-item-tall" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image1" style="background-image: url('<?= esc($gallery1) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>

            <div class="ch-gallery-item" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image2" style="background-image: url('<?= esc($gallery2) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>

            <div class="ch-gallery-item ch-gallery-item-wide" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image3" style="background-image: url('<?= esc($gallery3) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>

            <div class="ch-gallery-item" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image4" style="background-image: url('<?= esc($gallery4) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>

            <div class="ch-gallery-item ch-gallery-item-tall" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image5" style="background-image: url('<?= esc($gallery5) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>

            <div class="ch-gallery-item" data-animate>
                <div class="ch-gallery-image" data-ts-bg="gallery.image6" style="background-image: url('<?= esc($gallery6) ?>')">
                    <div class="ch-gallery-overlay">
                        <i class="fas fa-search-plus"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="ch-gallery-cta" data-animate>
            <a href="/gallery" class="ch-btn ch-btn-secondary">
                View Full Gallery
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
