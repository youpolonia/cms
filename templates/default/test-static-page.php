<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Test Static Page Template
 *
 * @package CMS
 * @subpackage Templates
 */

declare(strict_types=1);

// Template variables available:
// $title - Page title
// $content - Page content
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
</head>
<body>
    <header>
        <h1><?= htmlspecialchars($title) ?></h1>
    </header>
    
    <main>
        <?= $content  ?>
    </main>
    
    <footer>
        <p>Static page rendered via TemplateInheritance</p>
    </footer>
</body>
</html>
