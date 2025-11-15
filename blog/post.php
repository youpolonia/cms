<?php
// Single blog post view
ob_start();

?><main class="container">
    <article class="blog-post">
        <h1>Blog Post Title</h1>
        <div class="post-content">
            <p>Full blog post content will appear here</p>
        </div>
    </article>
</main>
<?php
$content = ob_get_clean();
require_once __DIR__ . '/../views/includes/layout.php';
render_layout('Blog Post', $content);
