<section class="hero">
    <div class="container">
        <div class="hero-content">
            <span class="tag">✨ AI-Powered CMS</span>
            <h1>Build Smarter with <span class="gradient-text">Jessie AI-CMS</span></h1>
            <p>The next-generation content management system powered by artificial intelligence.</p>
            <div class="hero-actions">
                <a href="/admin/login" class="btn btn-primary">Get Started →</a>
                <a href="/page/about" class="btn btn-secondary">Learn More</a>
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

<style>
.hero {
    min-height: 80vh;
    display: flex;
    align-items: center;
    text-align: center;
    padding: 60px 0;
    background: linear-gradient(180deg, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
}
.hero-content { max-width: 800px; margin: 0 auto; }
.tag {
    display: inline-block;
    padding: 8px 16px;
    background: rgba(139, 92, 246, 0.2);
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 9999px;
    font-size: 0.875rem;
    color: #a78bfa;
    margin-bottom: 24px;
}
.hero h1 { 
    font-size: 3.5rem; 
    font-weight: 800; 
    margin-bottom: 24px;
    line-height: 1.1;
}
.gradient-text {
    background: linear-gradient(135deg, #8b5cf6, #06b6d4);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.hero p { 
    font-size: 1.25rem; 
    margin-bottom: 32px; 
    max-width: 600px; 
    margin-left: auto; 
    margin-right: auto;
    color: #a1a1aa;
}
.hero-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; }
.btn {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 12px;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary {
    background: linear-gradient(135deg, #8b5cf6, #6366f1);
    color: #fff;
    box-shadow: 0 4px 20px rgba(139, 92, 246, 0.4);
}
.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 30px rgba(139, 92, 246, 0.5);
}
.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}
.featured-section { padding: 80px 0; }
</style>
