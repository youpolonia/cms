<?php
$statsLabel = theme_get('stats.label', 'By The Numbers');
$statsTitle = theme_get('stats.title', 'Proven Results Across Industries');
$statsDesc = theme_get('stats.description', 'Our data-driven approach delivers consistent growth for DTC brands of all sizes.');
?>
<section class="sf-section sf-stats">
    <div class="container">
        <div class="sf-section__header" data-animate>
            <span class="sf-section__label" data-ts="stats.label"><?= esc($statsLabel) ?></span>
            <div class="sf-section__divider"></div>
            <h2 class="sf-section__title" data-ts="stats.title"><?= esc($statsTitle) ?></h2>
            <p class="sf-section__desc" data-ts="stats.description"><?= esc($statsDesc) ?></p>
        </div>
        <div class="sf-stats__grid">
            <div class="sf-stat" data-animate>
                <div class="sf-stat__number" data-count="42">0</div>
                <div class="sf-stat__divider"></div>
                <p class="sf-stat__label">Average ROAS Increase</p>
            </div>
            <div class="sf-stat" data-animate>
                <div class="sf-stat__number" data-count="320">0</div>
                <div class="sf-stat__divider"></div>
                <p class="sf-stat__label">Conversion Rate Lift</p>
            </div>
            <div class="sf-stat" data-animate>
                <div class="sf-stat__number" data-count="127">0</div>
                <div class="sf-stat__divider"></div>
                <p class="sf-stat__label">DTC Brands Scaled</p>
            </div>
            <div class="sf-stat" data-animate>
                <div class="sf-stat__number" data-count="15">0</div>
                <div class="sf-stat__divider"></div>
                <p class="sf-stat__label">Industry Awards</p>
            </div>
        </div>
        <div class="sf-stats__clients">
            <p class="sf-stats__clients-label">Trusted by innovative DTC brands</p>
            <div class="sf-stats__logos">
                <div class="sf-stats__logo">BRAND<span>X</span></div>
                <div class="sf-stats__logo">VITA<span>BOOST</span></div>
                <div class="sf-stats__logo">ARTISAN<span>LIVING</span></div>
                <div class="sf-stats__logo">GADGET<span>FLOW</span></div>
                <div class="sf-stats__logo">LUXE<span>APPAREL</span></div>
            </div>
        </div>
    </div>
</section>
