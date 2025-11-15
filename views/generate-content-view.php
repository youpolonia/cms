<?php
require_once __DIR__ . '/../content/aicontentengine.php';

$content = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['topic'])) {
    try {
        $topic = htmlspecialchars($_POST['topic']);
        $content = AIContentEngine::generateContent($topic);
    } catch (Exception $e) {
        $error = htmlspecialchars($e->getMessage());
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>AI Content Generator</title>
</head>
<body>
    <h1>Generate Content</h1>
    
    <form method="POST">
        <label for="topic">Topic:</label>
        <input type="text" id="topic" name="topic"
 required>
?>        <button type="submit">Generate</button>
    </form>

    <?php if ($error): ?>
        <div style="color: red;">Error: <?= $error ?></div>
    <?php endif; ?>
    <?php if ($content): ?>
        <div class="generated-content">
            <h2><?= htmlspecialchars($content['title']) ?></h2>
            <p><strong>Summary:</strong> <?= htmlspecialchars($content['summary']) ?></p>
            <div><?= $content['body'] ?></div>
            <div>
                <strong>Tags:</strong>
                <ul>
                    <?php foreach ($content['tags'] as $tag): ?>
                        <li><?= htmlspecialchars($tag) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
