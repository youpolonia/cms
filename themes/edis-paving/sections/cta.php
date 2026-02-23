<?php
$ctaTitle = theme_get('cta.title', 'Ready to Transform Your Outdoor Space?');
$ctaDesc = theme_get('cta.description', 'Get a free, no-obligation quote from Essex\'s most trusted paving contractors. We\'ll visit your property, discuss your vision, and provide honest, transparent pricing.');
$ctaBtnText = theme_get('cta.btn_text', 'Request Your Free Quote');
$ctaBtnLink = theme_get('cta.btn_link', '#contact');
$ctaBgImage = theme_get('cta.bg_image', $themePath . '/assets/images/cta-bg.jpg');
?>
<section class="section cta-section" id="cta">
    <div class="cta-bg" data-ts-bg="cta.bg_image" style="background-image: url('<?= esc($ctaBgImage) ?>');"></div>
    <div class="cta-overlay"></div>
    <div class="cta-pattern"></div>
    
    <div class="container">
        <div class="cta-content" data-animate>
            <div class="cta-badge">
                <i class="fas fa-phone-alt"></i>
                <span>Free Consultation</span>
            </div>
            
            <h2 class="cta-title" data-ts="cta.title"><?= esc($ctaTitle) ?></h2>
            <p class="cta-desc" data-ts="cta.description"><?= esc($ctaDesc) ?></p>
            
            <div class="cta-actions">
                <a href="<?= esc($ctaBtnLink) ?>" 
                   class="btn btn-primary btn-xl"
                   data-ts="cta.btn_text"
                   data-ts-href="cta.btn_link">
                    <?= esc($ctaBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <a href="mailto:info@edispaving.co.uk" class="btn btn-glass btn-xl">
                    <i class="fas fa-envelope"></i>
                    Email Us
                </a>
            </div>
            
            <div class="cta-trust">
                <div class="trust-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>No Hard Sell</span>
                </div>
                <div class="trust-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>Written Quotes</span>
                </div>
                <div class="trust-badge">
                    <i class="fas fa-check-circle"></i>
                    <span>Family-Run</span>
                </div>
            </div>
        </div>
        
        <div class="cta-contact-card" data-animate>
            <h3>Get In Touch</h3>
            <ul class="contact-details">
                <li>
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <strong>Location</strong>
                        <span>70 Broomstick Hall Road, EN9 1LR</span>
                    </div>
                </li>
                <li>
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email</strong>
                        <a href="mailto:info@edispaving.co.uk">info@edispaving.co.uk</a>
                    </div>
                </li>
                <li>
                    <i class="fab fa-instagram"></i>
                    <div>
                        <strong>Instagram</strong>
                        <a href="https://instagram.com/edispaving" target="_blank" rel="noopener">@edispaving</a>
                    </div>
                </li>
            </ul>
            <div class="card-badge">
                <span class="years">23+</span>
                <span class="text">Years Serving Essex</span>
            </div>
        </div>
    </div>
</section>