<?php
declare(strict_types=1);

/**
 * Admin Dashboard
 * 
 * @package CMS
 * @subpackage Admin
 */

// Verify admin access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /login');
    exit;
}

require_once __DIR__ . '/../views/templates/admin/dashboard.php';
