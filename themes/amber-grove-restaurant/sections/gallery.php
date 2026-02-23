<?php
$galleryLabel = theme_get('gallery.label', 'Ambiance');
$galleryTitle = theme_get('gallery.title', 'Step Into Our World');
$galleryDesc = theme_get('gallery.description', 'A visual preview of the spaces, plates, and moments that define the Amber Grove experience.');
?>
<section class="agr-section agr-section--gallery" id="gallery">
    <div class="container">
        <div class="agr-section-header" data-animate>
            <span class="agr-section-label" data-ts="gallery.label"><?= esc($galleryLabel) ?></span>
            <div class="agr-section-divider"></div>
            <h2 class="agr-section-title" data-ts="gallery.title"><?= esc($galleryTitle) ?></h2>
            <p class="agr-section-desc" data-ts="gallery.description"><?= esc($galleryDesc) ?></p>
        </div>
    </div>
    <div class="agr-gallery-container">
        <div class="agr-gallery-grid">
            <div class="agr-gallery-item agr-gallery-item--large" data-animate>
                <a href="<?= $themePath ?>/assets/gallery/dining-room.jpg" class="agr-gallery-link" data-fslightbox="gallery">
                    <img src="<?= $themePath ?>/assets/gallery/dining-room-thumb.jpg" alt="The main dining room with ambient lighting and walnut tables" loading="lazy">
                    <div class="agr-gallery-overlay">
                        <div class="agr-gallery-caption">
                            <h4>The Dining Room</h4>
                            <span>Evening ambiance</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="agr-gallery-item" data-animate>
                <a href="<?= $themePath ?>/assets/gallery/plating.jpg" class="agr-gallery-link" data-fslightbox="gallery">
                    <img src="<?= $themePath ?>/assets/gallery/plating-thumb.jpg" alt="Artfully plated dish with edible flowers" loading="lazy">
                    <div class="agr-gallery-overlay">
                        <div class="agr-gallery-caption">
                            <h4>Spring Composition</h4>
                            <span>Signature plating</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="agr-gallery-item" data-animate>
                <a href="<?= $themePath ?>/assets/gallery/cellar.jpg" class="agr-gallery-link" data-fslightbox="gallery">
                    <img src="<?= $themePath ?>/assets/gallery/cellar-thumb.jpg" alt="The temperature‑controlled wine cellar" loading="lazy">
                    <div class="agr-gallery-overlay">
                        <div class="agr-gallery-caption">
                            <h4>The Cellar</h4>
                            <span>500+ labels</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="agr-gallery-item" data-animate>
                <a href="<?= $themePath ?>/assets/gallery/chef-table.jpg" class="agr-gallery-link" data-fslightbox="gallery">
                    <img src="<?= $themePath ?>/assets/gallery/chef-table-thumb.jpg" alt="The chef's counter overlooking the kitchen" loading="lazy">
                    <div class="agr-gallery-overlay">
                        <div class="agr-gallery-caption">
                            <h4>Chef’s Counter</h4>
                            <span>Interactive experience</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="agr-gallery-item" data-animate>
                <a href="<?= $themePath ?>/assets/gallery/cocktail.jpg" class="agr-gallery-link" data-fslightbox="gallery">
                    <img src="<?= $themePath ?>/assets/gallery/cocktail-thumb.jpg" alt="Crafted cocktail with smoked glass presentation" loading="lazy">
                    <div class="agr-gallery-overlay">
                        <div class="agr-gallery-caption">
                            <h4>Bar Program</h4>
                            <span>Seasonal cocktails</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    <div class="container agr-gallery-footer" data-animate>
        <a href="/gallery" class="agr-btn agr-btn--outline">
            <i class="fas fa-images"></i> Explore Full Gallery
        </a>
    </div>
</section>
