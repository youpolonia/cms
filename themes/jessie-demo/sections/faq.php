<?php /** FAQ Section */ ?>
<section class="jd-section" id="faq">
    <div class="jd-section-header jd-fade-up">
        <span class="jd-section-badge"><i class="fas fa-question-circle"></i> FAQ</span>
        <h2 class="jd-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="jd-faq-list">
        <?php
        $faqs = [
            ['q' => 'Do I need a framework like Laravel or Symfony?', 'a' => 'No. Jessie CMS is built from scratch with pure PHP 8.2+. Zero Composer, zero framework dependencies. It runs on any hosting that supports PHP and MySQL — even shared hosting with FTP access.'],
            ['q' => 'Which AI providers are supported?', 'a' => 'Jessie CMS supports OpenAI (GPT-4, GPT-5), Anthropic (Claude), DeepSeek, Google (Gemini), and HuggingFace. You can switch providers freely — bring your own API keys.'],
            ['q' => 'Can I use it for e-commerce?', 'a' => 'Absolutely. The built-in e-commerce plugin includes products, variants, orders, coupons, digital downloads, wishlists, and reviews. Plus AI-powered SEO optimization and image processing. Dropshipping module included.'],
            ['q' => 'What is the Theme Builder vs JTB?', 'a' => 'The AI Theme Builder generates complete themes (HTML+CSS+content) from scratch using AI. JTB (Jessie Theme Builder) is a visual drag & drop page builder with 79 modules. They work independently but complement each other.'],
            ['q' => 'Can I run it as a SaaS platform?', 'a' => 'Yes! Jessie CMS includes a full SaaS platform with 6 AI tools (SEO Writer, Copywriter, Image Studio, Social Media, Email Marketing, Analytics). Multi-tenant architecture with credit-based billing and API gateway.'],
            ['q' => 'How many themes can I generate?', 'a' => 'Unlimited. The AI Theme Builder creates unique themes every time — with 960+ visual variation combinations. Currently 49 themes are pre-generated, covering industries from restaurants to fintech.'],
            ['q' => 'What kind of support is available?', 'a' => 'Every license includes access to documentation, updates, and support. Higher tiers include priority support and dedicated assistance for setup and customization.'],
        ];
        foreach ($faqs as $f): ?>
        <div class="jd-faq-item jd-fade-up">
            <button class="jd-faq-question">
                <?= $f['q'] ?>
                <span class="jd-faq-icon"><i class="fas fa-plus"></i></span>
            </button>
            <div class="jd-faq-answer">
                <div class="jd-faq-answer-inner"><?= $f['a'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
