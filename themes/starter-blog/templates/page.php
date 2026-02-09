<section class="page-content">
    <div class="container-narrow">
        <article class="page-article">
            <h1><?= esc($page['title']) ?></h1>

            <?php if (!empty($page['featured_image'])): ?>
            <div class="page-featured-image">
                <img src="<?= esc($page['featured_image']) ?>" alt="<?= esc($page['title']) ?>">
            </div>
            <?php endif; ?>

            <div class="content-body">
                <?= $page['content'] ?>
            </div>
        </article>
    </div>
</section>
