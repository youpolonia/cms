<?php Template::render('includes/header'); ?>
<div class="container">
    <h1>Blog Posts</h1>

    <?php if (empty($posts)): ?>
<p>No blog posts found.</p>
    <?php else: ?>
<div class="blog-list">
            <?php foreach ($posts as $post): ?>
<article class="blog-post">
                    <h2><a href="/blog/<?= $post->id ?>"><?= htmlspecialchars($post->title) ?></a></h2>
                    <div class="meta">
                        <span class="date">Posted on <?= $post->getCreatedDate() ?></span>
                    </div>
                    <div class="excerpt">
                        <?= $post->getExcerpt() ?>
                    </div>
<a href="/blog/<?= $post->id ?>" class="read-more">Read More</a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php Template::render('includes/footer');
