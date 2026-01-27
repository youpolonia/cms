<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('CMS_ROOT', '/var/www/html/cms');
define('CMS_APP', CMS_ROOT . '/app');
define('CMS_CORE', CMS_ROOT . '/core');
define('CMS_CONFIG', CMS_ROOT . '/config');

echo "1. Loading config...\n";
require_once CMS_ROOT . '/config.php';

echo "2. Loading helpers...\n";
require_once CMS_APP . '/helpers/functions.php';

echo "3. Loading core...\n";
require_once CMS_CORE . '/session.php';
require_once CMS_CORE . '/request.php';
require_once CMS_CORE . '/response.php';

echo "4. Loading AnalyticsService...\n";
require_once CMS_ROOT . '/services/analyticsservice.php';

echo "5. Loading controller...\n";
require_once CMS_APP . '/controllers/admin/analyticscontroller.php';

echo "6. Creating controller instance...\n";
$ctrl = new \App\Controllers\Admin\AnalyticsController();

echo "7. Calling index method...\n";
$request = new \Core\Request();
try {
    $ctrl->index($request);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
