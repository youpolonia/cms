<?php
require_once __DIR__ . '/../../includes/coreloader.php';
require_once __DIR__ . '/../reportscontroller.php';
$core = new CoreLoader();
$core->loadController('ReportsController');

$reportsController = new ReportsController();
$data = $reportsController->index();

$title = $data['title'];
$content = $data['content'];

require_once __DIR__ . '/../views/layout.php';
