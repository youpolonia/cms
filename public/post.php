<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../controllers/blogcontroller.php';
require_once __DIR__ . '/../core/commentmanager.php';

$controller = new BlogController();
$posts = $controller->getPosts();
$postId = $_GET['id'] ?? null;

if ($postId !== null && isset($posts[$postId])) {
    $post = $posts[$postId];
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit;
}

// Handle comment submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    try {
        if (!CommentManager::validateCsrfToken($_POST['csrf_token'] ?? '')) {
            throw new RuntimeException('Invalid CSRF token');
        }

        $commentData = [
            'post_id' => $postId,
            'author_name' => $_POST['author_name'] ?? '',
            'author_email' => $_POST['author_email'] ?? '',
            'content' => $_POST['content'] ?? ''
        ];

        if (CommentManager::submitComment($commentData)) {
            $success = true;
        }
    } catch (Exception $e) {
        $errors[] = $e->getMessage();
    }
}

$csrfToken = CommentManager::generateCsrfToken();
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="/assets/public-ui.css">
</head>
<body>
    <?php require_once __DIR__ . '/../views/layouts/header.php'; 
?>    <main class="blog-post">
        <article>
            <h1><?= htmlspecialchars($post['title']) ?></h1>
            <time datetime="<?= $post['date'] ?>">
                <?= date('F j, Y', strtotime($post['date'])) 
?>            </time>
            <div class="post-content">
                <?= nl2br(htmlspecialchars($post['content'])) 
?>            </div>
            <a href="/blog" class="btn">Back to Blog</a>
        </article>

        <section class="comments-section">
            <h2>Comments</h2>

            <?php if ($success): ?>
                <div class="alert success">Thank you! Your comment is awaiting moderation.</div>
            <?php endif; ?>            <?php if (!empty($errors)): ?>
                <div class="alert error">
                    <?php foreach ($errors as $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="post" class="comment-form">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                <div class="form-group">
                    <label for="author_name">Name*</label>
                    <input type="text" id="author_name" name="author_name"
 required
                           value="<?= htmlspecialchars($_POST['author_name'] ?? '') ?>">
?>                </div>

                <div class="form-group">
                    <label for="author_email">Email*</label>
                    <input type="email" id="author_email" name="author_email"
 required
                           value="<?= htmlspecialchars($_POST['author_email'] ?? '') ?>">
?>                </div>

                <div class="form-group">
                    <label for="content">Comment*</label>
                    <textarea id="content" name="content"
 required><?=
                        htmlspecialchars($_POST['content'] ?? '')
                    ?></textarea>
                </div>

                <button type="submit" name="submit_comment" class="btn">Submit Comment</button>
            </form>

            <?= CommentManager::renderComments($postId) 
?>        </section>
    </main>

    <?php require_once __DIR__ . '/../views/layouts/footer.php';
?></body>
</html>
