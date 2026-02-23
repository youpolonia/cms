<?php
$menuLabel = theme_get('menu.label', 'Signatures');
$menuTitle = theme_get('menu.title', 'A Curated Journey Through Flavor');
$menuDesc = theme_get('menu.description', 'Our seasonal tasting menus evolve with the harvest. Each course is a deliberate expression of technique, terroir, and time.');
?>
<section class="agr-section agr-section--menu" id="menu">
    <div class="container">
        <div class="agr-section-header" data-animate>
            <span class="agr-section-label" data-ts="menu.label"><?= esc($menuLabel) ?></span>
            <div class="agr-section-divider"></div>
            <h2 class="agr-section-title" data-ts="menu.title"><?= esc($menuTitle) ?></h2>
            <p class="agr-section-desc" data-ts="menu.description"><?= esc($menuDesc) ?></p>
        </div>
        <div class="agr-menu-tabs" data-animate>
            <button class="agr-menu-tab active" data-tab="tasting">Tasting Menu</button>
            <button class="agr-menu-tab" data-tab="a-la-carte">À La Carte</button>
            <button class="agr-menu-tab" data-tab="wine">Wine List</button>
        </div>
        <div class="agr-menu-content">
            <div class="agr-menu-pane active" id="tasting">
                <div class="agr-menu-course">
                    <div class="agr-course-header">
                        <h3 class="agr-course-title">The Prelude</h3>
                        <span class="agr-course-subtitle">Canapés &amp; Amuse‑Bouche</span>
                    </div>
                    <div class="agr-course-items">
                        <div class="agr-menu-item">
                            <div class="agr-item-header">
                                <h4 class="agr-item-title">Oyster &amp; Seaweed</h4>
                                <span class="agr-item-price">—</span>
                            </div>
                            <p class="agr-item-desc">Kumamoto oyster, cucumber gel, finger lime, coastal herbs.</p>
                            <span class="agr-item-pairing"><i class="fas fa-wine-glass-alt"></i> Pairing: Blanc de Blancs, NV</span>
                        </div>
                        <div class="agr-menu-item">
                            <div class="agr-item-header">
                                <h4 class="agr-item-title">Foie Gras &amp; Cherry</h4>
                                <span class="agr-item-price">—</span>
                            </div>
                            <p class="agr-item-desc">Terrine of Hudson Valley foie gras, sour cherry compote, brioche.</p>
                            <span class="agr-item-pairing"><i class="fas fa-wine-glass-alt"></i> Pairing: Sauternes, 2015</span>
                        </div>
                    </div>
                </div>
                <div class="agr-menu-course">
                    <div class="agr-course-header">
                        <h3 class="agr-course-title">The Land &amp; Sea</h3>
                        <span class="agr-course-subtitle">Main Compositions</span>
                    </div>
                    <div class="agr-course-items">
                        <div class="agr-menu-item">
                            <div class="agr-item-header">
                                <h4 class="agr-item-title">Halibut en Papillote</h4>
                                <span class="agr-item-price">—</span>
                            </div>
                            <p class="agr-item-desc">Wild Alaskan halibut, morels, spring peas, vermouth broth.</p>
                            <span class="agr-item-pairing"><i class="fas fa-wine-glass-alt"></i> Pairing: Chardonnay, Burgundy</span>
                        </div>
                        <div class="agr-menu-item">
                            <div class="agr-item-header">
                                <h4 class="agr-item-title">Wagyu Ribeye</h4>
                                <span class="agr-item-price">Supplement +$85</span>
                            </div>
                            <p class="agr-item-desc">A5 Miyazaki wagyu, black garlic purée, charred leek, bone marrow reduction.</p>
                            <span class="agr-item-pairing"><i class="fas fa-wine-glass-alt"></i> Pairing: Cabernet Sauvignon, Napa</span>
                        </div>
                    </div>
                </div>
                <div class="agr-menu-footer">
                    <p class="agr-menu-note">Seven‑course tasting menu: $195 per person. Wine pairing: +$125. Subject to change seasonally.</p>
                    <a href="/page/menu" class="agr-btn agr-btn--text">View Full Menu PDF <i class="fas fa-external-link-alt"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>
