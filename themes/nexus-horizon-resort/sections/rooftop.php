<?php
$rooftopLabel = theme_get('rooftop.label', 'INFINITY EXPERIENCE');
$rooftopTitle = theme_get('rooftop.title', 'Skyline Pool & Sunset Lounge');
$rooftopDesc = theme_get('rooftop.description', 'An elevated oasis 40 stories up. Swim against the city skyline, sip craft cocktails, and watch the sun dip below the horizon.');
$rooftopBtnText = theme_get('rooftop.btn_text', 'Book Rooftop Access');
$rooftopBtnLink = theme_get('rooftop.btn_link', '#');
$images = [
    'https://images.pexels.com/photos/31533422/pexels-photo-31533422.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/6872320/pexels-photo-6872320.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14036450/pexels-photo-14036450.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="nhr-section nhr-section-rooftop" id="rooftop">
    <div class="nhr-rooftop-hero">
        <div class="nhr-rooftop-bg" style="background-image: url('<?= esc($images[0]) ?>');"></div>
        <div class="nhr-rooftop-overlay"></div>
        <div class="container">
            <div class="nhr-rooftop-header" data-animate>
                <span class="nhr-section-label" data-ts="rooftop.label"><?= esc($rooftopLabel) ?></span>
                <h2 class="nhr-section-title nhr-rooftop-title" data-ts="rooftop.title"><?= esc($rooftopTitle) ?></h2>
                <p class="nhr-section-desc nhr-rooftop-desc" data-ts="rooftop.description"><?= esc($rooftopDesc) ?></p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="nhr-rooftop-features">
            <!-- Feature 1 -->
            <div class="nhr-rooftop-feature" data-animate>
                <div class="nhr-rooftop-feature-icon">
                    <i class="fas fa-infinity"></i>
                </div>
                <div class="nhr-rooftop-feature-content">
                    <h3 class="nhr-rooftop-feature-title">Edge‑less Pool</h3>
                    <p class="nhr-rooftop-feature-text">25‑meter infinity pool with transparent side—feel like you’re swimming into the sky.</p>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="nhr-rooftop-feature" data-animate>
                <div class="nhr-rooftop-feature-icon">
                    <i class="fas fa-cocktail"></i>
                </div>
                <div class="nhr-rooftop-feature-content">
                    <h3 class="nhr-rooftop-feature-title">Sky Bar</h3>
                    <p class="nhr-rooftop-feature-text">Signature drinks curated by award‑winning mixologists, served with panoramic views.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="nhr-rooftop-feature" data-animate>
                <div class="nhr-rooftop-feature-icon">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="nhr-rooftop-feature-content">
                    <h3 class="nhr-rooftop-feature-title">Fire Pit Lounge</h3>
                    <p class="nhr-rooftop-feature-text">Cozy up around modern fire features as the city lights twinkle below.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="nhr-rooftop-feature" data-animate>
                <div class="nhr-rooftop-feature-icon">
                    <i class="fas fa-music"></i>
                </div>
                <div class="nhr-rooftop-feature-content">
                    <h3 class="nhr-rooftop-feature-title">Sunset Sessions</h3>
                    <p class="nhr-rooftop-feature-text">Live acoustic sets and curated DJs every Friday and Saturday evening.</p>
                </div>
            </div>
        </div>

        <div class="nhr-rooftop-gallery">
            <div class="nhr-rooftop-gallery-main" data-animate>
                <img src="<?= esc($images[1]) ?>" alt="Stunning aerial shot of a resort with a swimming pool surrounded by lush palm trees." loading="lazy">
            </div>
            <div class="nhr-rooftop-gallery-side">
                <div class="nhr-rooftop-gallery-item" data-animate>
                    <img src="<?= esc($images[2]) ?>" alt="Beautiful tropical resort pool with sunloungers, parasols, and palm trees under a sunny sky." loading="lazy">
                    <div class="nhr-rooftop-gallery-caption">Day‑time lounging</div>
                </div>
                <div class="nhr-rooftop-gallery-item" data-animate>
                    <div class="nhr-rooftop-gallery-placeholder">
                        <i class="fas fa-video"></i>
                        <p>Evening vibe video tour</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="nhr-section-actions nhr-rooftop-actions" data-animate>
            <a href="<?= esc($rooftopBtnLink) ?>" class="nhr-btn nhr-btn-primary" data-ts="rooftop.btn_text" data-ts-href="rooftop.btn_link">
                <?= esc($rooftopBtnText) ?>
                <i class="fas fa-arrow-up"></i>
            </a>
            <a href="#hours" class="nhr-btn nhr-btn-outline">
                View Hours & Access
                <i class="fas fa-clock"></i>
            </a>
        </div>
    </div>
</section>
