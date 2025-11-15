<section class="single-post">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <time datetime="<?= $post['date'] ?>"><?= date('F j, Y', strtotime($post['date'])) ?></time>
    <div class="post-content">
        <p><?= htmlspecialchars($post->body) ?></p>
    </div>
    <a href="/blog" class="back-to-blog">Back to Blog</a>
</section>

</body>
</html>
