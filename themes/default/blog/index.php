<?php
/**
 * Blog Listing Template
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - <?= htmlspecialchars($_ENV['SITE_NAME'] ?? 'CMS') ?></title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <header>
        <h1>Blog</h1>
    </header>

    <main>
        <?php if (!empty($posts)): ?>
            <div class="blog-list">
                <?php foreach ($posts as $post): ?>
                    <article class="blog-post">
                        <h2><a href="/blog/<?= htmlspecialchars($post['slug']) ?>">
                            <?= htmlspecialchars($post['title']) ?>
                        </a></h2>
                        <time datetime="<?= htmlspecialchars($post['published_at']) ?>">
                            <?= date('F j, Y', strtotime($post['published_at'])) ?>
                        </time>
                        <p><?= htmlspecialchars($post['excerpt']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="/blog?page=<?= $page - 1 ?>">&laquo; Previous</a>
                <?php endif; ?>
                <?php if ($page * $perPage < $totalPosts): ?>
                    <a href="/blog?page=<?= $page + 1 ?>">Next &raquo;</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p>No blog posts found.</p>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($_ENV['SITE_NAME'] ?? 'CMS') ?></p>
    </footer>
</body>
</html>
