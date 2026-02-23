<?php
$smartroomsLabel = theme_get('smartrooms.label', 'SMART LIVING');
$smartroomsTitle = theme_get('smartrooms.title', 'Intelligent Rooms, Intuitive Experience');
$smartroomsDesc = theme_get('smartrooms.description', 'Our rooms adapt to you—climate, lighting, and entertainment controlled by voice or touch. Sleep, work, and relax in spaces that learn your preferences.');
$smartroomsBtnText = theme_get('smartrooms.btn_text', 'Explore Room Features');
$smartroomsBtnLink = theme_get('smartrooms.btn_link', '#');
$images = [
    'https://images.pexels.com/photos/14024966/pexels-photo-14024966.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14024952/pexels-photo-14024952.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14036440/pexels-photo-14036440.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14917458/pexels-photo-14917458.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="nhr-section nhr-section-smartrooms" id="smartrooms">
    <div class="container">
        <div class="nhr-section-header" data-animate>
            <span class="nhr-section-label" data-ts="smartrooms.label"><?= esc($smartroomsLabel) ?></span>
            <div class="nhr-section-divider"></div>
            <h2 class="nhr-section-title" data-ts="smartrooms.title"><?= esc($smartroomsTitle) ?></h2>
            <p class="nhr-section-desc" data-ts="smartrooms.description"><?= esc($smartroomsDesc) ?></p>
        </div>

        <div class="nhr-smartrooms-grid">
            <!-- Feature 1 -->
            <div class="nhr-smartroom-card" data-animate>
                <div class="nhr-smartroom-visual">
                    <img src="<?= esc($images[0]) ?>" alt="Serene resort featuring a swimming pool surrounded by palm trees and villas." loading="lazy">
                    <div class="nhr-smartroom-overlay"></div>
                    <div class="nhr-smartroom-icon">
                        <i class="fas fa-robot"></i>
                    </div>
                </div>
                <div class="nhr-smartroom-content">
                    <h3 class="nhr-smartroom-title">AI Climate Control</h3>
                    <p class="nhr-smartroom-text">Room temperature and humidity adjust automatically based on your activity and time of day.</p>
                    <ul class="nhr-smartroom-list">
                        <li><i class="fas fa-check"></i> Voice-activated settings</li>
                        <li><i class="fas fa-check"></i> Energy-efficient modes</li>
                        <li><i class="fas fa-check"></i> Sleep optimization</li>
                    </ul>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="nhr-smartroom-card" data-animate>
                <div class="nhr-smartroom-visual">
                    <img src="<?= esc($images[1]) ?>" alt="Relax by the luxurious pool with palm trees at a tropical resort." loading="lazy">
                    <div class="nhr-smartroom-overlay"></div>
                    <div class="nhr-smartroom-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                </div>
                <div class="nhr-smartroom-content">
                    <h3 class="nhr-smartroom-title">Adaptive Lighting</h3>
                    <p class="nhr-smartroom-text">Mood-based lighting scenes for work, relaxation, or entertainment—controlled via app or touch panel.</p>
                    <ul class="nhr-smartroom-list">
                        <li><i class="fas fa-check"></i> Circadian rhythm sync</li>
                        <li><i class="fas fa-check"></i> Color temperature tuning</li>
                        <li><i class="fas fa-check"></i> Motion-sensor activation</li>
                    </ul>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="nhr-smartroom-card" data-animate>
                <div class="nhr-smartroom-visual">
                    <img src="<?= esc($images[2]) ?>" alt="Experience a tropical paradise at this luxurious resort with a stunning pool and relaxation area." loading="lazy">
                    <div class="nhr-smartroom-overlay"></div>
                    <div class="nhr-smartroom-icon">
                        <i class="fas fa-tv"></i>
                    </div>
                </div>
                <div class="nhr-smartroom-content">
                    <h3 class="nhr-smartroom-title">Immersive Entertainment</h3>
                    <p class="nhr-smartroom-text">4K projection, surround sound, and streaming integration—your media follows you room-to-room.</p>
                    <ul class="nhr-smartroom-list">
                        <li><i class="fas fa-check"></i> Wireless casting</li>
                        <li><i class="fas fa-check"></i> Personalized profiles</li>
                        <li><i class="fas fa-check"></i> Gaming mode</li>
                    </ul>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="nhr-smartroom-card" data-animate>
                <div class="nhr-smartroom-visual">
                    <img src="<?= esc($images[3]) ?>" alt="A luxurious swimming pool surrounded by arch bridges and lush trees, ideal for relaxation." loading="lazy">
                    <div class="nhr-smartroom-overlay"></div>
                    <div class="nhr-smartroom-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <div class="nhr-smartroom-content">
                    <h3 class="nhr-smartroom-title">Smart Security & Privacy</h3>
                    <p class="nhr-smartroom-text">Biometric entry, digital do-not-disturb, and encrypted data—your space is yours alone.</p>
                    <ul class="nhr-smartroom-list">
                        <li><i class="fas fa-check"></i> Facial recognition</li>
                        <li><i class="fas fa-check"></i> Privacy glass</li>
                        <li><i class="fas fa-check"></i> Data anonymization</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="nhr-section-actions" data-animate>
            <a href="<?= esc($smartroomsBtnLink) ?>" class="nhr-btn nhr-btn-primary" data-ts="smartrooms.btn_text" data-ts-href="smartrooms.btn_link">
                <?= esc($smartroomsBtnText) ?>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
