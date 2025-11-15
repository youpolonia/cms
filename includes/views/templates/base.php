<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($title ?? 'CMS') ?></title>
</head>
<body>
    <header>
        <h1><?= View::e($header ?? 'Welcome') ?></h1>
    </header>
    
    <main>
        <?= $content  ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> My CMS</p>
    </footer>
</body>
</html>
