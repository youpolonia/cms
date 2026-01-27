<?php
/**
 * Jessie Theme - Landing Page Template
 * Hero section with optional featured image + content sections
 *
 * @var array $page Page data array
 * @var string $content Raw content (fallback)
 */
?>
<article class="landing-page">
    <?php if (!empty($page['featured_image'])): ?>
    <section class="landing-hero">
        <img src="<?= htmlspecialchars($page['featured_image']) ?>" alt="<?= htmlspecialchars($page['title'] ?? '') ?>" class="hero-bg">
        <div class="hero-overlay">
            <div class="hero-content">
                <h1 class="gradient-text"><?= htmlspecialchars($page['title'] ?? '') ?></h1>
                <?php if (!empty($page['excerpt'])): ?>
                <p class="hero-subtitle"><?= htmlspecialchars($page['excerpt']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php else: ?>
    <section class="landing-hero-simple">
        <div class="container">
            <h1 class="gradient-text"><?= htmlspecialchars($page['title'] ?? '') ?></h1>
            <?php if (!empty($page['excerpt'])): ?>
            <p class="hero-subtitle"><?= htmlspecialchars($page['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <div class="landing-content">
        <div class="container">
            <div class="content-body">
                <?= $page['content'] ?? $content ?? '' ?>
            </div>
        </div>
    </div>
</article>
