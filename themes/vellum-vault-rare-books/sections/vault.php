<?php
$vaultLabel = theme_get('vault.label', 'Preservation');
$vaultTitle = theme_get('vault.title', 'The Climate‑Controlled Vault');
$vaultDesc = theme_get('vault.description', 'Our state‑of‑the‑art archival facility ensures the longevity of irreplaceable works through precise environmental control and security protocols.');
$vaultBtnText = theme_get('vault.btn_text', 'Schedule a Vault Tour');
$vaultBtnLink = theme_get('vault.btn_link', '#contact');

$vaultImages = [
    'https://images.pexels.com/photos/14805379/pexels-photo-14805379.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/14284949/pexels-photo-14284949.jpeg?auto=compress&cs=tinysrgb&h=650&w=940',
    'https://images.pexels.com/photos/22129776/pexels-photo-22129776.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'
];
?>
<section class="section vvr-vault-section" id="vault" style="background-color: var(--background);">
    <div class="container">
        <div class="vvr-vault-split">
            <div class="vvr-vault-content" data-animate>
                <span class="section-label" data-ts="vault.label"><?= esc($vaultLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="vault.title"><?= esc($vaultTitle) ?></h2>
                <p class="section-desc" data-ts="vault.description"><?= esc($vaultDesc) ?></p>

                <div class="vvr-vault-features">
                    <div class="vvr-vault-feature">
                        <div class="vvr-vault-feature-icon">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <div class="vvr-vault-feature-text">
                            <h4>Temperature & Humidity</h4>
                            <p>Maintained at 18°C (65°F) and 45% RH ±2% year‑round.</p>
                        </div>
                    </div>
                    <div class="vvr-vault-feature">
                        <div class="vvr-vault-feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="vvr-vault-feature-text">
                            <h4>Multi‑Layer Security</h4>
                            <p>Biometric access, 24/7 monitoring, and fire‑suppression systems.</p>
                        </div>
                    </div>
                    <div class="vvr-vault-feature">
                        <div class="vvr-vault-feature-icon">
                            <i class="fas fa-hands"></i>
                        </div>
                        <div class="vvr-vault-feature-text">
                            <h4>White‑Glove Handling</h4>
                            <p>All items are accessed with archival‑grade gloves and tools.</p>
                        </div>
                    </div>
                </div>

                <a href="<?= esc($vaultBtnLink) ?>" class="vvr-btn vvr-btn-primary" data-ts="vault.btn_text" data-ts-href="vault.btn_link">
                    <?= esc($vaultBtnText) ?> <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="vvr-vault-visual" data-animate>
                <div class="vvr-vault-image-stack">
                    <div class="vvr-vault-image-main">
                        <img src="<?= esc($vaultImages[0]) ?>" alt="A person browses a vibrant selection of books in a cozy Brașov bookstore." loading="lazy">
                    </div>
                    <div class="vvr-vault-image-secondary">
                        <img src="<?= esc($vaultImages[1]) ?>" alt="Warm and vintage bookstore filled with an assortment of books and nostalgia in Antalya." loading="lazy">
                    </div>
                    <div class="vvr-vault-image-tertiary">
                        <img src="<?= esc($vaultImages[2]) ?>" alt="A curious black and white cat climbs stacks of books inside a cozy bookstore, conveying a sense of adventure." loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
