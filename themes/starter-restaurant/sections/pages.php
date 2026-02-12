<?php
/**
 * Starter Restaurant — Explore Section (pages as overlay tiles)
 * Photo-forward cards with overlay text
 * Variables inherited from parent scope
 */
$menuIcons = [
    'our-menu' => 'fas fa-book-open',
    'reservations' => 'fas fa-calendar-check',
    'events' => 'fas fa-glass-cheers',
    'gallery' => 'fas fa-camera',
];
$menuDescriptions = [
    'our-menu' => 'Explore our seasonal dishes, handcrafted with the finest ingredients',
    'reservations' => 'Book your table for an unforgettable dining experience',
    'events' => 'Private dining, wine tastings, and special culinary evenings',
    'gallery' => 'A glimpse into our kitchen, dining room, and signature dishes',
];
?>
<!-- Explore / Pages Section -->
<?php if (!empty($pages)): ?>
<section class="section" id="menu">
    <div class="container">
        <div class="section-header" data-animate>
            <span class="section-label" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <div class="section-divider"></div>
            <h2 class="section-title" data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p class="section-desc" data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="card-grid">
            <?php foreach ($pages as $p): 
                $icon = $menuIcons[$p['slug']] ?? 'fas fa-utensils';
                $desc = $menuDescriptions[$p['slug']] ?? esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...'));
            ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="card-overlay" data-animate>
                <?php if (!empty($p['featured_image'])): ?>
                <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" loading="lazy">
                <?php else: ?>
                <div class="card-ph"><i class="<?= $icon ?>"></i></div>
                <?php endif; ?>
                <div class="card-overlay-content">
                    <div class="card-overlay-icon"><i class="<?= $icon ?>"></i></div>
                    <h3><?= esc($p['title']) ?></h3>
                    <p><?= $desc ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
