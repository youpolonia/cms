<?php
require_once __DIR__ . '/../media/ai/mediaaisearch.php';
require_once __DIR__ . '/../modules/mediagallery/mediaregistry.php';

// Handle search if form submitted
$filesToDisplay = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['semantic_query'])) {
        $searchResults = MediaAISearch::semanticSearch($_POST['semantic_query']);
        foreach ($searchResults as $filename => $score) {
            $fileData = MediaRegistry::getByFilename($filename);
            if ($fileData) {
                $filesToDisplay[] = $fileData;
            }
        }
    }
} else {
    // Default to showing all files
    $filesToDisplay = MediaRegistry::getAll();
}

?><!DOCTYPE html>
<html>
<head>
    <title>Media Gallery</title>
    <style>
        .media-tools { margin: 10px 0; }
        .media-tools a { 
            display: inline-block;
            margin-right: 5px;
            padding: 3px 8px;
            background: #f0f0f0;
            border: 1px solid #ddd;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>Media Gallery</h1>
    
    <!-- AI Image Generation Form -->
    <form method="POST" action="image-tools.php?action=generate">
        <input type="text" name="prompt" placeholder="Describe image to generate..."
 required>
?>        <button type="submit">Generate Image</button>
    </form>

    <!-- Semantic Search Form -->
    <form method="POST">
        <input type="text" name="semantic_query" placeholder="Search media..."
 required>
?>        <button type="submit">Search</button>
    </form>

    <!-- Results Display -->
    <div class="media-grid">
        <?php foreach ($filesToDisplay as $file): ?>
            <div class="media-item">
                <h3><?= htmlspecialchars($file['filename']) ?></h3>
                
                <!-- Image Tools -->
                <div class="media-tools">
                    <a href="image-tools.php?action=resize&file=<?= urlencode($file['filename']) ?>">Resize</a>
                    <a href="image-tools.php?action=crop&file=<?= urlencode($file['filename']) ?>">Crop</a>
                    <a href="image-tools.php?action=removeBackground&file=<?= urlencode($file['filename']) ?>">Remove BG</a>
                </div>

                <?php if (!empty($file['ai_title'])): ?>
                    <p><strong>Title:</strong> <?= htmlspecialchars($file['ai_title']) ?></p>
                <?php endif;  ?>                <?php if (!empty($file['ai_description'])): ?>
                    <p><strong>Description:</strong> <?= htmlspecialchars($file['ai_description']) ?></p>
                <?php endif;  ?>                <?php if (!empty($file['ai_tags'])): ?>
                    <p><strong>Tags:</strong> <?= htmlspecialchars($file['ai_tags']) ?></p>
                <?php endif;  ?>
            </div>
        <?php endforeach;  ?>
    </div>
</body>
</html>
