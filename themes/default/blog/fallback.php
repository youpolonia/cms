<?php
/**
 * Fallback template for blog listing
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts</title>
    <style>
        .post { margin-bottom: 2rem; }
        .pagination { margin-top: 2rem; }
    </style>
</head>
<body>
    <h1>Blog Posts</h1>
    
    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>        <?php foreach ($posts as $post): ?>
            <article class="post">
                <h2><?= htmlspecialchars($post['title']) ?></h2>
                <time datetime="<?= $post['published_date'] ?>">
                    <?= date('F j, Y', strtotime($post['published_date'])) ?>
                </time>
                <p><?= htmlspecialchars($post['excerpt'] ?? '') ?></p>
                <a href="/blog/<?= $post['slug'] ?>">Read more</a>
            </article>
        <?php endforeach; ?>
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="/blog?page=<?= $currentPage - 1 ?>">Previous</a>
                <?php endif; ?>
                Page <?= $currentPage ?> of <?= $totalPages ?>                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="/blog?page=<?= $currentPage + 1 ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>    <?php endif; ?>
</body>
</html>
