<?php
// CSRF guard (auto-inserted)
if (!defined('CMS_ROOT')) {
    $___root = dirname(__DIR__, 2);
    if (!is_file($___root . '/config.php')) { $___root = dirname(__DIR__); }
    require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session

require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
}
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }

require_once __DIR__ . '/../core/automation_rules.php';
require_once __DIR__ . '/../models/blogmanager.php';
require_once __DIR__ . '/../models/blogpost.php';

$blogManager = new BlogManager();
$post = null;
$isEdit = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $slug = $_POST['slug'] ?? '';
    $title = $_POST['title'] ?? '';
    $body = $_POST['body'] ?? '';
    $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
    $published = isset($_POST['published']);

    $post = new BlogPost($title, $slug, $body, $tags, $published);
    $blogManager->savePost($post);

    if ($published) {
        automation_rules_handle_event('blog.post_published', [
            'post_id'   => null,
            'title'     => $title,
            'slug'      => $slug,
            'status'    => 'published',
            'author_id' => $_SESSION['user_id'] ?? null
        ]);
    }

    header("Location: /admin/blog-admin-view.php?slug=" . urlencode($slug));
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
    <title><?= $isEdit ? 'Edit' : 'Create' ?> Blog Post</title>
    <style>
        .form-container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"], textarea { width: 100%; padding: 0.5rem; }
        textarea { min-height: 300px; }
        .checkbox-label { display: inline-block; margin-left: 0.5rem; }
        .form-actions { margin-top: 1rem; }
        .ai-generate { margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="form-container">
        <h1><?= $isEdit ? 'Edit' : 'Create' ?> Blog Post</h1>
        <form method="POST">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required 
                       value="<?= htmlspecialchars($post->title ?? '') ?>">            </div>
            
            <div class="form-group">
                <label for="slug">Slug (URL-friendly identifier):</label>
                <input type="text" id="slug" name="slug" required 
                       value="<?= htmlspecialchars($post->slug ?? '') ?>">            </div>
            
            <div class="form-group">
                <label for="body">Content:</label>
                <textarea id="body" name="body" required><?= htmlspecialchars($post->body ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="tags">Tags (comma separated):</label>
                <input type="text" id="tags" name="tags" 
                       value="<?= htmlspecialchars(implode(', ', $post->tags ?? [])) ?>">
            </div>
            
            <div class="form-group">
                <input type="checkbox" id="published" name="published" 
                       <?= ($post->published ?? true) ? 'checked' : '' ?>>
                <label for="published" class="checkbox-label">Published</label>
            </div>
            
            <div class="form-actions">
                <button type="submit">Save Post</button>
                <button type="button" onclick="document.getElementById('seo-form').submit()">Analyze SEO</button>
                <button type="button" onclick="document.getElementById('rewrite-form').submit()">Rewrite with AI</button>
                <a href="/blog">Cancel</a>
            </div>
            <?= csrf_field(); 
?>        </form>

        <form id="seo-form" method="POST" action="/admin/blog-seo-analyze.php" style="display: none;">
            <input type="hidden" name="title" value="<?= htmlspecialchars($post->title ?? '') ?>">
            <input type="hidden" name="content" value="<?= htmlspecialchars($post->body ?? '') ?>">
            <input type="hidden" name="tags" value="<?= htmlspecialchars(implode(', ', $post->tags ?? [])) ?>">
            <?= csrf_field(); 
?>        </form>

        <form id="rewrite-form" method="POST" action="/admin/blog-rewrite.php" style="display: none;">
            <input type="hidden" name="slug" value="<?= htmlspecialchars($post->slug ?? '') ?>">
            <input type="hidden" name="title" value="<?= htmlspecialchars($post->title ?? '') ?>">
            <input type="hidden" name="body" value="<?= htmlspecialchars($post->body ?? '') ?>">
            <?= csrf_field(); 
?>        </form>

        <?php if (isset($_SESSION['seo_suggestions'])): ?>
            <div class="seo-suggestions" style="margin-top: 2rem; padding: 1rem; background: #f8f8f8; border-radius: 4px;">
                <h3>SEO Suggestions</h3>
                <div class="suggestion">
                    <h4>Meta Title</h4>
                    <p><?= htmlspecialchars($_SESSION['seo_suggestions']['meta_title']) ?></p>
                </div>
                <div class="suggestion">
                    <h4>Meta Description</h4>
                    <p><?= htmlspecialchars($_SESSION['seo_suggestions']['meta_description']) ?></p>
                </div>
                <div class="suggestion">
                    <h4>Keywords</h4>
                    <p><?= htmlspecialchars(implode(', ', $_SESSION['seo_suggestions']['keywords'])) ?></p>
                </div>
            </div>
            <?php unset($_SESSION['seo_suggestions']); ?>
        <?php endif; ?>
        <div class="versions-section" style="margin-top: 2rem;">
            <h3>Previous Versions</h3>
            <?php
            $versions = $blogManager->getVersions($post->slug ?? '');
            if (!empty($versions)): ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Date</th>
                            <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($versions as $timestamp => $file): ?>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                    <?= date('Y-m-d H:i:s', $timestamp) 
?>                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                    <form method="POST" action="/admin/blog-view-version.php" target="_blank" style="display: inline;">
                                        <?= csrf_field(); 
?>                                        <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                                        <button type="submit">Preview</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No previous versions found</p>
            <?php endif; ?>
        </div>
        
        <?php if (!$isEdit): ?>
            <div class="ai-generate">
                <h3>AI Content Generation</h3>
                <button type="button" onclick="generateAIContent()">Generate Blog Post with AI</button>
                <div id="ai-status"></div>
            </div>
            
            <script>
                function generateAIContent() {
                    const title = document.getElementById('title').value;
                    if (!title) {
                        alert('Please enter a title first');
                        return;
                    }
                    
                    document.getElementById('ai-status').textContent = 'Generating...';
                    
                    fetch('/api/generate-blog-post.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ title: title })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('body').value = data.content;
                            document.getElementById('ai-status').textContent = 'Content generated!';
                        } else {
                            document.getElementById('ai-status').textContent = 'Error: ' + data.message;
                        }
                    })
                    .catch(error => {
                        document.getElementById('ai-status').textContent = 'Error: ' + error.message;
                    });
                }
            </script>
        <?php endif; ?>
    </div>
</body>
</html>
