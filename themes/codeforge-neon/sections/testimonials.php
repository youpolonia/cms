<?php
$testimonialsLabel = theme_get('testimonials.label', 'Real Stories');
$testimonialsTitle = theme_get('testimonials.title', 'Founders Who Shipped Faster');
$testimonialsDesc = theme_get('testimonials.description', "Don't take our word for it—here's what builders are saying about CodeForge.");
?>
<section class="section testimonials-section" id="testimonials">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        
        <div class="testimonials-masonry" data-animate>
            <div class="testimonial-card large">
                <div class="card-glow"></div>
                <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                <p class="testimonial-text">"I went from idea to a working SaaS prototype in a single weekend. As a non-technical founder, this felt like magic. CodeForge understood exactly what I wanted and generated clean, readable code."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/80?img=32" alt="Sarah Chen" loading="lazy">
                    </div>
                    <div class="author-info">
                        <strong>Sarah Chen</strong>
                        <span>Founder, TaskFlow</span>
                    </div>
                </div>
                <div class="testimonial-rating">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                <p class="testimonial-text">"Saved me 3 months of development time. I described my e-commerce features and had a working storefront the same day."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/80?img=11" alt="Marcus Johnson" loading="lazy">
                    </div>
                    <div class="author-info">
                        <strong>Marcus Johnson</strong>
                        <span>Creator, Artisan Goods</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                <p class="testimonial-text">"The iteration speed is insane. 'Add a dark mode toggle' and boom—it's done. No Stack Overflow rabbit holes."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/80?img=25" alt="Priya Patel" loading="lazy">
                    </div>
                    <div class="author-info">
                        <strong>Priya Patel</strong>
                        <span>Solo Developer</span>
                    </div>
                </div>
            </div>
            
            <div class="testimonial-card">
                <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                <p class="testimonial-text">"Built our MVP, got into Y Combinator, raised our seed round. CodeForge was the unfair advantage we needed."</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <img src="https://i.pravatar.cc/80?img=53" alt="Alex Rivera" loading="lazy">
                    </div>
                    <div class="author-info">
                        <strong>Alex Rivera</strong>
                        <span>CEO, LaunchPad AI</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
