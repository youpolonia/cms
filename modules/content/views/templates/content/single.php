<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? $post['title'] ?? 'Content' ?></title>
</head>
<body>
    <article class="content-single">
        <header>
            <h1><?= $post['title'] ?? 'Untitled' ?></h1>
            <div class="meta">
                <?php if (!empty($post['author'])): ?>
                    <span class="author">By <?= $post['author'] ?></span>
                <?php endif;  ?>                <?php if (!empty($post['date'])): ?>
                    <span class="date">Posted on <?= $post['date'] ?></span>
                <?php endif;  ?>                <?php if (!empty($post['categories'])): ?>
                    <div class="categories">
                        Categories: <?= implode(', ', $post['categories'])  ?>
                    </div>
                <?php endif;  ?>
            </div>
        </header>

        <div class="content">
            <?= $post['content'] ?? ''  ?>
        </div>

        <?php if (!empty($post['tags'])): ?>
            <footer class="tags">
                Tags: <?= implode(', ', $post['tags'])  ?>
            </footer>
        <?php endif;  ?>
    </article>
</body>
</html>
