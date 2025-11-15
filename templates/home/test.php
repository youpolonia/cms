<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($title) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($title) ?></h1>
    <div class="message">
        <?= htmlspecialchars($message)  ?>
    </div>
    <p>Current time: <?= date('Y-m-d H:i:s') ?></p>
</body>
</html>
