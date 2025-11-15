<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
require_once __DIR__ . '/memorybrowser.php';
require_once __DIR__ . '/memorybrowserui.php';

header('Content-Type: text/html; charset=utf-8');

$action = $_GET['action'] ?? 'ui';
$browser = new MemoryBrowser();
$ui = new MemoryBrowserUI();

switch ($action) {
    case 'view':
        if (isset($_GET['file'])) {
            $content = $browser->getFileContent($_GET['file']);
            echo htmlspecialchars($content);
        }
        break;
        
    case 'search':
        if (isset($_GET['q'])) {
            header('Content-Type: application/json');
            echo json_encode($browser->searchFiles($_GET['q']));
        }
        break;
        
    case 'export':
        if (isset($_GET['file'])) {
            $browser->exportFile($_GET['file']);
        }
        break;
        
    case 'ui':
    default:
        echo $ui->render();
        echo $ui->renderScripts();
        break;
}
