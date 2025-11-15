<?php
// CMS Core Constants
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('CMS_ADMIN')) {
    define('CMS_ADMIN', CMS_ROOT . DS . 'admin');
}

if (!defined('CMS_INCLUDES')) {
    define('CMS_INCLUDES', CMS_ROOT . DS . 'includes');
}

if (!defined('CMS_TEMP')) {
    define('CMS_TEMP', CMS_ROOT . DS . 'temp');
}

if (!defined('CMS_ASSETS')) {
    define('CMS_ASSETS', CMS_ROOT . DS . 'assets');
}

if (!defined('CMS_UPLOADS')) {
    define('CMS_UPLOADS', CMS_ROOT . DS . 'uploads');
}
