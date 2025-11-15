<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CMS' ?></title>
    <?= $this->assetManager->renderCss() ?>
</head>
<body>
    <header>
        <h1><?= $siteTitle ?? 'My CMS' ?></h1>
        <nav>
            <?= $navigation ?? '' ?>
        </nav>
    </header>

<main>
        <?= $this->yield('content') ?>
    </main>

<footer>
        <p>&copy; <?= date('Y') ?> My CMS</p>
    </footer>

    <?= $this->assetManager->renderJs() ?>
</body>
</html>
