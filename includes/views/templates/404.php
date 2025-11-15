<!DOCTYPE html>
<html>
<head>
    <title>Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif;  ?>
</body>
</html>
