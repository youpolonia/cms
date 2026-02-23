<?php
$testimonialsLabel = theme_get('testimonials.label', 'Client Love');
$testimonialsTitle = theme_get('testimonials.title', 'What Our Clients Say');
$testimonialsDesc = theme_get('testimonials.description', 'Hear from DTC founders and marketing directors who have transformed their growth with our strategies.');
?>
<section class="sf-section sf-testimonials">
    <div class="container">
        <div class="sf-section__header" data-animate>
            <span class="sf-section__label" data-ts="testimonials.label"><?= esc($testimonialsLabel) ?></span>
            <div class="sf-section__divider"></div>
            <h2 class="sf-section__title" data-ts="testimonials.title"><?= esc($testimonialsTitle) ?></h2>
            <p class="sf-section__desc" data-ts="testimonials.description"><?= esc($testimonialsDesc) ?></p>
        </div>
        <div class="sf-testimonials__slider">
            <div class="sf-testimonials__track">
                <div class="sf-testimonial" data-animate>
                    <div class="sf-testimonial__content">
                        <i class="fas fa-quote-left sf-testimonial__quote"></i>
                        <p class="sf-testimonial__text">"Scarlet Flow transformed our paid acquisition strategy. In 6 months, our ROAS went from 2.5x to 4.8x while decreasing CPA by 35%. Their team is incredibly data-driven and responsive."</p>
                    </div>
                    <div class="sf-testimonial__author">
                        <div class="sf-testimonial__avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="sf-testimonial__info">
                            <h4 class="sf-testimonial__name">Alex Morgan</h4>
                            <p class="sf-testimonial__role">CEO, Luxe Apparel Co.</p>
                        </div>
                    </div>
                </div>
                <div class="sf-testimonial" data-animate>
                    <div class="sf-testimonial__content">
                        <i class="fas fa-quote-left sf-testimonial__quote"></i>
                        <p class="sf-testimonial__text">"The conversion optimization work they did on our checkout flow increased our completion rate by 42%. Their A/B testing methodology is rigorous and their insights are always actionable."</p>
                    </div>
                    <div class="sf-testimonial__author">
                        <div class="sf-testimonial__avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="sf-testimonial__info">
                            <h4 class="sf-testimonial__name">Jamie Chen</h4>
                            <p class="sf-testimonial__role">Marketing Director, VitaBoost</p>
                        </div>
                    </div>
                </div>
                <div class="sf-testimonial" data-animate>
                    <div class="sf-testimonial__content">
                        <i class="fas fa-quote-left sf-testimonial__quote"></i>
                        <p class="sf-testimonial__text">"As a tech DTC brand, we needed a partner who understood our complex customer journey. Scarlet Flow's integrated approach across SEO, content, and paid social delivered 3x growth in 9 months."</p>
                    </div>
                    <div class="sf-testimonial__author">
                        <div class="sf-testimonial__avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="sf-testimonial__info">
                            <h4 class="sf-testimonial__name">Taylor Reed</h4>
                            <p class="sf-testimonial__role">Founder, GadgetFlow</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sf-testimonials__controls">
                <button class="sf-test