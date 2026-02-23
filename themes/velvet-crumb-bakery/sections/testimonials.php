<?php
$testimonialsLabel = theme_get('testimonials.label', 'Testimonials');
$testimonialsTitle = theme_get('testimonials.title', 'Praised by Connoisseurs');
$testimonialsDesc = theme_get('testimonials.description', 'Discover why food critics, wedding planners, and dessert lovers consistently rate Velvet Crumb as the pinnacle of cheesecake artistry.');
?>
<section class="vcb-section vcb-testimonials" id="testimonials">
    <div class="container">
        <div class="vcb-section-header" data-animate>
            <span class="vcb-section-label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="vcb-section-divider"></div>
            <h2 class="vcb-section-title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="vcb-section-desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>

        <div class="vcb-testimonials-grid">
            <div class="vcb-testimonial-card" data-animate>
                <div class="vcb-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p>"The salted caramel cheesecake was the highlight of our corporate gifting. Elegant, delicious, and delivered with white-glove service."</p>
                </div>
                <div class="vcb-testimonial-author">
                    <div class="vcb-author-avatar">
                        <img src="https://images.pexels.com/photos/30632210/pexels-photo-30632210.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Satisfied corporate client">
                    </div>
                    <div class="vcb-author-info">
                        <h4>Alexandra Chen</h4>
                        <span>CFO, Sterling Capital</span>
                    </div>
                </div>
            </div>

            <div class="vcb-testimonial-card" data-animate>
                <div class="vcb-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p>"Our wedding guests are still talking about the seasonal berry trio cheesecake tower. It was as breathtaking as it was delicious."</p>
                </div>
                <div class="vcb-testimonial-author">
                    <div class="vcb-author-avatar">
                        <img src="https://images.pexels.com/photos/30846579/pexels-photo-30846579.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Happy wedding couple">
                    </div>
                    <div class="vcb-author-info">
                        <h4>Michael & Sofia</h4>
                        <span>Wedding Clients</span>
                    </div>
                </div>
            </div>

            <div class="vcb-testimonial-card" data-animate>
                <div class="vcb-testimonial-quote">
                    <i class="fas fa-quote-left"></i>
                    <p>"As a food critic, I've tasted hundreds of cheesecakes. Velvet Crumb's texture is flawless—creamy yet light, with perfectly balanced flavors."</p>
                </div>
                <div class="vcb-testimonial-author">
                    <div class="vcb-author-avatar">
                        <img src="https://images.pexels.com/photos/30846574/pexels-photo-30846574.jpeg?auto=compress&cs=tinysrgb&h=650&w=940" alt="Food critic">
                    </div>
                    <div class="vcb-author-info">
                        <h4>James Rivera</h4>
                        <span>Editor, Culinary Arts Magazine</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="vcb-testimonials-cta" data-animate>
            <p>Share your experience with us</p>
            <a href="/testimonials" class="vcb-btn vcb-btn-secondary">
                <span>Write a Review</span>
                <i class="fas fa-pen"></i>
            </a>
        </div>
    </div>
</section>
