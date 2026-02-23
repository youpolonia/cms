<?php
$faqLabel = theme_get('faq.label', 'Your Questions');
$faqTitle = theme_get('faq.title', 'Frequently Asked Questions');
$faqDesc = theme_get('faq.description', 'Find answers to common questions about our methodology, scheduling, and learning experience.');
?>
<section class="lsa-section lsa-faq" id="faq">
    <div class="container">
        <div class="lsa-section-header" data-animate>
            <span class="lsa-section-label" data-ts="faq.label"><?= esc($faqLabel) ?></span>
            <div class="lsa-section-divider"></div>
            <h2 class="lsa-section-title" data-ts="faq.title"><?= esc($faqTitle) ?></h2>
            <p class="lsa-section-desc" data-ts="faq.description"><?= esc($faqDesc) ?></p>
        </div>

        <div class="lsa-faq-grid">
            <div class="lsa-faq-column">
                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>How does the conversation-first method work?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>We prioritize real dialogue from day one. Instead of starting with grammar rules, you'll engage in practical conversations tailored to your level, with immediate feedback from native-speaking instructors. Grammar and vocabulary are taught contextually within these conversations.</p>
                    </div>
                </div>

                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>What if I'm a complete beginner?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>Perfect! Our beginner tracks start with survival phrases and simple dialogues. You'll learn through visual cues, repetition, and supportive group practice. Many beginners achieve basic conversational ability within their first month.</p>
                    </div>
                </div>

                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>How are cultural exchange sessions structured?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>Each month includes themed cultural modules: virtual tours, holiday celebrations, cuisine discussions, and current events analysis. You'll connect with native speakers and fellow learners to explore the living context of the language.</p>
                    </div>
                </div>
            </div>

            <div class="lsa-faq-column">
                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>Can I switch between languages?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>Yes! Many students study multiple languages simultaneously. Your account gives you access to all four language platforms. You can allocate your session credits across languages as you prefer.</p>
                    </div>
                </div>

                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>What technology do I need?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>A computer, tablet, or smartphone with a stable internet connection, microphone, and camera. We use a browser-based platform that works on all major devices—no special software required.</p>
                    </div>
                </div>

                <div class="lsa-faq-item" data-animate>
                    <button class="lsa-faq-question" aria-expanded="false">
                        <span>Is there a free trial available?</span>
                        <i class="fas fa-chevron-down lsa-faq-icon"></i>
                    </button>
                    <div class="lsa-faq-answer">
                        <p>Absolutely! We offer a 7-day trial with access to two group sessions and our introductory cultural module. No credit card required to start. <a href="/trial">Sign up for your trial here</a>.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="lsa-faq-cta" data-animate>
            <p>Still have questions? Our language advisors are here to help.</p>
            <a href="/contact" class="lsa-btn lsa-btn-secondary">
                <i class="fas fa-comments"></i>
                Contact Support
            </a>
        </div>
    </div>
</section>
