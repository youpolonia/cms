<section class="page-header">
    <div class="page-header-content">
        <div class="container">
            <h1 class="page-title"><?= esc($page['title']) ?></h1>
            <div class="breadcrumbs">
                <a href="/">Home</a>
                <span class="breadcrumb-sep"><i class="fas fa-chevron-right"></i></span>
                <span class="breadcrumb-current"><?= esc($page['title']) ?></span>
            </div>
        </div>
    </div>
</section>

<section class="page-content-section">
    <div class="container container-narrow">
        <?php if (!empty($page['featured_image'])): ?>
        <div style="margin-bottom:40px;border-radius:12px;overflow:hidden">
            <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>" style="width:100%">
        </div>
        <?php endif; ?>

        <div class="prose">
            <?= $page['content'] ?>
        </div>
    </div>
</section>
