<?php
/**
 * Home Template — Lens & Light Photography
 * Full-screen hero → Horizontal work strip → About → Contact
 */

// Get recent articles for "Selected Work"
$_articles = [];
try {
    $_pdo = \core\Database::connection();
    $_stmt = $_pdo->query("
        SELECT a.*, 
            (SELECT filename FROM media WHERE id = a.featured_image_id LIMIT 1) as featured_image
        FROM articles a 
        WHERE a.status = 'published' 
        ORDER BY a.published_at DESC 
        LIMIT 8
    ");
    $_articles = $_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {}
?>

<!-- Hero Fullscreen -->
<section class="hero-fullscreen" style="background-image: url('/uploads/media/demo_starter-photography_3.jpg');">
    <div class="hero-content">
        <span class="hero-label">Photography Portfolio</span>
        <h1>Capturing Light<br>& Shadow</h1>
        <p>Fine art and documentary photography exploring the space between darkness and illumination.</p>
    </div>
    <div class="hero-scroll-hint">Scroll</div>
</section>

<!-- Selected Work — Horizontal Scroll -->
<?php if (!empty($_articles)): ?>
<section class="selected-work">
    <div class="section-header fade-in">
        <span class="section-label">Portfolio</span>
        <h2 class="section-title">Selected Work</h2>
    </div>
    <div class="horizontal-scroll">
        <?php foreach ($_articles as $_a): ?>
        <a href="/article/<?= esc($_a['slug']) ?>" class="work-item">
            <?php if (!empty($_a['featured_image'])): ?>
            <img src="/uploads/media/<?= esc($_a['featured_image']) ?>" alt="<?= esc($_a['title']) ?>" loading="lazy">
            <?php else: ?>
            <div style="width:100%;height:100%;background:#1a1a1a;"></div>
            <?php endif; ?>
            <div class="work-item-overlay">
                <div>
                    <h3><?= esc($_a['title']) ?></h3>
                    <span><?= date('Y', strtotime($_a['published_at'])) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- About Strip -->
<section class="about-strip">
    <div class="about-grid fade-in">
        <div class="about-text">
            <span class="section-label">About</span>
            <h2 class="about-heading">Stories told through a lens</h2>
            <p>Every frame is an invitation to see the world differently. With a focus on natural light, bold contrasts, and raw human emotion, each photograph seeks to capture moments that words cannot.</p>
            <p>Based in London. Available worldwide.</p>
            <a href="/about" class="about-link">Learn More →</a>
        </div>
        <div class="about-image">
            <img src="/uploads/media/demo_starter-photography_1.jpg" alt="Portrait" loading="lazy">
        </div>
    </div>
</section>

<!-- Contact Strip -->
<section class="contact-strip fade-in">
    <span class="section-label">Get in Touch</span>
    <h2>Let's Work Together</h2>
    <p>Available for commissions, collaborations, and editorial assignments.</p>
    <a href="/contact" class="contact-email">hello@lensandlight.com</a>
</section>
