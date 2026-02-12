<?php
/**
 * Starter Restaurant — Menu Section (pages)
 * Shows pages as menu categories (Our Menu, Reservations, Events, Gallery)
 * Variables inherited from parent scope: $pagesLabel, $pagesTitle, $pagesDesc, $pages
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
<!-- Menu / Pages Section -->
<?php if (!empty($pages)): ?>
<section class="section menu-section" id="menu">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 class="section-title" data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p class="section-desc" data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="menu-grid">
            <?php foreach ($pages as $p): 
                $icon = $menuIcons[$p['slug']] ?? 'fas fa-utensils';
                $desc = $menuDescriptions[$p['slug']] ?? esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...'));
            ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                <div class="menu-card-img">
                    <?php if (!empty($p['featured_image'])): ?>
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:220px;object-fit:cover">
                    <?php else: ?>
                    <div class="img-placeholder menu-ph"><i class="<?= $icon ?>"></i></div>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-header">
                        <h3><i class="<?= $icon ?>" style="margin-right:8px;opacity:0.7"></i><?= esc($p['title']) ?></h3>
                    </div>
                    <p><?= $desc ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
