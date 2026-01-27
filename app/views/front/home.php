<?php
$pageTitle = null;
require_once __DIR__ . '/layouts/header.php';
?>
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="tag">✨ AI-Powered CMS</span>
            <h1>Build Smarter with<br><span class="gradient-text">Jessie AI-CMS</span></h1>
            <p class="hero-desc">The next-generation content management system powered by AI. Built with pure PHP, zero dependencies.</p>
            <div class="hero-actions">
                <a href="/admin/login" class="btn btn-primary btn-lg">Start Free →</a>
                <a href="/features" class="btn btn-secondary btn-lg">View Features</a>
            </div>
        </div>
    </div>
</section>

<section class="features-section">
    <div class="container">
        <div class="section-header">
            <span class="tag tag-cyan">Features</span>
            <h2>Everything You Need</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card card">
                <div class="feature-icon">🤖</div>
                <h3>AI Content</h3>
                <p>Generate articles and SEO content using AI.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon">📊</div>
                <h3>SEO Analysis</h3>
                <p>Real-time SEO scores and optimization tips.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon">🔗</div>
                <h3>Smart Linking</h3>
                <p>AI-powered internal linking suggestions.</p>
            </div>
            <div class="feature-card card">
                <div class="feature-icon">⚡</div>
                <h3>Lightning Fast</h3>
                <p>Pure PHP with no framework overhead.</p>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($articles)): ?>
<section class="articles-section">
    <div class="container">
        <div class="section-header">
            <span class="tag">Blog</span>
            <h2>Latest Articles</h2>
        </div>
        <div class="articles-grid">
            <?php foreach ($articles as $a): ?>
            <article class="article-card card">
                <?php if (!empty($a['featured_image'])): ?>
                <div class="article-image" style="background-image: url('<?= esc($a['featured_image']) ?>')"></div>
                <?php else: ?>
                <div class="article-image placeholder"><span>📝</span></div>
                <?php endif; ?>
                <div class="article-body">
                    <h3><a href="/article/<?= esc($a['slug']) ?>"><?= esc($a['title']) ?></a></h3>
                    <div class="article-meta">
                        <span><?= date('M j, Y', strtotime($a['published_at'] ?? $a['created_at'])) ?></span>
                        <a href="/article/<?= esc($a['slug']) ?>" class="read-more">Read more →</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="section-footer"><a href="/articles" class="btn btn-secondary">View All Articles →</a></div>
    </div>
</section>
<?php endif; ?>

<style>
.hero { min-height: 80vh; display: flex; align-items: center; padding: 120px 0 80px; text-align: center; }
.hero h1 { margin: 20px 0; }
.hero-desc { font-size: 1.2rem; max-width: 600px; margin: 0 auto 32px; }
.hero-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.features-section, .articles-section { padding: 100px 0; }
.section-header { text-align: center; max-width: 600px; margin: 0 auto 48px; }
.section-header .tag { margin-bottom: 16px; }
.section-header h2 { margin-top: 12px; }
.features-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; }
@media (max-width: 900px) { .features-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .features-grid { grid-template-columns: 1fr; } }
.feature-card { padding: 32px; text-align: center; }
.feature-icon { font-size: 2.5rem; margin-bottom: 16px; }
.feature-card h3 { margin-bottom: 12px; font-size: 1.2rem; }
.articles-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
@media (max-width: 900px) { .articles-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .articles-grid { grid-template-columns: 1fr; } }
.article-card { overflow: hidden; }
.article-image { height: 180px; background-size: cover; background-position: center; background-color: var(--bg-tertiary); }
.article-image.placeholder { display: flex; align-items: center; justify-content: center; font-size: 3rem; background: var(--gradient-primary); }
.article-body { padding: 20px; }
.article-body h3 { font-size: 1.1rem; margin-bottom: 12px; }
.article-body h3 a { color: var(--text-primary); }
.article-body h3 a:hover { color: var(--accent-primary); }
.article-meta { display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--text-muted); }
.read-more { color: var(--accent-primary); }
.section-footer { text-align: center; margin-top: 40px; }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
