<?php
$pageTitle = $page['title'] ?? 'Page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <?php if (!empty($page['meta_description'])): ?>
    <meta name="description" content="<?= esc($page['meta_description']) ?>">
    <?php endif; ?>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --text-primary: #1a1a2e;
            --text-secondary: #4a4a68;
            --surface-primary: #ffffff;
            --accent-primary: #6366f1;
            --radius-md: 8px;
        }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: var(--text-primary); background: var(--surface-primary); }
        .blank-page { min-height: 100vh; padding: 40px 24px; }
        .blank-page-content { max-width: 1200px; margin: 0 auto; }
        .blank-page-content h1 { font-size: 2.5rem; margin-bottom: 30px; }
        .blank-page-content h2 { font-size: 1.75rem; margin: 40px 0 20px; }
        .blank-page-content p { margin-bottom: 20px; }
        .blank-page-content a { color: var(--accent-primary); }
        .blank-page-content img { max-width: 100%; height: auto; border-radius: var(--radius-md); }
    </style>
</head>
<body>
<article class="blank-page">
    <div class="blank-page-content">
        <?= $page['content'] ?? '' ?>
    </div>
</article>
</body>
</html>
