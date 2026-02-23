<?php
$flavorsLabel = theme_get('flavors.label', 'Our Collection');
$flavorsTitle = theme_get('flavors.title', '30+ Signature Flavors');
$flavorsDesc = theme_get('flavors.description', 'Each cheesecake is a masterpiece of texture and taste, crafted from the finest ingredients and aged for optimal richness.');
$flavorsBtnText = theme_get('flavors.btn_text', 'View Full Menu');
$flavorsBtnLink = theme_get('flavors.btn_link', '/menu');

$flavorImages = [
    'https://images.pexels.com/photos/30890572/pexels-photo-30890572.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/29349523/pexels-photo-29349523.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/6594904/pexels-photo-6594904.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/30926137/pexels-photo-30926137.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="vcb-section vcb-flavors" id="flavors">
    <div class="container">
        <div class="vcb-section-header" data-animate>
            <span class="vcb-section-label" data-ts="flavors.label"><?= esc($flavorsLabel) ?></span>
            <div class="vcb-section-divider"></div>
            <h2 class="vcb-section-title" data-ts="flavors.title"><?= esc($flavorsTitle) ?></h2>
            <p class="vcb-section-desc" data-ts="flavors.description"><?= esc($flavorsDesc) ?></p>
        </div>

        <div class="vcb-flavors-grid">
            <div class="vcb-flavor-card" data-animate>
                <div class="vcb-flavor-img">
                    <img src="<?= esc($flavorImages[0]) ?>" alt="Classic New York Cheesecake" loading="lazy">
                    <span class="vcb-flavor-badge">Best Seller</span>
                </div>
                <div class="vcb-flavor-content">
                    <h3 class="vcb-flavor-name">Classic New York</h3>
                    <p class="vcb-flavor-desc">Dense, creamy, and perfectly sweet with a graham cracker crust.</p>
                    <div class="vcb-flavor-meta">
                        <span class="vcb-flavor-price">From $48</span>
                        <span class="vcb-flavor-serves"><i class="fas fa-user-friends"></i> Serves 8-10</span>
                    </div>
                </div>
            </div>

            <div class="vcb-flavor-card" data-animate>
                <div class="vcb-flavor-img">
                    <img src="<?= esc($flavorImages[1]) ?>" alt="Salted Caramel Cheesecake" loading="lazy">
                    <span class="vcb-flavor-badge vcb-flavor-badge-new">New</span>
                </div>
                <div class="vcb-flavor-content">
                    <h3 class="vcb-flavor-name">Salted Caramel</h3>
                    <p class="vcb-flavor-desc">Buttery caramel swirls with a hint of sea salt on a pretzel crust.</p>
                    <div class="vcb-flavor-meta">
                        <span class="vcb-flavor-price">From $52</span>
                        <span class="vcb-flavor-serves"><i class="fas fa-user-friends"></i> Serves 8-10</span>
                    </div>
                </div>
            </div>

            <div class="vcb-flavor-card" data-animate>
                <div class="vcb-flavor-img">
                    <img src="<?= esc($flavorImages[2]) ?>" alt="Matcha Green Tea Cheesecake" loading="lazy">
                </div>
                <div class="vcb-flavor-content">
                    <h3 class="vcb-flavor-name">Matcha Green Tea</h3>
                    <p class="vcb-flavor-desc">Elegant Japanese matcha with a white chocolate ganache layer.</p>
                    <div class="vcb-flavor-meta">
                        <span class="vcb-flavor-price">From $56</span>
                        <span class="vcb-flavor-serves"><i class="fas fa-user-friends"></i> Serves 8-10</span>
                    </div>
                </div>
            </div>

            <div class="vcb-flavor-card" data-animate>
                <div class="vcb-flavor-img">
                    <img src="<?= esc($flavorImages[3]) ?>" alt="Chocolate Raspberry Truffle" loading="lazy">
                </div>
                <div class="vcb-flavor-content">
                    <h3 class="vcb-flavor-name">Chocolate Raspberry</h3>
                    <p class="vcb-flavor-desc">Decadent dark chocolate with fresh raspberry coulis and chocolate crust.</p>
                    <div class="vcb-flavor-meta">
                        <span class="vcb-flavor-price">From $54</span>
                        <span class="vcb-flavor-serves"><i class="fas fa-user-friends"></i> Serves 8-10</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="vcb-flavors-cta" data-animate>
            <a href="<?= esc($flavorsBtnLink) ?>" class="vcb-btn vcb-btn-primary" data-ts="flavors.btn_text" data-ts-href="flavors.btn_link">
                <?= esc($flavorsBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
            <p class="vcb-flavors-note">All flavors available for individual slices, whole cakes, and custom sizing.</p>
        </div>
    </div>
</section>
