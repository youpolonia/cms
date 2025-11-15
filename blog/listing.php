<?php
// Blog listing view
ob_start();
?><main class="container">
    <h1>Blog Posts</h1>
    <div class="blog-list">
        <?php foreach ($posts as $post): ?>
<article class="blog-post-summary">
                <h2><a href="/blog/post/<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
                <div class="post-meta">
                    <span class="post-date"><?= date('F j, Y', strtotime($post['date'])) ?></span>
                </div>
                <div class="post-excerpt">
                    <p><?= htmlspecialchars($post['excerpt']) ?></p>
                </div>
                <a href="/blog/post/<?= $post['slug'] ?>" class="read-more">Read More</a>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/includes/layout.php';
render_layout('Blog Posts', $content);
