<?php
$galleryLabel = theme_get('gallery.label', 'Visual Journey');
$galleryTitle = theme_get('gallery.title', 'Moments of Artistry');
$galleryDesc = theme_get('gallery.description', 'Each dish is a fleeting masterpiece, composed with the precision of a calligrapher and the soul of a poet. Witness the beauty of our craft.');
?>
<section class="kno-section kno-gallery-section" id="gallery">
    <div class="container">
        <div class="kno-section-header kno-section-header-center" data-animate>
            <span class="kno-section-label" data-ts="gallery.label"><?= esc($galleryLabel) ?></span>
            <div class="kno-section-divider"></div>
            <h2 class="kno-section-title" data-ts="gallery.title"><?= esc($galleryTitle) ?></h2>
            <p class="kno-section-desc" data-ts="gallery.description"><?= esc($galleryDesc) ?></p>
        </div>

        <div class="kno-gallery-mosaic" data-animate>
            <div class="kno-gallery-item kno-gallery-item-large">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-1.jpg" alt="Omakase dish presentation" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Toro Nigiri</span>
                </div>
            </div>

            <div class="kno-gallery-item">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-2.jpg" alt="Chef preparing sushi" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Master at Work</span>
                </div>
            </div>

            <div class="kno-gallery-item">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-3.jpg" alt="Seasonal sashimi" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Seasonal Selection</span>
                </div>
            </div>

            <div class="kno-gallery-item kno-gallery-item-tall">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-4.jpg" alt="Restaurant interior" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Our Space</span>
                </div>
            </div>

            <div class="kno-gallery-item kno-gallery-item-wide">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-5.jpg" alt="Sake pairing" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Curated Sake</span>
                </div>
            </div>

            <div class="kno-gallery-item">
                <img src="<?= esc($themePath) ?>/assets/images/gallery-6.jpg" alt="Dessert course" loading="lazy">
                <div class="kno-gallery-overlay">
                    <span class="kno-gallery-caption">Sweet Finale</span>
                </div>
            </div>
        </div>
    </div>
</section>
