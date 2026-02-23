<?php
$aboutLabel = theme_get('about.label', 'Our Story');
$aboutTitle = theme_get('about.title', 'Crafted with Passion, Served with Precision');
$aboutDesc = theme_get('about.description', 'Amber Grove is the vision of Chef Elara Vance, whose Michelin-starred journey across Europe culminates in a dining experience that balances innovation with deep respect for ingredient and tradition.');
$aboutImage = theme_get('about.image', $themePath . '/assets/about-chef.jpg');
?>
<section class="agr-section agr-section--about" id="about">
    <div class="container">
        <div class="agr-about-grid">
            <div class="agr-about-content" data-animate>
                <div class="agr-section-header agr-section-header--left">
                    <span class="agr-section-label" data-ts="about.label"><?= esc($aboutLabel) ?></span>
                    <div class="agr-section-divider"></div>
                    <h2 class="agr-section-title" data-ts="about.title"><?= esc($aboutTitle) ?></h2>
                    <p class="agr-section-desc" data-ts="about.description"><?= esc($aboutDesc) ?></p>
                </div>
                <div class="agr-about-features">
                    <div class="agr-feature">
                        <div class="agr-feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="agr-feature-text">
                            <h4>Michelin‑Starred Chef</h4>
                            <p>15 years of culinary excellence across Paris, Tokyo, and New York.</p>
                        </div>
                    </div>
                    <div class="agr-feature">
                        <div class="agr-feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="agr-feature-text">
                            <h4>Hyper‑Local Sourcing</h4>
                            <p>Ingredients sourced daily from our partner farms within 50 miles.</p>
                        </div>
                    </div>
                    <div class="agr-feature">
                        <div class="agr-feature-icon">
                            <i class="fas fa-wine-glass-alt"></i>
                        </div>
                        <div class="agr-feature-text">
                            <h4>Curated Wine Cellar</h4>
                            <p>Over 500 labels, with a focus on Old World vineyards and rare vintages.</p>
                        </div>
                    </div>
                </div>
                <a href="/page/about" class="agr-btn agr-btn--text">
                    Read Chef’s Full Story <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            <div class="agr-about-image" data-animate>
                <div class="agr-image-frame">
                    <img src="<?= esc($aboutImage) ?>" alt="Chef Elara Vance in the kitchen" data-ts-bg="about.image">
                    <div class="agr-image-accent"></div>
                </div>
            </div>
        </div>
    </div>
</section>
