<?php
require_once __DIR__ . '/../../services/SeoService.php';

$seoService = new SeoService();
$content = $_POST['content'] ?? '';
$metaTitle = $_POST['meta_title'] ?? '';
$metaDescription = $_POST['meta_description'] ?? '';
$seoScore = $seoService->getSeoScore($content);


?><!DOCTYPE html>
<html>
<head>
    <title>SEO Editor</title>
    <script src="seo.js"></script>
</head>
<body>
    <h1>SEO Editor</h1>
    <div class="seo-nav">
        <a href="robots.php" class="nav-link">Robots.txt Management</a>
    </div>
    <div class="seo-score">SEO Score: <span id="seo-score"><?= $seoScore ?></span>/100</div>
    <form id="seo-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="10"><?= htmlspecialchars($content) ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="meta_title">Meta Title:</label>
            <input type="text" id="meta_title" name="meta_title" value="<?= htmlspecialchars($metaTitle) ?>">
            <button type="button" id="generate-title" class="ai-button">Generate with AI</button>
        </div>
        
        <div class="form-group">
            <label for="meta_description">Meta Description:</label>
            <textarea id="meta_description" name="meta_description"><?= htmlspecialchars($metaDescription) ?></textarea>
            <button type="button" id="generate-description" class="ai-button">Generate with AI</button>
        </div>
        
        <button type="submit">Save SEO Settings</button>
    </form>
</body>
</html>
