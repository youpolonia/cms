<?php
$pageTitle = 'Features';
require_once __DIR__ . '/layouts/header.php';
?>
<section class="page-hero">
    <div class="container">
        <span class="tag">Features</span>
        <h1>Powerful Features for<br><span class="gradient-text">Modern Content</span></h1>
        <p>Everything you need to create, manage, and optimize your content.</p>
    </div>
</section>

<section class="features-list">
    <div class="container">
        <div class="feature-row">
            <div class="feature-content">
                <span class="tag tag-cyan">AI Engine</span>
                <h2>AI Content Generation</h2>
                <p>Generate high-quality articles, meta descriptions, and SEO content using advanced AI models including OpenAI, Anthropic Claude, and HuggingFace.</p>
            </div>
            <div class="feature-visual">🤖</div>
        </div>
        <div class="feature-row reverse">
            <div class="feature-content">
                <span class="tag">SEO Tools</span>
                <h2>SEO Analysis & Optimization</h2>
                <p>Get real-time SEO scores, keyword suggestions, content gap analysis, and optimization recommendations.</p>
            </div>
            <div class="feature-visual">📊</div>
        </div>
        <div class="feature-row">
            <div class="feature-content">
                <span class="tag tag-cyan">Linking</span>
                <h2>Smart Internal Linking</h2>
                <p>AI-powered internal linking suggestions. Find orphan pages, build topic clusters, and improve site architecture.</p>
            </div>
            <div class="feature-visual">🔗</div>
        </div>
    </div>
</section>

<style>
.page-hero { padding: 160px 0 80px; text-align: center; }
.page-hero .tag { margin-bottom: 20px; }
.page-hero h1 { margin-bottom: 20px; }
.page-hero p { font-size: 1.2rem; max-width: 500px; margin: 0 auto; }
.features-list { padding: 60px 0 100px; }
.feature-row { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; padding: 60px 0; border-bottom: 1px solid var(--border); }
.feature-row:last-child { border-bottom: none; }
.feature-row.reverse { direction: rtl; }
.feature-row.reverse > * { direction: ltr; }
.feature-content .tag { margin-bottom: 16px; }
.feature-content h2 { margin-bottom: 20px; }
.feature-content p { font-size: 1.1rem; line-height: 1.8; }
.feature-visual { font-size: 8rem; text-align: center; }
@media (max-width: 768px) {
    .feature-row, .feature-row.reverse { grid-template-columns: 1fr; text-align: center; direction: ltr; }
    .feature-visual { font-size: 5rem; order: -1; }
}
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
