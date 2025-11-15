<?php
require_once __DIR__ . '/../config.php';

$DB_USER = (defined('DB_USER') ? DB_USER : 'cms_user');
$DB_PASS = (defined('DB_PASS') ? DB_PASS : '');
$DB_NAME = (defined('DB_NAME') ? DB_NAME : 'cms_database');
$DB_HOST = (defined('DB_HOST') ? DB_HOST : 'localhost');
