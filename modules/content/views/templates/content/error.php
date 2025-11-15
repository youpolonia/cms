<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Content Not Found' ?></title>
</head>
<body>
    <div class="content-error">
        <h1><?= $heading ?? 'Content Not Found' ?></h1>
        <p><?= $message ?? 'The requested content could not be found.' ?></p>
        
        <?php if (!empty($suggestions)): ?>
            <div class="suggestions">
                <h3>You might be interested in:</h3>
                <ul>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <li><a href="<?= $suggestion['url'] ?>"><?= $suggestion['title'] ?></a></li>
                    <?php endforeach;  ?>
                </ul>
            </div>
        <?php endif;  ?>
        <p><a href="<?= $home_url ?? '/' ?>">Return to homepage</a></p>
    </div>
</body>
</html>
