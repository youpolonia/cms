<?php
/**
 * Company Profile Template
 * 
 * @param array $company Company data
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($company['name'] ?? 'Company Profile') ?></title>
    <link rel="stylesheet" href="/assets/css/company.css">
</head>
<body>
    <div class="company-profile">
        <h1><?= htmlspecialchars($company['name'] ?? 'Company') ?></h1>
        
        <?php if (!empty($company['logo'])): ?>
            <div class="company-logo">
                <img src="<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name'] ?? '') ?> logo">
            </div>
        <?php endif;  ?>
        <?php if (!empty($company['description'])): ?>
            <div class="company-description">
                <?= nl2br(htmlspecialchars($company['description']))  ?>
            </div>
        <?php endif;  ?>
        <?php if (!empty($company['website'])): ?>
            <div class="company-website">
                <a href="<?= htmlspecialchars($company['website']) ?>" target="_blank">Visit Website</a>
            </div>
        <?php endif;  ?>
    </div>
</body>
</html>
