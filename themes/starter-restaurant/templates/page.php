<section class="page-hero">
    <div class="page-hero-overlay"></div>
    <div class="container">
        <h1 class="page-hero-title"><?= esc($page['title']) ?></h1>
        <div class="page-breadcrumb">
            <a href="/">Home</a>
            <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
            <span><?= esc($page['title']) ?></span>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <?php if (!empty($page['featured_image'])): ?>
        <div style="margin-bottom:40px;border-radius:8px;overflow:hidden">
            <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" style="width:100%">
        </div>
        <?php endif; ?>

        <div class="prose">
            <?= $page['content'] ?>
        </div>
    </div>
</section>
