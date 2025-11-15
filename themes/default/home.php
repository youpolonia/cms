<!DOCTYPE html>
<html>
<head>
    <title><?= $title ?? 'CMS Home' ?></title>
    <link rel="stylesheet" href="<?= $this->getAssetUrl('css', 'main.css') ?>">
</head>
<body>
    <header>
        <h1><?= $heading ?? 'Welcome' ?></h1>
    </header>
    <main>
        <?= $content ?? '' 
?>    </main>
    <script src="<?= $this->getAssetUrl('js', 'main.js') ?>"></script>
</body>
</html>
