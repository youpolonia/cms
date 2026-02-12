<?php
/**
 * Starter Restaurant — Pages Section
 * Variables inherited from parent scope: $pagesLabel, $pagesTitle, $pagesDesc, $pages
 */
?>
<!-- Pages as Menu Categories -->
<?php if (!empty($pages)): ?>
<section class="section menu-section" id="pages">
    <div class="container">
        <div class="section-header">
            <span class="section-label" data-ts="pages.label"><?= esc($pagesLabel) ?></span>
            <h2 class="section-title" data-ts="pages.title"><?= esc($pagesTitle) ?></h2>
            <p class="section-desc" data-ts="pages.description"><?= esc($pagesDesc) ?></p>
        </div>
        <div class="menu-grid">
            <?php foreach ($pages as $p): ?>
            <a href="/page/<?= esc($p['slug']) ?>" class="menu-card" style="text-decoration:none" data-animate>
                <div class="menu-card-img">
                    <?php if (!empty($p['featured_image'])): ?>
                    <img src="<?= esc($p['featured_image']) ?>" alt="<?= esc($p['title']) ?>" style="width:100%;height:220px;object-fit:cover">
                    <?php else: ?>
                    <div class="img-placeholder menu-ph"><i class="fas fa-file-alt"></i></div>
                    <?php endif; ?>
                </div>
                <div class="menu-card-body">
                    <div class="menu-card-header">
                        <h3><?= esc($p['title']) ?></h3>
                    </div>
                    <p><?= esc(mb_strimwidth(strip_tags($p['content']), 0, 120, '...')) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
