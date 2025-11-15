<?php
require_once __DIR__ . '/../core/aiclient.php';
// session boot (admin)
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

// Simple session-based authentication
cms_session_start('admin');
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

csrf_boot('admin');

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    try {
        $action = $_POST['action'] ?? '';
        $apiKey = $_POST['api_key'] ?? '';
        $prompt = $_POST['prompt'] ?? '';
        
        AIClient::init($apiKey);
        
        switch ($action) {
            case 'generate':
                $content = AIClient::generateContent($prompt);
                $_SESSION['ai_content'] = $content;
                break;
                
            case 'moderate':
                $isSafe = AIClient::moderateContent($_POST['content'] ?? '');
                $_SESSION['moderation_result'] = $isSafe ? 'Approved' : 'Rejected';
                break;
                
            case 'save_key':
                $_SESSION['ai_api_key'] = $apiKey;
                break;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: editor-ai.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Content Editor</title>
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        textarea { width: 100%; min-height: 200px; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="container">
        <h1>AI Content Tools</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?= htmlspecialchars($_SESSION['error']) ?></p>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <h2>API Key Management</h2>
        <form method="post">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label>AI API Key:</label>
                <input type="text" name="api_key" value="<?= htmlspecialchars($_SESSION['ai_api_key'] ?? '') ?>" style="width: 100%">
            </div>
            <input type="hidden" name="action" value="save_key">
            <button type="submit">Save Key</button>
        </form>
        
        <h2>Content Generation</h2>
        <form method="post">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label>Prompt:</label>
                <textarea name="prompt" required></textarea>
            </div>
            <input type="hidden" name="action" value="generate">
            <button type="submit">Generate Content</button>
        </form>
        
        <?php if (isset($_SESSION['ai_content'])): ?>
            <h3>Generated Content:</h3>
            <textarea readonly><?= htmlspecialchars($_SESSION['ai_content']) ?></textarea>
            <?php unset($_SESSION['ai_content']); ?>
        <?php endif; ?>
        <h2>Content Moderation</h2>
        <form method="post">
            <?= csrf_field(); ?>
            <div class="form-group">
                <label>Content to Moderate:</label>
                <textarea name="content" required></textarea>
            </div>
            <input type="hidden" name="action" value="moderate">
            <button type="submit">Check Content</button>
        </form>
        
        <?php if (isset($_SESSION['moderation_result'])): ?>
            <p class="<?= $_SESSION['moderation_result'] === 'Approved' ? 'success' : 'error' ?>">
                Content <?= $_SESSION['moderation_result'] ?>
            </p>
            <?php unset($_SESSION['moderation_result']); ?>
        <?php endif; ?>
    </div>
</body>
</html>
