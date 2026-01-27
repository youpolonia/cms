<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'CMS Home' ?></title>
    <link rel="stylesheet" href="<?= $themeUrl ?? '' ?>/assets/css/main.css">
</head>
<body>
    <header>
        <h1><?= $heading ?? 'Welcome' ?></h1>
    </header>
    <main>
        <?= $content ?? '' ?>
    </main>
    <script src="<?= $themeUrl ?? '' ?>/assets/js/main.js"></script>
</body>
</html>
