<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Content List' ?></title>
</head>
<body>
    <h1><?= $heading ?? 'Content List' ?></h1>
    
    <?php if (!empty($posts)): ?>
        <div class="content-list">
            <?php foreach ($posts as $post): ?>
                <article class="post">
                    <h2><a href="<?= $post['url'] ?>"><?= $post['title'] ?></a></h2>
                    <p class="meta">Posted on <?= $post['date'] ?></p>
                    <div class="excerpt"><?= $post['excerpt'] ?></div>
                </article>
            <?php endforeach;  ?>
        </div>
    <?php else: ?>
        <p>No content found.</p>
    <?php endif;  ?>
    <?php if (!empty($pagination)): ?>
        <div class="pagination">
            <?= $pagination  ?>
        </div>
    <?php endif;  ?>
</body>
</html>
