<?php
require_once __DIR__ . '/../../core/bootstrap.php';
// Verify admin session
require_once __DIR__ . '/../admin-access.php';

// CSRF protection
if (empty($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['flash_message'] = 'Invalid CSRF token';
    header('Location: menus.php');
    exit;
}

// Database connection
require_once __DIR__ . '/../../includes/db_connect.php';

// Get menu ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash_message'] = 'Invalid menu ID';
    header('Location: menus.php');
    exit;
}

try {
    // Delete menu
    $stmt = $pdo->prepare("DELETE FROM menus WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $_SESSION['flash_message'] = 'Menu deleted successfully';
} catch (PDOException $e) {
    $_SESSION['flash_message'] = 'Error deleting menu: ' . $e->getMessage();
}

header('Location: menus.php');
exit;
