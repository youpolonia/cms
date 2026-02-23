<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Success Stories');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Clients Say About Us');
$testimonialsDesc = theme_get('testimonials.description', 'Hear from WordPress professionals who trust VerdantPress to power their mission-critical sites.');

$testimonial1Quote = theme_get('testimonials.testimonial1_quote', 'Migrating to VerdantPress was the best decision we made. Our site speed doubled, downtime became non-existent, and their support team is incredibly responsive.');
$testimonial1Author = theme_get('testimonials.testimonial1_author', 'Sarah Mitchell');
$testimonial1Role = theme_get('testimonials.testimonial1_role', 'CTO, TechVenture Inc');
$testimonial1Avatar = theme_get('testimonials.testimonial1_avatar', $themePath . '/assets/images/testimonial-1.jpg');

$testimonial2Quote = theme_get('testimonials.testimonial2_quote', 'The staging environment and automated backups give us peace of mind. We can test changes without fear and roll back instantly if needed. Game changer for our agency.');
$testimonial2Author = theme_get('testimonials.testimonial2_author', 'Marcus Chen');
$testimonial2Role = theme_get('testimonials.testimonial2_role', 'Lead Developer, Digital Spark');
$testimonial2Avatar = theme_get('testimonials.testimonial2_avatar', $themePath . '/assets/images/testimonial-2.jpg');

$testimonial3Quote = theme_get('testimonials.testimonial3_quote', 'VerdantPress handles everything—updates, security, performance optimization. We focus on content and growth while they keep our infrastructure bulletproof.');
$testimonial3Author = theme_get('testimonials.testimonial3_author', 'Emily Rodriguez');
$testimonial3Role = theme_get('testimonials.testimonial3_role', 'Founder, ContentFlow Media');
$testimonial3Avatar = theme_get('testimonials.testimonial3_avatar', $themePath . '/assets/images/testimonial-3.jpg');
?>
<section class="vp-section vp-testimonials-section" id="testimonials">
    <div class="container">
        <div class="vp-section-header" data-animate>
            <span class="vp-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="vp-section-divider"></div>
            <h2 class="vp-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="vp-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="vp-testimonials-grid">
            <div class="vp-testimonial-card" data-animate>
                <div class="vp-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="vp-testimonial-quote" data-ts="testimonials.testimonial1_quote">
                    <?= esc($testimonial1Quote) ?>
                </blockquote>
                <div class="vp-testimonial-author">
                    <div class="vp-testimonial-avatar" data-ts-bg="testimonials.testimonial1_avatar" style="background-image: url('<?= esc($testimonial1Avatar) ?>');"></div>
                    <div class="vp-testimonial-info">
                        <div class="vp-testimonial-name" data-ts="testimonials.testimonial1_author"><?= esc($testimonial1Author) ?></div>
                        <div class="vp-testimonial-role" data-ts="testimonials.testimonial1_role"><?= esc($testimonial1Role) ?></div>
                    </div>
                </div>
                <div class="vp-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="vp-testimonial-card" data-animate>
                <div class="vp-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="vp-testimonial-quote" data-ts="testimonials.testimonial2_quote">
                    <?= esc($testimonial2Quote) ?>
                </blockquote>
                <div class="vp-testimonial-author">
                    <div class="vp-testimonial-avatar" data-ts-bg="testimonials.testimonial2_avatar" style="background-image: url('<?= esc($testimonial2Avatar) ?>');"></div>
                    <div class="vp-testimonial-info">
                        <div class="vp-testimonial-name" data-ts="testimonials.testimonial2_author"><?= esc($testimonial2Author) ?></div>
                        <div class="vp-testimonial-role" data-ts="testimonials.testimonial2_role"><?= esc($testimonial2Role) ?></div>
                    </div>
                </div>
                <div class="vp-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="vp-testimonial-card" data-animate>
                <div class="vp-testimonial-quote-icon">
                    <i class="fas fa-quote-left"></i>
                </div>
                <blockquote class="vp-testimonial-quote" data-ts="testimonials.testimonial3_quote">
                    <?= esc($testimonial3Quote) ?>
                </blockquote>
                <div class="vp-testimonial-author">
                    <div class="vp-testimonial-avatar" data-ts-bg="testimonials.testimonial3_avatar" style="background-image: url('<?= esc($testimonial3Avatar) ?>');"></div>
                    <div class="vp-testimonial-info">
                        <div class="vp-testimonial-name" data-ts="testimonials.testimonial3_author"><?= esc($testimonial3Author) ?></div>
                        <div class="vp-testimonial-role" data-ts="testimonials.testimonial3_role"><?= esc($testimonial3Role) ?></div>
                    </div>
                </div>
                <div class="vp-testimonial-stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
    </div>
</section>
