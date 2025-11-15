<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e('page_title', 'CMS') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
    <?php View::partial('partials/head-assets') ?>
</head>
<body>
    <?php View::partial('partials/header') ?>
<main class="content">
        <?= $content ?? '' ?>
    </main>

    <?php View::partial('partials/footer') ?>    <?php View::partial('partials/footer-assets') ?>
    <script src="<?= BASE_URL ?>/assets/js/recommendation-client.js"></script>
</body>
</html>
