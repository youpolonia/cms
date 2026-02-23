<?php
$galleryLabel = theme_get('gallery.label', 'The Space');
$galleryTitle = theme_get('gallery.title', 'A Place to Breathe');
$galleryDesc = theme_get('gallery.description', 'Clean lines, natural light, and the aroma of freshly ground beans.');
$galleryBtnText = theme_get('gallery.btn_text', 'View Full Gallery');
$galleryBtnLink = theme_get('gallery.btn_link', '/gallery');
?>
<section class="section gallery-section" id="gallery">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="gallery.label"><?= esc($galleryLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="gallery.title"><?= esc($galleryTitle) ?></h2>
            <p class="section-desc" data-ts="gallery.description"><?= esc($galleryDesc) ?></p>
        </div>
        <div class="gallery-mosaic" data-animate>
            <div class="gallery-item gallery-item-wide">
                <img src="<?= esc($themePath) ?>/assets/gallery-1.jpg" alt="Nordic Brew interior" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="<?= esc($themePath) ?>/assets/gallery-2.jpg" alt="Pour-over brewing" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="<?= esc($themePath) ?>/assets/gallery-3.jpg" alt="Coffee beans" loading="lazy">
            </div>
            <div class="gallery-item gallery-item-tall">
                <img src="<?= esc($themePath) ?>/assets/gallery-4.jpg" alt="Barista at work" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="<?= esc($themePath) ?>/assets/gallery-5.jpg" alt="Pastry display" loading="lazy">
            </div>
            <div class="gallery-item">
                <img src="<?= esc($themePath) ?>/assets/gallery-6.jpg" alt="Latte art" loading="lazy">
            </div>
        </div>
        <div class="gallery-cta" data-animate>
            <a href="<?= esc($galleryBtnLink) ?>" class="btn btn-outline" data-ts="gallery.btn_text" data-ts-href="gallery.btn_link"><?= esc($galleryBtnText) ?> <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</section>
