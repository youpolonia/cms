<?php
$pageTitle = $page['title'] ?? 'Page';
require_once __DIR__ . '/layouts/header.php';
?>
<article class="landing-page">
    <?php if (!empty($page['featured_image'])): ?>
    <section class="landing-hero">
        <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" class="hero-bg">
        <div class="hero-overlay">
            <h1><?= esc($page['title']) ?></h1>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="hero-subtitle"><?= esc($page['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php else: ?>
    <section class="landing-hero-simple">
        <h1><?= esc($page['title']) ?></h1>
        <?php if (!empty($page['excerpt'])): ?>
        <p class="hero-subtitle"><?= esc($page['excerpt']) ?></p>
        <?php endif; ?>
    </section>
    <?php endif; ?>
    
    <div class="landing-content">
        <?= $page['content'] ?? '' ?>
    </div>
</article>

<style>
.landing-page { padding-top: 0; }
.landing-hero { position: relative; height: 80vh; min-height: 500px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.landing-hero .hero-bg { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
.landing-hero .hero-overlay { position: relative; z-index: 2; text-align: center; padding: 40px; max-width: 900px; }
.landing-hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.6)); z-index: 1; }
.landing-hero h1 { color: white; font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 700; text-shadow: 0 4px 30px rgba(0,0,0,0.5); margin-bottom: 20px; }
.landing-hero .hero-subtitle { color: rgba(255,255,255,0.9); font-size: clamp(1.1rem, 2vw, 1.4rem); max-width: 600px; margin: 0 auto; }
.landing-hero-simple { padding: 180px 24px 80px; text-align: center; background: linear-gradient(135deg, var(--surface-secondary) 0%, var(--surface-primary) 100%); }
.landing-hero-simple h1 { font-size: clamp(2.5rem, 6vw, 4rem); margin-bottom: 20px; }
.landing-hero-simple .hero-subtitle { font-size: 1.25rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto; }
.landing-content { max-width: 1000px; margin: 0 auto; padding: 80px 24px; line-height: 1.8; font-size: 1.1rem; }
.landing-content h2 { font-size: 2.25rem; margin: 60px 0 30px; text-align: center; }
.landing-content h3 { font-size: 1.5rem; margin: 40px 0 20px; }
.landing-content p { margin-bottom: 24px; }
.landing-content a { color: var(--accent-primary); }
.landing-content img { max-width: 100%; height: auto; border-radius: var(--radius-lg); margin: 40px 0; box-shadow: 0 20px 60px rgba(0,0,0,0.15); }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
