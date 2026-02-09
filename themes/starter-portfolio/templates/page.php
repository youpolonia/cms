<section class="page-hero">
    <h1 class="page-hero-title"><?= esc($page['title']) ?></h1>
    <?php if (!empty($page['created_at'])): ?>
    <div class="page-hero-meta"><?= date('M j, Y', strtotime($page['created_at'])) ?></div>
    <?php endif; ?>
</section>

<div class="page-content">
    <?php if (!empty($page['featured_image'])): ?>
    <div style="margin-bottom:40px;border-radius:12px;overflow:hidden;border:1px solid var(--color-border)">
        <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" style="width:100%">
    </div>
    <?php endif; ?>

    <div class="page-content">
        <?= $page['content'] ?>
    </div>
</div>
