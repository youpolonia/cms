<?php
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
// Admin access check
cms_session_start('admin');
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied. Admins only.');
}

csrf_boot();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate inputs
    $errors = [];
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $type = $_POST['type'] ?? '';
    $scheduled_at = $_POST['scheduled_at'] ?? '';

    if (empty($title)) $errors[] = 'Title is required';
    if (empty($content)) $errors[] = 'Content is required';
    if (!in_array($type, ['blog', 'page', 'custom'])) $errors[] = 'Invalid content type';
    
    try {
        $scheduled_time = new DateTime($scheduled_at);
        $now = new DateTime();
        if ($scheduled_time <= $now) $errors[] = 'Scheduled time must be in the future';
    } catch (Exception $e) {
        $errors[] = 'Invalid date/time format';
    }

    // If no errors, save to JSON
    if (empty($errors)) {
        // Ensure scheduled directory exists
        if (!is_dir('../scheduled')) {
            mkdir('../scheduled', 0755, true);
        }

        // Prepare data
        $data = [
            'meta' => [
                'author' => $_SESSION['admin_username'] ?? 'system',
                'created_at' => date('c'),
                'type' => $type,
                'scheduled_at' => $scheduled_at
            ],
            'content' => [
                'title' => $title,
                'body' => $content
            ]
        ];

        // Generate filename
        $filename = '../scheduled/content_' . date('Ymd_His') . '_' . uniqid() . '.json';

        // Save file
        if (file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT))) {
            $success = 'Content successfully scheduled for ' . $scheduled_at;
        } else {
            $errors[] = 'Failed to save scheduled content';
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Content</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea, select { 
            width: 100%; 
            padding: 8px; 
            box-sizing: border-box; 
            max-width: 500px; 
        }
        textarea { height: 200px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Schedule Content</h1>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <form method="post">
        <?= csrf_field(); 
?>        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title"
required
                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">        </div>
        
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content" required><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="type">Content Type:</label>
            <select id="type" name="type" required>
                <option value="">Select type</option>
                <option value="blog" <?= ($_POST['type'] ?? '') === 'blog' ? 'selected' : '' ?>>Blog Post</option>
                <option value="page" <?= ($_POST['type'] ?? '') === 'page' ? 'selected' : '' ?>>Page</option>
                <option value="custom" <?= ($_POST['type'] ?? '') === 'custom' ? 'selected' : '' ?>>Custom</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="scheduled_at">Scheduled Date/Time:</label>
            <input type="datetime-local" id="scheduled_at" name="scheduled_at" required value="<?= htmlspecialchars($_POST['scheduled_at'] ?? '') ?>">
        </div>
        
        <button type="submit">Schedule Content</button>
    </form>
</body>
</html>
