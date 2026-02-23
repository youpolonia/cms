<?php
$testimonialsLabel = theme_get('testimonials.label', 'CLIENT VOICES');
$testimonialsTitle = theme_get('testimonials.title', 'What Freelancers Say About Velocity');
$testimonialsDesc = theme_get('testimonials.description', 'Join thousands of independent professionals who have transformed their financial management with our AI-powered platform.');

$test1Name = theme_get('testimonials.test1_name', 'Sarah Chen');
$test1Title = theme_get('testimonials.test1_title', 'UX Designer & Consultant');
$test1Quote = theme_get('testimonials.test1_quote', 'The AI expense categorization is a game-changer. What used to take me hours at the end of each month now happens automatically. I can focus on my clients instead of my bookkeeping.');
$test1Image = theme_get('testimonials.test1_image', $themePath . '/assets/testimonial-1.jpg');
$test1Rating = theme_get('testimonials.test1_rating', '5');

$test2Name = theme_get('testimonials.test2_name', 'Marcus Rodriguez');
$test2Title = theme_get('testimonials.test2_title', 'Software Developer');
$test2Quote = theme_get('testimonials.test2_quote', 'Managing clients in 8 different currencies used to be a nightmare. Velocity handles multi-currency seamlessly and the instant invoicing feature has improved my cash flow dramatically.');
$test2Image = theme_get('testimonials.test2_image', $themePath . '/assets/testimonial-2.jpg');
$test2Rating = theme_get('testimonials.test2_rating', '5');

$test3Name = theme_get('testimonials.test3_name', 'Aisha Patel');
$test3Title = theme_get('testimonials.test3_title', 'Content Strategist');
$test3Quote = theme_get('testimonials.test3_quote', 'As a digital nomad, I needed banking that travels with me. The real-time insights help me make smarter financial decisions no matter which timezone I\'m in.');
$test3Image = theme_get('testimonials.test3_image', $themePath . '/assets/testimonial-3.jpg');
$test3Rating = theme_get('testimonials.test3_rating', '5');
?>
<section class="vbf-testimonials" id="testimonials">
    <div class="container">
        <div class="vbf-section-header" data-animate>
            <span class="vbf-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="vbf-section-divider"></div>
            <h2 class="vbf-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="vbf-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="vbf-testimonials-layout">
            <div class="vbf-testimonial-featured" data-animate>
                <div class="vbf-testimonial-featured-content">
                    <div class="vbf-testimonial-quote-mark">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <blockquote class="vbf-testimonial-text" data-ts="testimonials.test1_quote">
                        <?= esc($test1Quote) ?>
                    </blockquote>
                    <div class="vbf-testimonial-rating">
                        <?php for ($i = 0; $i < (int)$test1Rating; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="vbf-testimonial-author">
                        <div class="vbf-testimonial-avatar" data-ts-bg="testimonials.test1_image" style="background-image: url('<?= esc($test1Image) ?>');"></div>
                        <div class="vbf-testimonial-author-info">
                            <div class="vbf-testimonial-name" data-ts="testimonials.test1_name"><?= esc($test1Name) ?></div>
                            <div class="vbf-testimonial-title" data-ts="testimonials.test1_title"><?= esc($test1Title) ?></div>
                        </div>
                    </div>
                </div>
                <div class="vbf-testimonial-featured-decoration"></div>
            </div>
            
            <div class="vbf-testimonials-secondary">
                <div class="vbf-testimonial-card" data-animate>
                    <div class="vbf-testimonial-card-header">
                        <div class="vbf-testimonial-avatar" data-ts-bg="testimonials.test2_image" style="background-image: url('<?= esc($test2Image) ?>');"></div>
                        <div class="vbf-testimonial-author-info">
                            <div class="vbf-testimonial-name" data-ts="testimonials.test2_name"><?= esc($test2Name) ?></div>
                            <div class="vbf-testimonial-title" data-ts="testimonials.test2_title"><?= esc($test2Title) ?></div>
                        </div>
                    </div>
                    <div class="vbf-testimonial-rating">
                        <?php for ($i = 0; $i < (int)$test2Rating; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <blockquote class="vbf-testimonial-text" data-ts="testimonials.test2_quote">
                        <?= esc($test2Quote) ?>
                    </blockquote>
                </div>
                
                <div class="vbf-testimonial-card" data-animate>
                    <div class="vbf-testimonial-card-header">
                        <div class="vbf-testimonial-avatar" data-ts-bg="testimonials.test3_image" style="background-image: url('<?= esc($test3Image) ?>');"></div>
                        <div class="vbf-testimonial-author-info">
                            <div class="vbf-testimonial-name" data-ts="testimonials.test3_name"><?= esc($test3Name) ?></div>
                            <div class="vbf-testimonial-title" data-ts="testimonials.test3_title"><?= esc($test3Title) ?></div>
                        </div>
                    </div>
                    <div class="vbf-testimonial-rating">
                        <?php for ($i = 0; $i < (int)$test3Rating; $i++): ?>
                        <i class="fas fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <blockquote class="vbf-testimonial-text" data-ts="testimonials.test3_quote">
                        <?= esc($test3Quote) ?>
                    </blockquote>
                </div>
            </div>
        </div>
        
        <div class="vbf-testimonials-trust-badges" data-animate>
            <div class="vbf-trust-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Bank-Level Security</span>
            </div>
            <div class="vbf-trust-badge">
                <i class="fas fa-lock"></i>
                <span>256-bit Encryption</span>
            </div>
            <div class="vbf-trust-badge">
                <i class="fas fa-check-circle"></i>
                <span>SOC 2 Certified</span>
            </div>
        </div>
    </div>
</section>
