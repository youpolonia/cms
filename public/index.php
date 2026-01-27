<?php
declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__));
define('CMS_PUBLIC', __DIR__);
define('CMS_APP', CMS_ROOT . '/app');
define('CMS_CORE', CMS_ROOT . '/core');
define('CMS_CONFIG', CMS_ROOT . '/config');
define('CMS_STORAGE', CMS_ROOT . '/storage');

require_once CMS_ROOT . '/config.php';
require_once CMS_CORE . '/app.php';

$app = new \Core\App();
$app->run();
