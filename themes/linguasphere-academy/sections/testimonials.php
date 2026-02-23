<?php
$testimonialsLabel = theme_get('testimonials.label', 'Success Stories');
$testimonialsTitle = theme_get('testimonials.title', 'Hear From Our Fluent Speakers');
$testimonialsDesc = theme_get('testimonials.description', 'Our students have transformed their language skills and connected with cultures worldwide. Here’s what they say about their immersive journey.');
?>
<section class="lsa-section lsa-testimonials" id="testimonials">
    <div class="container">
        <div class="lsa-section-header" data-animate>
            <span class="lsa-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="lsa-section-divider"></div>
            <h2 class="lsa-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="lsa-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>

        <div class="lsa-testimonials-grid">
            <div class="lsa-testimonial-card" data-animate>
                <div class="lsa-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="lsa-testimonial-text">“After just 3 months of conversation-focused lessons, I held my first full Spanish conversation during a trip to Madrid. The cultural insights made all the difference!”</p>
                <div class="lsa-testimonial-author">
                    <div class="lsa-testimonial-avatar">
                        <img src="https://images.pexels.com/photos/5905719/pexels-photo-5905719.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Sarah, Spanish learner" loading="lazy">
                    </div>
                    <div class="lsa-testimonial-info">
                        <h4>Sarah M.</h4>
                        <span>Spanish Immersion Student</span>
                    </div>
                </div>
            </div>

            <div class="lsa-testimonial-card" data-animate>
                <div class="lsa-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="lsa-testimonial-text">“The Mandarin course completely changed my approach to language learning. Instead of memorizing characters, I learned through real dialogues. Now I can chat with my colleagues in Beijing!”</p>
                <div class="lsa-testimonial-author">
                    <div class="lsa-testimonial-avatar">
                        <img src="https://images.pexels.com/photos/4260323/pexels-photo-4260323.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="James, Mandarin learner" loading="lazy">
                    </div>
                    <div class="lsa-testimonial-info">
                        <h4>James L.</h4>
                        <span>Business Mandarin Program</span>
                    </div>
                </div>
            </div>

            <div class="lsa-testimonial-card" data-animate>
                <div class="lsa-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                </div>
                <p class="lsa-testimonial-text">“I tried traditional French classes for years with little progress. LinguaSphere’s conversation-first method finally made it click. My pronunciation improved dramatically with native speaker practice.”</p>
                <div class="lsa-testimonial-author">
                    <div class="lsa-testimonial-avatar">
                        <img src="https://images.pexels.com/photos/5759803/pexels-photo-5759803.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Marie, French learner" loading="lazy">
                    </div>
                    <div class="lsa-testimonial-info">
                        <h4>Marie K.</h4>
                        <span>French Conversation Group</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsa-testimonials-cta" data-animate>
            <p>Ready to share your success story?</p>
            <a href="/testimonials" class="lsa-btn lsa-btn-secondary">
                View All Testimonials
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>
