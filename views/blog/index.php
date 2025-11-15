<section class="blog-posts">
    <h1>Blog Posts</h1>

    <?php foreach ($posts as $post): ?>
        <article class="post">
            <h2><a href="post.php?id=<?= array_search($post, $posts) ?>"><?= htmlspecialchars($post['title']) ?></a></h2>
            <time datetime="<?= $post['date'] ?>"><?= date('F j, Y', strtotime($post['date'])) ?></time>
            <p><?= htmlspecialchars($post['content']) ?></p>
            <a href="post.php?id=<?= array_search($post, $posts) ?>" class="read-more">Read more</a>
        </article>
    <?php endforeach; ?>
</ul>

</body>
</html>
