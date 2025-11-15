<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'CMS' ?></title>
    <?php require_once __DIR__ . '/../partials/assets.php'; 
?></head>
<body>
    <header>
        <h1>CMS</h1>
    </header>
    <main>
        <?= $content  ?>
    </main>
    <footer>
        <p>&copy; <?= date('Y') ?> CMS</p>
    </footer>
</body>
</html>
