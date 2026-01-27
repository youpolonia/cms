<?php
/**
 * Jessie Theme - Home Page Template
 * Landing hero section for homepage
 *
 * @var array $featuredContent Featured content (optional)
 */
?>
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="tag">AI-Powered CMS</span>
            <h1>Build Smarter with <span class="gradient-text">Jessie AI-CMS</span></h1>
            <p>The next-generation content management system powered by artificial intelligence.</p>
            <div class="hero-actions">
                <a href="/admin/login" class="btn btn-primary">Start Free →</a>
                <a href="/features" class="btn btn-secondary">Learn More</a>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredContent)): ?>
<section class="featured-section">
    <div class="container">
        <?= $featuredContent ?>
    </div>
</section>
<?php endif; ?>
