<?php
require_once __DIR__.'/../../includes/adminauth.php';
require_once __DIR__ . '/../controllers/contenttypescontroller.php';

$controller = new ContentTypesController();
$action = $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $controller->list();
            break;
            
        case 'add':
            $controller->showForm();
            break;
            
        case 'edit':
            if (empty($_GET['id'])) {
                throw new Exception('Content Type ID is required');
            }
            $controller->showForm($_GET['id']);
            break;
            
        case 'save':
            $controller->save();
            break;
            
        case 'delete':
            if (empty($_POST['id'])) {
                throw new Exception('Content Type ID is required');
            }
            $controller->delete($_POST['id']);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header('Location: types.php');
    exit;
}
