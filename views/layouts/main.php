<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CMS' ?></title>
    <?php require_once __DIR__ . '/../partials/assets.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../partials/header.php'; ?>
<main>
        <?= $content ?? '' ?>
    </main>
    <?php require_once __DIR__ . '/../partials/footer.php'; ?>
</body>
</html>
