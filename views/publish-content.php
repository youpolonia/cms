<?php
// Validate and sanitize input
$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
if (empty($filename) || !preg_match('/^[a-zA-Z0-9\-_]+\.json$/', $filename)) {
    header('Location: /error.php?code=invalid_input');
    exit;
}

$contentPath = __DIR__ . '/../content/generated/' . $filename;
if (!file_exists($contentPath)) {
    header('Location: /error.php?code=file_not_found');
    exit;
}

// Read and parse content
$content = file_get_contents($contentPath);
$data = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['title'], $data['summary'], $data['body'])) {
    header('Location: /error.php?code=invalid_content');
    exit;
}

// Create safe filename
$pageSlug = preg_replace('/[^a-z0-9\-]/', '-', strtolower($data['title']));
$pageSlug = substr($pageSlug, 0, 50) . '.html';
$pagePath = __DIR__ . '/../pages/' . $pageSlug;

// Create HTML content
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$data['title']}</title>
</head>
<body>
    <h1>{$data['title']}</h1>
    <div class="summary">{$data['summary']}</div>
    <div class="content">{$data['body']}</div>
</body>
</html>
HTML;

// Save page
if (file_put_contents($pagePath, $html) === false) {
    header('Location: /error.php?code=save_failed');
    exit;
}

// Redirect to new page
header("Location: /pages/$pageSlug");
exit;
