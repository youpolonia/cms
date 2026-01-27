<?php
$pageTitle = $page['title'] ?? 'Page';
require_once __DIR__ . '/layouts/header.php';
?>
<article class="single-page full-width-page">
    <?php if (!empty($page['featured_image'])): ?>
    <div class="page-hero">
        <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>">
        <div class="page-hero-overlay">
            <h1><?= esc($page['title']) ?></h1>
        </div>
    </div>
    <?php else: ?>
    <header class="page-header-wide">
        <h1><?= esc($page['title']) ?></h1>
    </header>
    <?php endif; ?>
    
    <div class="page-content-wide">
        <?= $page['content'] ?? '' ?>
    </div>
</article>

<style>
.full-width-page { padding-top: 80px; }
.page-hero { position: relative; height: 50vh; min-height: 400px; overflow: hidden; }
.page-hero img { width: 100%; height: 100%; object-fit: cover; }
.page-hero-overlay { position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.7), transparent); display: flex; align-items: flex-end; justify-content: center; padding-bottom: 60px; }
.page-hero-overlay h1 { color: white; font-size: clamp(2rem, 5vw, 3.5rem); text-shadow: 0 2px 20px rgba(0,0,0,0.5); }
.page-header-wide { text-align: center; padding: 80px 24px 40px; background: var(--surface-secondary); }
.page-header-wide h1 { font-size: clamp(2rem, 5vw, 3.5rem); }
.page-content-wide { max-width: 100%; padding: 60px 5%; line-height: 1.8; font-size: 1.1rem; }
.page-content-wide h2 { margin: 50px 0 25px; font-size: 2rem; }
.page-content-wide h3 { margin: 40px 0 20px; font-size: 1.5rem; }
.page-content-wide p { margin-bottom: 24px; }
.page-content-wide a { color: var(--accent-primary); }
.page-content-wide img { max-width: 100%; height: auto; border-radius: var(--radius-md); margin: 30px 0; }
</style>
<?php require_once __DIR__ . '/layouts/footer.php'; ?>
