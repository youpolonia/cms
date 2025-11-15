<!DOCTYPE html>
<html>
<head>
    <title>CMS Home</title>
</head>
<body>
    <h1>Welcome to the CMS</h1>
    <?php if (isset($message)): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif;  ?>
</body>
</html>
