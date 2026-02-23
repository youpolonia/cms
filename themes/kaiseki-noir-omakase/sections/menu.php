<?php
$menuLabel = theme_get('menu.label', 'Tonight\'s Selection');
$menuTitle = theme_get('menu.title', 'Omakase Journey');
$menuDesc = theme_get('menu.description', 'Our twelve-course tasting menu evolves daily, guided by the season, the market, and the chef\'s inspiration. Below is an example journey—your experience will be uniquely curated for the evening you join us.');
?>
<section class="kno-section kno-menu" id="menu">
    <div class="container-narrow">
        <div class="kno-section-header kno-section-header-center" data-animate>
            <span class="kno-section-label" data-ts="menu.label"><?= esc($menuLabel) ?></span>
            <div class="kno-section-divider"></div>
            <h2 class="kno-section-title" data-ts="menu.title"><?= esc($menuTitle) ?></h2>
            <p class="kno-section-desc" data-ts="menu.description"><?= esc($menuDesc) ?></p>
        </div>

        <div class="kno-menu-timeline">
            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">01</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Sakizuke</h3>
                    <p class="kno-course-desc">Seasonal appetizer—tonight, Hokkaido uni with yuzu kosho and micro shiso</p>
                </div>
            </div>

            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">02</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Owan</h3>
                    <p class="kno-course-desc">Clear dashi broth with matsutake mushroom and delicate white fish</p>
                </div>
            </div>

            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">03</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Tsukuri</h3>
                    <p class="kno-course-desc">Selection of three seasonal sashimi: bluefin tuna, Hokkaido scallop, kinmedai</p>
                </div>
            </div>

            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">04</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Yakimono</h3>
                    <p class="kno-course-desc">Charcoal-grilled nodoguro with sansho pepper and autumn mushrooms</p>
                </div>
            </div>

            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">05</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Nigiri Sequence</h3>
                    <p class="kno-course-desc">Seven pieces of chef's selection nigiri, each at ideal temperature and seasoning</p>
                </div>
            </div>

            <div class="kno-menu-course" data-animate>
                <div class="kno-course-number">06</div>
                <div class="kno-course-content">
                    <h3 class="kno-course-name">Mizumono</h3>
                    <p class="kno-course-desc">Seasonal dessert—black sesame panna cotta with miso caramel and gold leaf</p>
                </div>
            </div>
        </div>

        <div class="kno-menu-note" data-animate>
            <i class="fas fa-info-circle"></i>
            <p>Our omakase experience is £195 per person. Sake pairing available for £85. Vegetarian and dietary accommodations with 48-hour notice.</p>
        </div>
    </div>
</section>
