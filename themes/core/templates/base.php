<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Default Title' ?></title>
    <link rel="stylesheet" href="<?= $themeUrl ?? '' ?>/assets/css/main.css">
    <?= $head ?? '' ?>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <a href="/">
                    <?= $siteName ?? 'CMS' ?>
                </a>
            </div>
            <nav>
                <?= $navigation ?? '' ?>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?= $content ?? '' ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> <?= $siteName ?? 'CMS' ?></p>
            <?= $footer ?? '' ?>
        </div>
    </footer>

    <script src="<?= $themeUrl ?? '' ?>/assets/js/main.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
