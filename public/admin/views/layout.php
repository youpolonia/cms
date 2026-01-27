<?php
/**
 * Admin Layout Template
 */
use Admin\Core\Services\LanguageService;

$languageService = LanguageService::getInstance();
$currentLanguage = $languageService->detectLanguage();
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($currentLanguage) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body class="admin">
    <header>
        <h1>Admin Panel</h1>
        <nav>
            <?php require_once __DIR__ . '/partials/nav.php'; ?>
            <?php require_once __DIR__ . '/partials/language_switcher.php'; ?>
        </nav>
    </header>

    <main>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
            </div>
        <?php endif; ?>
        <?php
        // Secure include of view content
        $baseDir = realpath(__DIR__);
        $target  = is_string($content) ? realpath($content) : false;
        $okPath  = ($target !== false) && (strpos($target, $baseDir . DIRECTORY_SEPARATOR) === 0);
        $okExt   = ($target !== false) && (pathinfo($target, PATHINFO_EXTENSION) === 'php');
        if (!$okPath || !$okExt) {
            http_response_code(400);
            echo '<div class="alert alert-error"><p>Invalid view path.</p></div>';
        } else {
            require_once $target;
        }
        ?>
    </main>

    <footer>
        <p>© <?= date('Y') ?> CMS Admin</p>
    </footer>
</body>
</html>
