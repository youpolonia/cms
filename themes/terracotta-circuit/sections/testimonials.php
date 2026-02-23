<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Stories');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Leading Brands');
$testimonialsDesc = theme_get('testimonials.description', 'See how businesses are transforming their delivery operations with our autonomous logistics platform.');
?>
<section class="section testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="testimonials-carousel">
            <div class="testimonial-card testimonial-featured" data-animate>
                <div class="testimonial-quote-icon"><i class="fas fa-quote-left"></i></div>
                <blockquote class="testimonial-text" data-ts="testimonials.quote_1">
                    <?= esc(theme_get('testimonials.quote_1', 'Terracotta Circuit transformed our same-day delivery promise from a logistical nightmare into a competitive advantage. Our customers love the real-time tracking and the reliability is unmatched.')) ?>
                </blockquote>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="author-info">
                        <span class="author-name" data-ts="testimonials.author_1"><?= esc(theme_get('testimonials.author_1', 'Sarah Chen')) ?></span>
                        <span class="author-role" data-ts="testimonials.role_1"><?= esc(theme_get('testimonials.role_1', 'VP of Operations, QuickMart')) ?></span>
                    </div>
                </div>
                <div class="testimonial-metric">
                    <span class="metric-highlight">47%</span>
                    <span class="metric-text">Reduction in delivery costs</span>
                </div>
            </div>
            
            <div class="testimonials-side">
                <div class="testimonial-card" data-animate>
                    <blockquote class="testimonial-text" data-ts="testimonials.quote_2">
                        <?= esc(theme_get('testimonials.quote_2', 'The AI route optimization alone saved us hundreds of thousands in fuel and labor costs. The robots are incredibly reliable.')) ?>
                    </blockquote>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <span class="author-name" data-ts="testimonials.author_2"><?= esc(theme_get('testimonials.author_2', 'Marcus Rodriguez')) ?></span>
                            <span class="author-role" data-ts="testimonials.role_2"><?= esc(theme_get('testimonials.role_2', 'CEO, Urban Goods Co.')) ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card" data-animate>
                    <blockquote class="testimonial-text" data-ts="testimonials.quote_3">
                        <?= esc(theme_get('testimonials.quote_3', 'Our sustainability goals are now achievable. Zero-emission deliveries have become our brand differentiator.')) ?>
                    </blockquote>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="author-info">
                            <span class="author-name" data-ts="testimonials.author_3"><?= esc(theme_get('testimonials.author_3', 'Emily Park')) ?></span>
                            <span class="author-role" data-ts="testimonials.role_3"><?= esc(theme_get('testimonials.role_3', 'Head of Logistics, GreenBox')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="testimonials-logos" data-animate>
            <span class="logos-label">Trusted by industry leaders</span>
            <div class="logos-row">
                <div class="logo-placeholder"><i class="fas fa-building"></i></div>
                <div class="logo-placeholder"><i class="fas fa-shopping-cart"></i></div>
                <div class="logo-placeholder"><i class="fas fa-store"></i></div>
                <div class="logo-placeholder"><i class="fas fa-truck"></i></div>
                <div class="logo-placeholder"><i class="fas fa-box-open"></i></div>
            </div>
        </div>
    </div>
</section>
