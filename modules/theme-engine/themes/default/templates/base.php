<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'CMS') ?></title>
    <link rel="stylesheet" href="/css/main.css">
</head>
<body>
    <?php $this->insert('partials/navigation') ?>

    <?php $this->section('content') ?>
        <!-- Default content can go here if needed -->
    <?php $this->endSection() ?>

    <?php $this->insert('partials/footer') ?>
</body>
</html>
