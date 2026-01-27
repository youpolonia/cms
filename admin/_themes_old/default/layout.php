<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <?php if (isset($styles)): ?>        <?php foreach ($styles as $style): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($style) ?>">
        <?php endforeach; ?>    <?php endif; ?>
</head>
<body>
    <?php if (config('dev_mode')): ?>
<div class="dev-banner">DEVELOPMENT MODE</div>
    <?php endif; ?>
<header>
        <?= $navigation ?? '' ?>
    </header>

<main>
        <?= $content ?>
    </main>

    <?php if (isset($scripts)): ?>        <?php foreach ($scripts as $script): ?>
<script src="<?= htmlspecialchars($script) ?>"></script>
        <?php endforeach; ?>    <?php endif; ?>
</body>
</html>
