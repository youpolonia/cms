<?php
/**
 * Company Profile Template
 */
extract($content); // Extract company data
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $seo['title'] ?? $company['name'] ?></title>
    <meta name="description" content="<?= $seo['description'] ?? '' ?>">
</head>
<body>
    <h1><?= htmlspecialchars($company['name']) ?></h1>
    
    <?php if (!empty($company['description'])): ?>
        <div class="company-description">
            <?= nl2br(htmlspecialchars($company['description']))  ?>
        </div>
    <?php endif;  ?>
    <?php if (!empty($company['logo'])): ?>
        <img src="<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?> logo">
    <?php endif;  ?>
</body>
</html>
