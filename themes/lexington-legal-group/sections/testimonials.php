<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Voices');
$testimonialsTitle = theme_get('testimonials.title', 'Trusted by Industry Leaders');
$testimonialsDesc = theme_get('testimonials.description', 'Our clients value our strategic insight, meticulous preparation, and unwavering commitment to their success.');
?>
<section class="llg-section llg-testimonials" id="testimonials">
    <div class="container">
        <div class="llg-section-header" data-animate>
            <span class="llg-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="llg-section-divider"></div>
            <h2 class="llg-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="llg-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="llg-testimonials-slider">
            <div class="llg-testimonial" data-animate>
                <div class="llg-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <blockquote>Lexington Legal Group guided us through a complex international acquisition with exceptional skill. Their attention to detail and strategic foresight saved our company millions and positioned us for future growth.</blockquote>
                </div>
                <div class="llg-testimonial-author">
                    <div class="llg-author-avatar">
                        <span>SR</span>
                    </div>
                    <div class="llg-author-info">
                        <h4>Sarah Reynolds</h4>
                        <p>CEO, TechNova Inc.</p>
                    </div>
                </div>
            </div>
            <div class="llg-testimonial" data-animate>
                <div class="llg-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <blockquote>When our intellectual property was challenged, their team mounted a formidable defense that not only protected our patents but strengthened our market position. They are true partners in innovation.</blockquote>
                </div>
                <div class="llg-testimonial-author">
                    <div class="llg-author-avatar">
                        <span>MJ</span>
                    </div>
                    <div class="llg-author-info">
                        <h4>Michael Johnson</h4>
                        <p>Founder, BioGen Labs</p>
                    </div>
                </div>
            </div>
            <div class="llg-testimonial" data-animate>
                <div class="llg-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <blockquote>The corporate restructuring advice we received was transformative. They understood our business deeply and provided solutions that balanced legal precision with practical business sense.</blockquote>
                </div>
                <div class="llg-testimonial-author">
                    <div class="llg-author-avatar">
                        <span>EC</span>
                    </div>
                    <div class="llg-author-info">
                        <h4>Elena Chen</h4>
                        <p>CFO, Global Manufacturing Partners</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="llg-testimonials-logos">
            <div class="llg-logo-item" data-animate>
                <span>Fortune 500</span>
            </div>
            <div class="llg-logo-item" data-animate>
                <span>Tech Review</span>
            </div>
            <div class="llg-logo-item" data-animate>
                <span>Legal Elite</span>
            </div>
            <div class="llg-logo-item" data-animate>
                <span>Global 100</span>
            </div>
        </div>
    </div>
</section>
