<?php
$artexhibitionsLabel = theme_get('artexhibitions.label', 'ROTATING GALLERY');
$artexhibitionsTitle = theme_get('artexhibitions.title', 'Where Art Meets Architecture');
$artexhibitionsDesc = theme_get('artexhibitions.description', 'Curated exhibitions from emerging digital artists and established creators. The hotel itself is a canvas—constantly evolving.');
$artexhibitionsBtnText = theme_get('artexhibitions.btn_text', 'View Current Exhibition');
$artexhibitionsBtnLink = theme_get('artexhibitions.btn_link', '#');
$images = [
    'https://images.pexels.com/photos/30370493/pexels-photo-30370493.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/31533422/pexels-photo-31533422.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/27429704/pexels-photo-27429704.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/4628181/pexels-photo-4628181.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14036450/pexels-photo-14036450.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/6130048/pexels-photo-6130048.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="nhr-section nhr-section-artexhibitions" id="artexhibitions">
    <div class="container">
        <div class="nhr-section-header" data-animate>
            <span class="nhr-section-label" data-ts="artexhibitions.label"><?= esc($artexhibitionsLabel) ?></span>
            <div class="nhr-section-divider"></div>
            <h2 class="nhr-section-title" data-ts="artexhibitions.title"><?= esc($artexhibitionsTitle) ?></h2>
            <p class="nhr-section-desc" data-ts="artexhibitions.description"><?= esc($artexhibitionsDesc) ?></p>
        </div>

        <div class="nhr-art-masonry">
            <?php foreach ($images as $index => $img): ?>
                <div class="nhr-art-item" data-animate>
                    <div class="nhr-art-frame">
                        <img src="<?= esc($img) ?>" alt="Art exhibition piece <?= $index + 1 ?>" loading="lazy">
                        <div class="nhr-art-overlay">
                            <div class="nhr-art-info">
                                <h4 class="nhr-art-title">Digital Horizon <?= $index + 1 ?></h4>
                                <p class="nhr-art-artist">by Studio Pulse</p>
                                <span class="nhr-art-medium">Interactive LED</span>
                            </div>
                            <button class="nhr-art-view" aria-label="View details">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="nhr-art-featured">
            <div class="nhr-art-featured-content" data-animate>
                <h3 class="nhr-art-featured-title">Current Spotlight: <em>Neon Memories</em></h3>
                <p class="nhr-art-featured-desc">An immersive installation by Rafael Chen exploring urban nostalgia through light and sound. Located in the main atrium through June.</p>
                <div class="nhr-art-featured-meta">
                    <div class="nhr-art-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>April 15 – June 30</span>
                    </div>
                    <div class="nhr-art-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Main Atrium & Lobby</span>
                    </div>
                    <div class="nhr-art-meta-item">
                        <i class="fas fa-clock"></i>
                        <span>24/7 Access</span>
                    </div>
                </div>
                <a href="<?= esc($artexhibitionsBtnLink) ?>" class="nhr-btn nhr-btn-outline" data-ts="artexhibitions.btn_text" data-ts-href="artexhibitions.btn_link">
                    <?= esc($artexhibitionsBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="nhr-art-featured-visual" data-animate>
                <div class="nhr-art-featured-image">
                    <img src="<?= esc($images[0]) ?>" alt="Serene outdoor pool with lush greenery in a luxury resort, Arusha, Tanzania." loading="lazy">
                    <div class="nhr-art-featured-badge">Now Showing</div>
                </div>
            </div>
        </div>
    </div>
</section>
