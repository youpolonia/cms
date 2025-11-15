<?php Template::render('includes/header'); ?>
<div class="container">
    <article class="blog-post-full">
        <h1><?= htmlspecialchars($post->title) ?></h1>
        <div class="meta">
            <span class="date">Posted on <?= $post->getCreatedDate() ?></span>
        </div>
        <div class="content">
            <?= $post->content ?>
        </div>
        
        <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
<div class="admin-actions">
                <a href="/blog/<?= $post->id ?>/edit" class="btn">Edit Post</a>
                <form action="/blog/<?= $post->id ?>" method="POST" onsubmit="
return confirm('Are you sure?')">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn danger">Delete Post</button>
                </form>
            </div>
        <?php endif; ?>
    </article>
</div>

<?php Template::render('includes/footer');
