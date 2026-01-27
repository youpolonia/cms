<?php
require_once __DIR__ . '/../models/blogmanager.php';
require_once __DIR__ . '/../models/blogpost.php';
require_once __DIR__ . '/../content/aicontentengine.php';
require_once __DIR__ . '/../core/csrf.php';

$blogManager = new BlogManager();
$aiEngine = new AIContentEngine();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $slug = $_POST['slug'] ?? '';
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    
    // Get AI rewrite
    $rewritten = $aiEngine->rewriteContent([
        'title' => $title,
        'content' => $body
    ]);
    
    // Store in session for preview
    $_SESSION['ai_rewrite'] = $rewritten;
    $_SESSION['original_content'] = [
        'title' => $title,
        'body' => $body
    ];
    
    header("Location: /admin/blog-rewrite.php?slug=" . urlencode($slug));
    exit;
}

// Load existing post if editing
if (isset($_GET['slug'])) {
    $post = $blogManager->getPost($_GET['slug']);
    $isEdit = true;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Rewrite Preview</title>
    <style>
        .form-container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        textarea { width: 100%; min-height: 300px; padding: 0.5rem; }
        .diff-added { background-color: #e6ffed; }
        .diff-removed { background-color: #ffeef0; }
        .form-actions { margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>AI Rewrite Preview</h1>
        
        <?php if (isset($_SESSION['ai_rewrite'])): ?>
            <div class="form-group">
                <label>Original Title:</label>
                <div><?= htmlspecialchars($_SESSION['original_content']['title']) ?></div>
            </div>
            
            <div class="form-group">
                <label>Rewritten Title:</label>
                <div><?= htmlspecialchars($_SESSION['ai_rewrite']['title']) ?></div>
            </div>
            
            <div class="form-group">
                <label>Rewritten Content:</label>
                <textarea readonly><?= htmlspecialchars($_SESSION['ai_rewrite']['content']) ?></textarea>
            </div>
            
            <div class="form-actions">
                <form method="POST" action="/admin/blog-admin-view.php">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($_GET['slug'] ?? '') ?>">
                    <input type="hidden" name="title" value="<?= htmlspecialchars($_SESSION['ai_rewrite']['title']) ?>">
                    <input type="hidden" name="body" value="<?= htmlspecialchars($_SESSION['ai_rewrite']['content']) ?>">
                    <button type="submit">Use This Version</button>
                </form>
                <a href="/admin/blog-admin-view.php?slug=<?= urlencode($_GET['slug'] ?? '') ?>">Cancel</a>
            </div>
            
            <?php unset($_SESSION['ai_rewrite']); ?>
        <?php else: ?>
            <p>No AI rewrite content found. Please try again.</p>
            <a href="/admin/blog-admin-view.php?slug=<?= urlencode($_GET['slug'] ?? '') ?>">Back to Editor</a>
        <?php endif; ?>
    </div>
</body>
</html>
