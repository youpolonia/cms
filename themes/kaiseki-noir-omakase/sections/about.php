<?php
$aboutLabel = theme_get('about.label', 'Our Philosophy');
$aboutTitle = theme_get('about.title', 'The Art of Omakase');
$aboutDesc = theme_get('about.description', 'In the intimate setting of our eight-seat counter, we practice the centuries-old tradition of omakase—"I leave it up to you." This is not merely dining; it is an act of trust between chef and guest, a dialogue spoken through the language of impeccable ingredients, precise technique, and seasonal reverence.');
$aboutImage = theme_get('about.image', $themePath . '/assets/images/about.jpg');
?>
<section class="kno-section kno-about" id="about">
    <div class="container">
        <div class="kno-about-layout">
            <div class="kno-about-visual" data-animate>
                <div class="kno-about-image-wrap">
                    <img src="<?= esc($aboutImage) ?>" 
                         alt="<?= esc($aboutTitle) ?>" 
                         class="kno-about-image"
                         data-ts-bg="about.image"
                         loading="lazy">
                    <div class="kno-about-accent"></div>
                </div>
            </div>

            <div class="kno-about-content">
                <div class="kno-section-header" data-animate>
                    <span class="kno-section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="kno-section-divider"></div>
                    <h2 class="kno-section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                </div>

                <div class="kno-about-body" data-animate>
                    <p class="kno-about-text" data-ts="about.description"><?= esc($aboutDesc) ?></p>

                    <div class="kno-about-features">
                        <div class="kno-feature-item">
                            <div class="kno-feature-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="kno-feature-content">
                                <h4 class="kno-feature-title">Seasonal Mastery</h4>
                                <p class="kno-feature-text">Every ingredient reflects the current season, sourced at peak perfection.</p>
                            </div>
                        </div>

                        <div class="kno-feature-item">
                            <div class="kno-feature-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="kno-feature-content">
                                <h4 class="kno-feature-title">Artisan Craftsmanship</h4>
                                <p class="kno-feature-text">Two decades of training in Tokyo's most revered kitchens.</p>
                            </div>
                        </div>

                        <div class="kno-feature-item">
                            <div class="kno-feature-icon">
                                <i class="fas fa-diamond"></i>
                            </div>
                            <div class="kno-feature-content">
                                <h4 class="kno-feature-title">Intimate Elegance</h4>
                                <p class="kno-feature-text">An exclusive eight-seat experience where every detail matters.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
