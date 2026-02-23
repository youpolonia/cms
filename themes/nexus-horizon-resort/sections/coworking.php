<?php
$coworkingLabel = theme_get('coworking.label', 'LOBBY WORKSPACE');
$coworkingTitle = theme_get('coworking.title', 'Productive, Connected, Inspired');
$coworkingDesc = theme_get('coworking.description', 'Our lobby isn’t just for check‑in. It’s a vibrant co‑working hub with high‑speed fiber, private pods, meeting rooms, and barista coffee.');
$coworkingBtnText = theme_get('coworking.btn_text', 'Book a Workspace');
$coworkingBtnLink = theme_get('coworking.btn_link', '#');
$images = [
    'https://images.pexels.com/photos/30370495/pexels-photo-30370495.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14036440/pexels-photo-14036440.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="nhr-section nhr-section-coworking" id="coworking">
    <div class="container">
        <div class="nhr-section-header" data-animate>
            <span class="nhr-section-label" data-ts="coworking.label"><?= esc($coworkingLabel) ?></span>
            <div class="nhr-section-divider"></div>
            <h2 class="nhr-section-title" data-ts="coworking.title"><?= esc($coworkingTitle) ?></h2>
            <p class="nhr-section-desc" data-ts="coworking.description"><?= esc($coworkingDesc) ?></p>
        </div>

        <div class="nhr-coworking-split">
            <div class="nhr-coworking-content" data-animate>
                <div class="nhr-coworking-feature-list">
                    <div class="nhr-coworking-feature">
                        <div class="nhr-coworking-feature-icon">
                            <i class="fas fa-wifi"></i>
                        </div>
                        <div class="nhr-coworking-feature-text">
                            <h4>Gigabit Fiber</h4>
                            <p>Dedicated 1Gbps up/down with enterprise‑grade security.</p>
                        </div>
                    </div>
                    <div class="nhr-coworking-feature">
                        <div class="nhr-coworking-feature-icon">
                            <i class="fas fa-headphones"></i>
                        </div>
                        <div class="nhr-coworking-feature-text">
                            <h4>Acoustic Pods</h4>
                            <p>Sound‑proof phone booths and focus pods for deep work.</p>
                        </div>
                    </div>
                    <div class="nhr-coworking-feature">
                        <div class="nhr-coworking-feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="nhr-coworking-feature-text">
                            <h4>Meeting Rooms</h4>
                            <p>Book by the hour—fully equipped with 4K displays and VC.</p>
                        </div>
                    </div>
                    <div class="nhr-coworking-feature">
                        <div class="nhr-coworking-feature-icon">
                            <i class="fas fa-coffee"></i>
                        </div>
                        <div class="nhr-coworking-feature-text">
                            <h4>Café & Bar</h4>
                            <p>Specialty coffee, healthy bites, and evening craft beer.</p>
                        </div>
                    </div>
                </div>

                <div class="nhr-coworking-cta">
                    <h3 class="nhr-coworking-cta-title">Day Passes & Memberships</h3>
                    <p class="nhr-coworking-cta-desc">Open to guests and local professionals. Flexible plans from hourly to monthly.</p>
                    <div class="nhr-coworking-actions">
                        <a href="<?= esc($coworkingBtnLink) ?>" class="nhr-btn nhr-btn-primary" data-ts="coworking.btn_text" data-ts-href="coworking.btn_link">
                            <?= esc($coworkingBtnText) ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#pricing" class="nhr-btn nhr-btn-outline">
                            View Pricing
                            <i class="fas fa-chart-line"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="nhr-coworking-visual" data-animate>
                <div class="nhr-coworking-image-stack">
                    <div class="nhr-coworking-image-main">
                        <img src="<?= esc($images[0]) ?>" alt="Relaxing view of a tranquil outdoor pool surrounded by lush greenery at a resort in Arusha, Tanzania." loading="lazy">
                        <div class="nhr-coworking-image-badge">
                            <i class="fas fa-star"></i>
                            <span>24/7 Access</span>
                        </div>
                    </div>
                    <div class="nhr-coworking-image-secondary">
                        <img src="<?= esc($images[1]) ?>" alt="Experience a tropical paradise at this luxurious resort with a stunning pool and relaxation area." loading="lazy">
                        <div class="nhr-coworking-image-caption">Collaboration Lounge</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="nhr-coworking-stats">
            <div class="nhr-coworking-stat" data-animate>
                <div class="nhr-coworking-stat-number">200+</div>
                <div class="nhr-coworking-stat-label">Seating Options</div>
            </div>
            <div class="nhr-coworking-stat" data-animate>
                <div class="nhr-coworking-stat-number">12</div>
                <div class="nhr-coworking-stat-label">Meeting Rooms</div>
            </div>
            <div class="nhr-coworking-stat" data-animate>
                <div class="nhr-coworking-stat-number">24/7</div>
                <div class="nhr-coworking-stat-label">Access for Members</div>
            </div>
            <div class="nhr-coworking-stat" data-animate>
                <div class="nhr-coworking-stat-number">∞</div>
                <div class="nhr-coworking-stat-label">Coffee Refills</div>
            </div>
        </div>
    </div>
</section>
