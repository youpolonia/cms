<!DOCTYPE html>
<html>
<head>
    <title><?= View::e('title', 'CMS') ?></title>
    <?php View::partial('partials/head-meta') ?>
    <?php View::partial('partials/head-assets') ?>
</head>
<body>
    <?php View::partial('partials/header') ?>
    <main class="container">
        <?= $content ?? '' ?>
    </main>

    <?php View::partial('partials/footer') ?>
    <?php View::partial('partials/footer-assets') ?>
</body>
</html>
