<?php
$faqLabel = theme_get('faq.label', 'Questions & Answers');
$faqTitle = theme_get('faq.title', 'Everything You Need to Know');
$faqDesc = theme_get('faq.description', 'Get answers to common questions about our autonomous delivery platform and how it can transform your logistics.');
$faqBtnText = theme_get('faq.btn_text', 'View All FAQs');
$faqBtnLink = theme_get('faq.btn_link', '/faq');
?>
<section class="section faq-section" id="faq">
    <div class="faq-accent-shape"></div>
    <div class="container">
        <div class="faq-layout">
            <div class="faq-intro" data-animate>
                <span class="section-label" data-ts="faq.label"><?= esc($faqLabel) ?></span>
                <div class="section-divider"></div>
                <h2 class="section-title" data-ts="faq.title"><?= esc($faqTitle) ?></h2>
                <p class="section-desc" data-ts="faq.description"><?= esc($faqDesc) ?></p>
                <a href="<?= esc($faqBtnLink) ?>" 
                   class="btn btn-primary"
                   data-ts="faq.btn_text"
                   data-ts-href="faq.btn_link">
                    <?= esc($faqBtnText) ?>
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div class="faq-support">
                    <div class="support-icon"><i class="fas fa-headset"></i></div>
                    <div class="support-text">
                        <span>Still have questions?</span>
                        <a href="#contact">Contact our team</a>
                    </div>
                </div>
            </div>
            
            <div class="faq-list" data-animate>
                <div class="faq-item">
                    <button class="faq-trigger">
                        <span class="faq-question" data-ts="faq.q1"><?= esc(theme_get('faq.q1', 'How do the autonomous robots navigate urban areas?')) ?></span>
                        <span class="faq-icon"><i class="fas fa-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p data-ts="faq.a1"><?= esc(theme_get('faq.a1', 'Our robots use a combination of LiDAR, computer vision, and GPS to navigate sidewalks and pedestrian areas safely. They can detect obstacles, traffic signals, and pedestrians in real-time, ensuring safe and efficient delivery routes.')) ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-trigger">
                        <span class="faq-question" data-ts="faq.q2"><?= esc(theme_get('faq.q2', 'What is the delivery radius and capacity?')) ?></span>
                        <span class="faq-icon"><i class="fas fa-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p data-ts="faq.a2"><?= esc(theme_get('faq.a2', 'Each robot can carry up to 25 lbs of cargo and operates within a 3-mile radius of fulfillment hubs. For larger coverage areas, we strategically position multiple hubs to ensure comprehensive service.')) ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-trigger">
                        <span class="faq-question" data-ts="faq.q3"><?= esc(theme_get('faq.q3', 'How does the AI route optimization work?')) ?></span>
                        <span class="faq-icon"><i class="fas fa-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p data-ts="faq.a3"><?= esc(theme_get('faq.a3', 'Our AI analyzes real-time traffic patterns, weather conditions, delivery windows, and historical data to calculate the most efficient routes. It continuously learns and adapts, improving delivery times by up to 35%.')) ?></p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-trigger">
                        <span class="faq-question" data-ts="faq.q4"><?= esc(theme_get('faq.q4', 'What happens if a robot encounters an issue?')) ?></span>
                        <span class="faq-icon"><i class="fas fa-plus"></i></span>
                    </button>
                    <div class="faq-answer">
                        <p data-ts="faq.a4"><?= esc(theme_get('faq.a4', 'Our 24/7 operations center monitors all robots in real-time. If a robot encounters an obstacle or malfunction, operators can remotely assist or dispatch a field technician within minutes. Customer deliveries are never left stranded.')) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
