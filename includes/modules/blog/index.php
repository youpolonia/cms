<?php
/**
 * Blog Module Entry Point
 * Routes to appropriate blog views
 */

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'view':
        require_once __DIR__.'/view.php';
        break;
    default:
        require_once __DIR__.'/list.php';
}
