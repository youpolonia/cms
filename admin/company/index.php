<?php
require_once __DIR__ . '/../../admin/includes/auth.php';

// Check admin permissions
if (!hasPermission('manage_companies')) {
    header('Location: /admin/');
    exit;
}

// Get all companies
$companies = CompanyController::getAll();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Companies</title>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <div class="container">
        <h1>Companies</h1>
        <a href="create.php" class="btn">Add New Company</a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($companies as $company): ?>
                <tr>
                    <td><?= htmlspecialchars($company['id']) ?></td>
                    <td><?= htmlspecialchars($company['name']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= $company['id'] ?>" class="btn">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
